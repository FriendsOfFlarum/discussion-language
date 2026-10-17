<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\DiscussionLanguage\DiscussionLanguage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class MiddlewareLanguageFilterTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->setting('default_route', '/all');

        $this->extension('flarum-tags');
        $this->extension('fof-discussion-language');

        $this->prepareDatabase([
            Discussion::class => [
                ['id' => 1, 'title' => 'English discussion', 'created_at' => Carbon::now(), 'user_id' => 2, 'first_post_id' => 1, 'language_id' => 1],
                ['id' => 2, 'title' => 'German discussion', 'created_at' => Carbon::now(), 'user_id' => 2, 'first_post_id' => 2, 'language_id' => 2],
                ['id' => 3, 'title' => 'French discussion', 'created_at' => Carbon::now(), 'user_id' => 2, 'first_post_id' => 3, 'language_id' => 3],
            ],
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
                ['id' => 3, 'code' => 'fr', 'country' => 'FR'],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1, 'created_at' => Carbon::now()],
                ['discussion_id' => 2, 'tag_id' => 1, 'created_at' => Carbon::now()],
                ['discussion_id' => 3, 'tag_id' => 1, 'created_at' => Carbon::now()],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'content' => 'English post', 'user_id' => 2, 'created_at' => Carbon::now()],
                ['id' => 2, 'discussion_id' => 2, 'content' => 'German post', 'user_id' => 2, 'created_at' => Carbon::now()],
                ['id' => 3, 'discussion_id' => 3, 'content' => 'French post', 'user_id' => 2, 'created_at' => Carbon::now()],
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'Tag 1', 'slug' => 'tag-1'],
            ],
            User::class => [
                $this->normalUser(),
            ],
        ]);
    }

    /**
     * Extracts the JSON payload embedded in the Flarum HTML response.
     *
     * This function looks for the <script> tag with id "flarum-json-payload"
     * and returns its decoded JSON content.
     *
     * @param string $html
     *
     * @return array
     */
    protected function extractJsonPayload(string $html): array
    {
        // Look for the script tag that contains the JSON payload.
        if (preg_match('/<script id="flarum-json-payload" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
            $json = trim($matches[1]);
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->fail('Invalid JSON in flarum-json-payload: '.json_last_error_msg());
            }

            return $data;
        }
        $this->fail('Could not extract JSON payload from HTML');
    }

    public function languages(): array
    {
        return [
            'en' => 'English discussion',
            'de' => 'German discussion',
            'fr' => 'French discussion',
        ];
    }

    public static function routesProvider(): array
    {
        return [
            [''],
            ['all'],
            ['t/tag-1'],

        ];
    }

    #[Test]
    #[DataProvider('routesProvider')]
    public function all_discussions_are_returned_when_no_query_is_included_frontend(string $route)
    {
        $response = $this->send(
            $this->request('GET', "/$route")
        );

        $this->assertEquals(200, $response->getStatusCode());

        $body = $response->getBody()->getContents();

        // Extract the JSON payload from the HTML.
        $payload = $this->extractJsonPayload($body);

        $this->assertArrayHasKey('apiDocument', $payload, 'apiDocument key missing from payload');
        $apiDocument = $payload['apiDocument'];
        $this->assertArrayHasKey('data', $apiDocument, 'Data key missing in apiDocument');

        // Assert that there are three discussions returned.
        $this->assertCount(3, $apiDocument['data'], 'The number of discussions returned does not match the expectation.');

        $titles = array_map(function ($discussion) {
            return $discussion['attributes']['title'] ?? '';
        }, $apiDocument['data']);

        $this->assertContains('English discussion', $titles);
        $this->assertContains('German discussion', $titles);
        $this->assertContains('French discussion', $titles);

        // print_r($payload);
    }

    public function all_discussions_are_returned_from_the_api_when_no_query_is_included()
    {
        $response = $this->send(
            $this->request('GET', '/api/discussions')
        );

        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('data', $payload);
        $this->assertCount(3, $payload['data']);

        $titles = array_map(function ($discussion) {
            return $discussion['attributes']['title'] ?? '';
        }, $payload['data']);

        $this->assertContains('English discussion', $titles);
        $this->assertContains('German discussion', $titles);
        $this->assertContains('French discussion', $titles);
    }

    /**
     * Validate that supplying a language parameter filters the discussions to return only the expected discussion.
     */
    #[Test]
    #[DataProvider('routesProvider')]
    public function discussions_are_filtered_by_language_parameter_frontend(string $route)
    {
        $languages = $this->languages();

        foreach ($languages as $code => $expectedTitle) {
            $response = $this->send(
                $this->request('GET', "/$route")->withQueryParams(['language' => $code])
            );

            $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code for language $code");

            $body = $response->getBody()->getContents();
            $payload = $this->extractJsonPayload($body);

            $this->assertArrayHasKey('apiDocument', $payload, 'apiDocument key missing from payload');
            $apiDocument = $payload['apiDocument'];
            $this->assertArrayHasKey('data', $apiDocument, 'Data key missing in apiDocument');

            $discussions = $apiDocument['data'];
            $this->assertCount(1, $discussions, "For language '$code', expected exactly one discussion.");

            $title = $discussions[0]['attributes']['title'] ?? '';
            $this->assertEquals($expectedTitle, $title, "For language '$code', expected discussion title '$expectedTitle', got '$title'.");
        }
    }

    /**
     * Regression: security scanners (e.g. Intigriti / bug-bounty probes) send payloads like
     * `?language[$ne]=10` which made `Arr::get($params, 'language')` return an array, and that
     * array was then passed to `addQueryParams(..., string $language)` causing a TypeError /
     * HTTP 500. The middleware must ignore non-string language values and fall through.
     */
    #[Test]
    #[DataProvider('routesProvider')]
    public function non_string_language_parameter_is_ignored_frontend(string $route)
    {
        $payloads = [
            ['language' => ['$ne' => '10']],
            ['language' => ['de']],
            ['language' => ''],
        ];

        foreach ($payloads as $query) {
            $response = $this->send(
                $this->request('GET', "/$route")->withQueryParams($query)
            );

            $this->assertEquals(
                200,
                $response->getStatusCode(),
                'Malformed language query should not cause a server error: '.json_encode($query)
            );
        }
    }

    #[Test]
    public function discussions_are_filtered_by_language_parameter_api()
    {
        $languages = $this->languages();

        foreach ($languages as $code => $expectedTitle) {
            $response = $this->send(
                $this->request('GET', '/api/discussions')->withQueryParams(['filter' => ['language' => $code]])
            );

            $this->assertEquals(200, $response->getStatusCode(), "Unexpected status code for language $code");

            $payload = json_decode($response->getBody()->getContents(), true);

            $this->assertArrayHasKey('data', $payload);
            $this->assertCount(1, $payload['data'], "For language '$code', expected exactly one discussion.");

            $title = $payload['data'][0]['attributes']['title'] ?? '';
            $this->assertEquals($expectedTitle, $title, "For language '$code', expected discussion title '$expectedTitle', got '$title'.");
        }
    }

    /**
     * @return string[] the titles of the discussions the page lists
     */
    protected function listed(string $path, array $query = [], ?string $locale = null): array
    {
        $request = $this->request('GET', $path)->withQueryParams($query);

        if ($locale) {
            $request = $request->withCookieParams(['locale' => $locale]);
        }

        $response = $this->send($request);

        $this->assertEquals(200, $response->getStatusCode(), "$path?".http_build_query($query).' was not shown');

        $payload = $this->extractJsonPayload($response->getBody()->getContents());

        $titles = array_map(fn (array $discussion) => $discussion['attributes']['title'], $payload['apiDocument']['data']);
        sort($titles);

        return $titles;
    }

    #[Test]
    public function a_list_without_a_language_is_sent_to_the_readers_keeping_its_other_parameters(): void
    {
        $this->setting('fof-discussion-language.filter_language_on_http_request', '1');
        $this->setting('fof-discussion-language.showAnyLangOpt', '1');

        $response = $this->send($this->request('GET', '/all')->withQueryParams(['sort' => 'oldest'])->withCookieParams(['locale' => 'en']));

        $this->assertEquals(303, $response->getStatusCode());

        $location = parse_url($response->getHeaderLine('Location'));
        parse_str($location['query'] ?? '', $query);

        $this->assertEquals('/all', $location['path']);
        $this->assertEquals(['sort' => 'oldest', 'filter' => ['language' => 'en']], $query);
    }

    #[Test]
    public function a_reader_whose_language_cannot_be_told_sees_every_language(): void
    {
        $this->setting('fof-discussion-language.filter_language_on_http_request', '1');
        $this->setting('fof-discussion-language.showAnyLangOpt', '1');

        $this->assertEquals(['English discussion', 'French discussion', 'German discussion'], $this->listed('/all'));
    }

    #[Test]
    public function a_list_sent_to_the_readers_language_is_then_shown(): void
    {
        $this->setting('fof-discussion-language.filter_language_on_http_request', '1');
        $this->setting('fof-discussion-language.showAnyLangOpt', '1');

        $this->assertEquals(['English discussion'], $this->listed('/all', ['filter' => ['language' => 'en']], 'en'));
    }

    #[Test]
    public function a_language_chosen_from_the_list_is_kept(): void
    {
        $this->setting('fof-discussion-language.filter_language_on_http_request', '1');
        $this->setting('fof-discussion-language.showAnyLangOpt', '1');

        $this->assertEquals(['German discussion'], $this->listed('/all', ['filter' => ['language' => 'de']], 'en'));
        $this->assertEquals(['English discussion', 'French discussion', 'German discussion'], $this->listed('/all', ['filter' => ['language' => 'any']], 'en'));
    }

    #[Test]
    public function a_language_chosen_is_kept_where_every_language_at_once_is_not_offered(): void
    {
        $this->setting('fof-discussion-language.showAnyLangOpt', '0');

        $this->assertEquals(['German discussion'], $this->listed('/all', ['filter' => ['language' => 'de']]));
        $this->assertEquals(['English discussion'], $this->listed('/all', ['filter' => ['language' => 'any']]));
        $this->assertEquals(['English discussion'], $this->listed('/all'));
    }

    #[Test]
    public function the_tags_page_is_not_a_discussion_list(): void
    {
        $this->setting('fof-discussion-language.filter_language_on_http_request', '1');
        $this->setting('fof-discussion-language.showAnyLangOpt', '1');

        $response = $this->send($this->request('GET', '/tags')->withCookieParams(['locale' => 'en']));

        $this->assertEquals(200, $response->getStatusCode());
    }
}
