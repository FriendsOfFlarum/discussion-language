<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Tests\unit;

use Flarum\Http\UrlGenerator;
use Flarum\Locale\LocaleManager;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Testing\unit\TestCase;
use FoF\DiscussionLanguage\Middleware\AddLanguageFilter;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddLanguageFilterTest extends TestCase
{
    /**
     * The query the forum's discussion list is built from, once the middleware has run.
     */
    protected function handledQuery(array $query, string $path = '/all'): array
    {
        $settings = $this->createStub(SettingsRepositoryInterface::class);
        $settings->method('get')->willReturnMap([
            ['default_route', null, '/all'],
            ['fof-discussion-language.filter_language_on_http_request', null, false],
            ['fof-discussion-language.showAnyLangOpt', null, true],
        ]);

        $handler = new class implements RequestHandlerInterface {
            public ?ServerRequestInterface $request = null;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->request = $request;

                return new EmptyResponse();
            }
        };

        $request = (new ServerRequest([], [], new Uri($path), 'GET'))
            ->withQueryParams($query)
            ->withAttribute('originalUri', new Uri($path));

        (new AddLanguageFilter($settings, $this->createStub(LocaleManager::class), $this->createStub(UrlGenerator::class)))->process($request, $handler);

        return $handler->request->getQueryParams();
    }

    #[Test]
    public function the_language_becomes_a_filter(): void
    {
        $this->assertEquals(['language' => 'de', 'filter' => ['language' => 'de']], $this->handledQuery(['language' => 'de']));
    }

    #[Test]
    public function a_search_is_filtered_by_the_language_and_left_as_it_was(): void
    {
        // 2.0 searches `q` as text: a `language:` gambit appended to it would be searched for.
        $query = $this->handledQuery(['q' => 'tagged release', 'language' => 'de']);

        $this->assertEquals('tagged release', $query['q']);
        $this->assertEquals(['language' => 'de'], $query['filter']);
    }

    #[Test]
    public function other_filters_are_kept(): void
    {
        $this->assertEquals(
            ['tag' => 'general', 'language' => 'de'],
            $this->handledQuery(['language' => 'de', 'filter' => ['tag' => 'general']])['filter']
        );
    }
}
