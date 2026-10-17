<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Middleware;

use Flarum\Http\RequestUtil;
use Flarum\Http\UrlGenerator;
use Flarum\Locale\LocaleManager;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\DiscussionLanguage\DiscussionLanguage;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddLanguageFilter implements MiddlewareInterface
{
    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected LocaleManager $locales,
        protected UrlGenerator $url
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // We only want to apply language filtering if we show the discussion list.
        if (!$this->isDiscussionListPath($request)) {
            return $handler->handle($request);
        }

        $params = $request->getQueryParams();
        $showAnyLanguage = (bool) $this->settings->get('fof-discussion-language.showAnyLangOpt');

        // The language the reader chose, as the forum writes it: `filter[language]`.
        $chosen = Arr::get(is_array($params['filter'] ?? null) ? $params['filter'] : [], 'language');

        if (is_string($chosen) && $chosen !== '' && ($chosen !== DiscussionLanguage::ANY || $showAnyLanguage)) {
            return $handler->handle($request);
        }

        // Or as 1.x's links did: `?language=`. The `is_string` guard ignores malformed
        // input like `?language[$ne]=10` from security scanners.
        if (($language = Arr::get($params, 'language')) && is_string($language)) {
            $request = $this->addQueryParams($request, $params, $language);

            return $handler->handle($request);
        }

        // Request has no language parameter, if we are set to force a language, determine the best language to apply
        if ((bool) $this->settings->get('fof-discussion-language.filter_language_on_http_request') || ! $showAnyLanguage) {
            /** @var \Flarum\User\User */
            $actor = RequestUtil::getActor($request);

            $language = null;

            if ($actor->exists) {
                $language = $actor->getPreference('locale');
            }

            if ($language === null && $requestLocale = Arr::get($request->getCookieParams(), 'locale')) {
                $language = $requestLocale;
            } elseif ($language === null && $acceptLangs = Arr::get($request->getServerParams(), 'HTTP_ACCEPT_LANGUAGE')) {
                $language = $this->determineLanguageFromBrowserRequest($acceptLangs);
            }

            if ($showAnyLanguage) {
                // Nothing detected, crawlers among them: every language, as before.
                if (! is_string($language) || $language === '') {
                    return $handler->handle($request);
                }

                // Into the URL, so the reader sees which language the list is in and may change it.
                $params['filter'] = [...(is_array($params['filter'] ?? null) ? $params['filter'] : []), 'language' => $language];

                $path = ltrim($request->getAttribute('originalUri')->getPath(), '/');

                return new RedirectResponse($this->url->to('forum')->path($path).'?'.http_build_query($params), 303);
            }

            // Without every language at once, the frontend filters by the forum's own
            // when nothing else says, so the page it is first given must too.
            $language = is_string($language) && $language !== '' ? $language : $this->locales->getLocale();

            $request = $this->addQueryParams($request, $params, $language);

            return $handler->handle($request);
        }

        // If we get this far, we don't have a language parameter, so we can just continue with the request using the forum default language.
        return $handler->handle($request);
    }

    /**
     * Merge the language parameter into the existing params as a `filter` value.
     *
     * @param ServerRequestInterface $request
     * @param array                  $params
     * @param string                 $language
     *
     * @return ServerRequestInterface
     */
    protected function addQueryParams(ServerRequestInterface $request, array $params, string $language): ServerRequestInterface
    {
        // Ensure the 'filter' key exists as an array.
        if (!isset($params['filter']) || !is_array($params['filter'])) {
            $params['filter'] = [];
        }

        // Set or overwrite the language filter. It applies to a search too: 2.0
        // searches `q` as text, so a `language:` gambit appended there would be
        // searched for rather than filtered by.
        $params['filter']['language'] = $language;

        return $request->withQueryParams($params);
    }

    protected function isDiscussionListPath(ServerRequestInterface $request): bool
    {
        $path = $request->getAttribute('originalUri')->getPath();
        $defaultRoute = $this->settings->get('default_route');

        if ($defaultRoute === '/all') {
            // If the default route is '/all', treat both '/' and '/all' as the discussion list.
            if ($path === '/' || $path === '/all') {
                return true;
            }
        } else {
            // Otherwise, if the default route is not '/all', then '/all' is the discussion list.
            if ($path === '/all') {
                return true;
            }
        }

        // Also apply for tag pages, but not the tags page.
        if (str_starts_with($path, '/t/')) {
            return true;
        }

        return false;
    }

    protected function determineLanguageFromBrowserRequest(string $acceptLangs): string
    {
        $langs = [];
        // break up string into pieces (languages and q factors)
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $acceptLangs, $lang_parse);

        if (count($lang_parse[1])) {
            // create a list like "en" => 0.8
            $langs = array_combine($lang_parse[1], $lang_parse[4]);

            // set default to 1 for any without q factor
            foreach ($langs as $lang => $val) {
                if ($val === '') {
                    $langs[$lang] = 1;
                }
            }

            // sort list based on value
            arsort($langs, SORT_NUMERIC);
        }

        // look through sorted list and use first one that matches our installed languages
        foreach ($langs as $lang => $val) {
            if ($this->locales->hasLocale($lang)) {
                // Once we find a match, return it
                return $lang;
            }
        }

        // No matches, so use the forum default language
        return $this->locales->getLocale();
    }
}
