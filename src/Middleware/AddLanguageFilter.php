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

        if ($language = Arr::get($params, 'language')) {
            $request = $this->addQueryParams($request, $params, $language);

            return $handler->handle($request);
        }

        if ((bool) $this->settings->get('fof-discussion-language.filter_language_on_http_request')) {
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

        return $handler->handle($request);
    }

    private function addQueryParams(ServerRequestInterface $request, array $params, string $language): ServerRequestInterface
    {
        return $request->withQueryParams(
            array_merge($params, ['filter' => ['language' => $language]]),
        );
    }

    private function isDiscussionListPath($request)
    {
        $path = $request->getAttribute('originalUri')->getPath();

        // Check for the 'index' route (showing all discussions)
        $defaultRoute = $this->settings->get('default_route');
        if ($defaultRoute === '/all') {
            if ($path === '/') {
                return true;
            }
        } elseif ($path === '/all') {
            return true;
        }

        // Check for the 'tag' route (tag page)
        if (substr($path, 0, 2) === '/t') {
            return true;
        }

        return false;
    }

    private function determineLanguageFromBrowserRequest(string $acceptLangs): string
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
                break;
            }
        }

        // No matches, so use the forum default language
        return $this->locales->getLocale();
    }
}
