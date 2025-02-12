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
use Flarum\Locale\LocaleManager;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddLanguageFilter implements MiddlewareInterface
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var LocaleManager
     */
    protected $locales;

    public function __construct(SettingsRepositoryInterface $settings, LocaleManager $locales)
    {
        $this->settings = $settings;
        $this->locales = $locales;
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

        // Request has a language parameter, so we handle the request with the language filter applied.
        if ($language = Arr::get($params, 'language')) {
            $request = $this->addQueryParams($request, $params, $language);

            return $handler->handle($request);
        }

        // Request has no language parameter, if we are set to force a language, determine the best language to apply
        if ((bool) $this->settings->get('fof-discussion-language.filter_language_on_http_request') || !(bool) $this->settings->get('fof-discussion-language.showAnyLangOpt')) {
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

            if ($language) {
                if ((bool) $this->settings->get('fof-discussion-language.showAnyLangOpt')) {
                    $uri = $request->getUri();
                    $uri = $uri->withQuery("language=$language");

                    return new RedirectResponse($uri, 303);
                } else {
                    $request = $this->addQueryParams($request, $params, $language);

                    return $handler->handle($request);
                }
            }
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

        // Set or overwrite the language filter.
        $params['filter']['language'] = $language;

        // If a search query is present, append the language filter to it.
        if (isset($params['q']) && $params['q'] !== '') {
            $params['q'] = $params['q'].' language:'.$language;
        }

        return $request->withQueryParams($params);
    }

    protected function isDiscussionListPath(ServerRequestInterface $request)
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

        // Also apply for tag pages.
        if (substr($path, 0, 2) === '/t') {
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
