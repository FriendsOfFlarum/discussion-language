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

/**
 * Ensures that stickied discussions appear at the top of the list when the
 * discussion list is filtered by language.
 *
 * Regression: after the URL scheme was migrated from ?language=en to
 * ?filter[language]=en in #68, the LanguageFilterGambit became a registered
 * active filter. PinStickiedDiscussionsToTop only re-ordered stickies when a
 * TagFilterGambit was active — any other filter caused an early return with no
 * sticky ordering applied.
 */
class StickyOrderingTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-sticky');
        $this->extension('flarum-tags');
        $this->extension('fof-discussion-language');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
            ],
            'discussion_languages' => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
            ],
            'discussions' => [
                // Older, stickied discussion — should appear first despite lower last_posted_at.
                ['id' => 1, 'title' => 'Sticky English discussion', 'created_at' => Carbon::now()->subDays(2), 'last_posted_at' => Carbon::now()->subDays(2), 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1, 'language_id' => 1, 'is_sticky' => 1],
                // Newer, non-sticky discussion — should appear after the sticky.
                ['id' => 2, 'title' => 'Regular English discussion', 'created_at' => Carbon::now()->subDay(), 'last_posted_at' => Carbon::now()->subDay(), 'user_id' => 1, 'first_post_id' => 2, 'comment_count' => 1, 'language_id' => 1, 'is_sticky' => 0],
                // Newest, non-sticky discussion — also after the sticky.
                ['id' => 3, 'title' => 'Another English discussion', 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 1, 'first_post_id' => 3, 'comment_count' => 1, 'language_id' => 1, 'is_sticky' => 0],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'type' => 'comment', 'content' => '<t><p>Sticky post</p></t>', 'user_id' => 1, 'created_at' => Carbon::now()->subDays(2)],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'type' => 'comment', 'content' => '<t><p>Regular post</p></t>', 'user_id' => 1, 'created_at' => Carbon::now()->subDay()],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'type' => 'comment', 'content' => '<t><p>Another post</p></t>', 'user_id' => 1, 'created_at' => Carbon::now()],
            ],
        ]);
    }

    protected function apiTitles(array $query = []): array
    {
        $request = $this->request('GET', '/api/discussions');

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

    /**
     * @test
     */
    public function sticky_discussion_is_first_when_filtered_by_language(): void
    {
        $titles = $this->apiTitles(['filter' => ['language' => 'en']]);

        $this->assertNotEmpty($titles);
        $this->assertEquals('Sticky English discussion', $titles[0], 'Stickied discussion should be pinned to the top of a language-filtered list');
    }

    /**
     * @test
     */
    public function sticky_discussion_is_first_without_language_filter(): void
    {
        $titles = $this->apiTitles();

        $this->assertNotEmpty($titles);
        $this->assertEquals('Sticky English discussion', $titles[0], 'Stickied discussion should be pinned to the top of the unfiltered list');
    }
}
