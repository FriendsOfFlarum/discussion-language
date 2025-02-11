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
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;

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
            'discussions' => [
                ['id' => 1, 'title' => 'English discussion', 'created_at' => Carbon::now(), 'user_id' => 2, 'first_post_id' => 1, 'language_id' => 1],
                ['id' => 2, 'title' => 'German discussion', 'created_at' => Carbon::now(), 'user_id' => 2, 'first_post_id' => 2, 'language_id' => 2],
                ['id' => 3, 'title' => 'French discussion', 'created_at' => Carbon::now(), 'user_id' => 2, 'first_post_id' => 3, 'language_id' => 3],
            ],
            'discussion_languages' => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
                ['id' => 3, 'code' => 'fr', 'country' => 'FR'],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1, 'created_at' => Carbon::now()],
                ['discussion_id' => 2, 'tag_id' => 1, 'created_at' => Carbon::now()],
                ['discussion_id' => 3, 'tag_id' => 1, 'created_at' => Carbon::now()],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'content' => 'English post', 'user_id' => 2, 'created_at' => Carbon::now()],
                ['id' => 2, 'discussion_id' => 2, 'content' => 'German post', 'user_id' => 2, 'created_at' => Carbon::now()],
                ['id' => 3, 'discussion_id' => 3, 'content' => 'French post', 'user_id' => 2, 'created_at' => Carbon::now()],
            ],
            'tags' => [
                ['id' => 1, 'name' => 'Tag 1', 'slug' => 'tag-1'],
            ],
            'users' => [
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

    public function routesProvider(): array
    {
        return [
            [''],
            ['all'],
            ['t/tag-1'],

        ];
    }

    /**
     * @test
     *
     * @dataProvider routesProvider
     */
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
     * @test
     *
     * @dataProvider routesProvider
     *
     * Validate that supplying a language parameter filters the discussions to return only the expected discussion.
     */
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
     * @test
     */
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
}
