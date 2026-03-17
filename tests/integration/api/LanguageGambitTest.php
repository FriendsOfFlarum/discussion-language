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

class LanguageGambitTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags');
        $this->extension('fof-discussion-language');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
            ],
            'discussion_languages' => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
                ['id' => 3, 'code' => 'fr', 'country' => 'FR'],
            ],
            'discussions' => [
                ['id' => 1, 'title' => 'English discussion', 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1, 'language_id' => 1],
                ['id' => 2, 'title' => 'German discussion',  'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 1, 'first_post_id' => 2, 'comment_count' => 1, 'language_id' => 2],
                ['id' => 3, 'title' => 'French discussion',  'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 1, 'first_post_id' => 3, 'comment_count' => 1, 'language_id' => 3],
                ['id' => 4, 'title' => 'No language discussion', 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 1, 'first_post_id' => 4, 'comment_count' => 1, 'language_id' => null],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'type' => 'comment', 'content' => '<t><p>English post</p></t>', 'user_id' => 1, 'created_at' => Carbon::now()],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'type' => 'comment', 'content' => '<t><p>German post</p></t>',  'user_id' => 1, 'created_at' => Carbon::now()],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'type' => 'comment', 'content' => '<t><p>French post</p></t>',  'user_id' => 1, 'created_at' => Carbon::now()],
                ['id' => 4, 'discussion_id' => 4, 'number' => 1, 'type' => 'comment', 'content' => '<t><p>No language post</p></t>', 'user_id' => 1, 'created_at' => Carbon::now()],
            ],
        ]);
    }

    protected function apiTitles(string $url, array $query = []): array
    {
        $request = $this->request('GET', $url);

        if ($query) {
            $request = $request->withQueryParams($query);
        }

        $response = $this->send($request);

        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode($response->getBody()->getContents(), true);

        return array_column(
            array_map(fn ($d) => $d['attributes'], $payload['data']),
            'title'
        );
    }

    // -------------------------------------------------------------------------
    // Filter API (filter[language]=<code>)
    // -------------------------------------------------------------------------

    /**
     * @test
     */
    public function filter_returns_only_matching_language(): void
    {
        $titles = $this->apiTitles('/api/discussions', ['filter' => ['language' => 'en']]);

        $this->assertContains('English discussion', $titles);
        $this->assertNotContains('German discussion', $titles);
        $this->assertNotContains('French discussion', $titles);
        $this->assertNotContains('No language discussion', $titles);
    }

    /**
     * @test
     */
    public function filter_returns_correct_discussion_for_each_language(): void
    {
        $cases = [
            'en' => 'English discussion',
            'de' => 'German discussion',
            'fr' => 'French discussion',
        ];

        foreach ($cases as $code => $expectedTitle) {
            $titles = $this->apiTitles('/api/discussions', ['filter' => ['language' => $code]]);

            $this->assertCount(1, $titles, "Expected exactly one result for language '$code'");
            $this->assertContains($expectedTitle, $titles, "Expected '$expectedTitle' for language '$code'");
        }
    }

    /**
     * @test
     */
    public function filter_with_unknown_language_returns_no_discussions(): void
    {
        $titles = $this->apiTitles('/api/discussions', ['filter' => ['language' => 'xx']]);

        $this->assertEmpty($titles);
    }

    /**
     * @test
     */
    public function filter_negation_excludes_matching_language(): void
    {
        // Negation uses a leading '-' on the filter key: filter[-language]=en
        $titles = $this->apiTitles('/api/discussions', ['filter' => ['-language' => 'en']]);

        $this->assertNotContains('English discussion', $titles);
        $this->assertContains('German discussion', $titles);
        $this->assertContains('French discussion', $titles);
    }

    // -------------------------------------------------------------------------
    // Search gambit (filter[q]=language:<code>)
    // -------------------------------------------------------------------------

    /**
     * @test
     */
    public function search_gambit_returns_only_matching_language(): void
    {
        $titles = $this->apiTitles('/api/discussions', ['filter' => ['q' => 'language:en']]);

        $this->assertContains('English discussion', $titles);
        $this->assertNotContains('German discussion', $titles);
        $this->assertNotContains('French discussion', $titles);
    }

    /**
     * @test
     */
    public function search_gambit_returns_correct_discussion_for_each_language(): void
    {
        $cases = [
            'en' => 'English discussion',
            'de' => 'German discussion',
            'fr' => 'French discussion',
        ];

        foreach ($cases as $code => $expectedTitle) {
            $titles = $this->apiTitles('/api/discussions', ['filter' => ['q' => "language:$code"]]);

            $this->assertCount(1, $titles, "Expected exactly one result for gambit language:$code");
            $this->assertContains($expectedTitle, $titles, "Expected '$expectedTitle' for gambit language:$code");
        }
    }

    /**
     * @test
     */
    public function search_gambit_with_unknown_language_returns_no_discussions(): void
    {
        $titles = $this->apiTitles('/api/discussions', ['filter' => ['q' => 'language:xx']]);

        $this->assertEmpty($titles);
    }

    /**
     * @test
     */
    public function search_gambit_negation_excludes_matching_language(): void
    {
        $titles = $this->apiTitles('/api/discussions', ['filter' => ['q' => '-language:en']]);

        $this->assertNotContains('English discussion', $titles);
        $this->assertContains('German discussion', $titles);
        $this->assertContains('French discussion', $titles);
    }

    /**
     * @test
     */
    public function search_gambit_comma_separated_codes_match_multiple_languages(): void
    {
        $titles = $this->apiTitles('/api/discussions', ['filter' => ['q' => 'language:en,de']]);

        $this->assertContains('English discussion', $titles);
        $this->assertContains('German discussion', $titles);
        $this->assertNotContains('French discussion', $titles);
    }

    /**
     * @test
     */
    public function filter_comma_separated_codes_match_multiple_languages(): void
    {
        $titles = $this->apiTitles('/api/discussions', ['filter' => ['language' => 'en,de']]);

        $this->assertContains('English discussion', $titles);
        $this->assertContains('German discussion', $titles);
        $this->assertNotContains('French discussion', $titles);
    }
}
