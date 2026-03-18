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

class TagLocalizedLastDiscussionSerializerTest extends TestCase
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
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'type' => 'comment', 'content' => '<t><p>English post</p></t>', 'user_id' => 1, 'created_at' => Carbon::now()],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'type' => 'comment', 'content' => '<t><p>German post</p></t>',  'user_id' => 1, 'created_at' => Carbon::now()],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'type' => 'comment', 'content' => '<t><p>French post</p></t>',  'user_id' => 1, 'created_at' => Carbon::now()],
            ],
            'tags' => [
                ['id'                           => 1, 'name' => 'Multi-lang Tag', 'slug' => 'multi-lang-tag', 'position' => 0, 'parent_id' => null, 'discussion_count' => 3, 'is_restricted' => 0, 'is_hidden' => 0,
                    'localised_last_discussion' => json_encode([
                        '1' => ['id' => 1, 'at' => Carbon::now()->timestamp, 'user_id' => 1],
                        '2' => ['id' => 2, 'at' => Carbon::now()->timestamp, 'user_id' => 1],
                        '3' => ['id' => 3, 'at' => Carbon::now()->timestamp, 'user_id' => 1],
                    ])],
                ['id'                           => 2, 'name' => 'Empty Tag', 'slug' => 'empty-tag', 'position' => 1, 'parent_id' => null, 'discussion_count' => 0, 'is_restricted' => 0, 'is_hidden' => 0,
                    'localised_last_discussion' => '{}'],
                ['id'                           => 3, 'name' => 'Stale Tag', 'slug' => 'stale-tag', 'position' => 2, 'parent_id' => null, 'discussion_count' => 0, 'is_restricted' => 0, 'is_hidden' => 0,
                    'localised_last_discussion' => json_encode([
                        '1' => ['id' => 999, 'at' => Carbon::now()->timestamp, 'user_id' => 1],
                    ])],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1],
                ['discussion_id' => 2, 'tag_id' => 1],
                ['discussion_id' => 3, 'tag_id' => 1],
            ],
        ]);
    }

    protected function getTagAttributes(int $authenticatedAs = 1): array
    {
        $response = $this->send(
            $this->request('GET', '/api/tags', [
                'authenticatedAs' => $authenticatedAs,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode($response->getBody()->getContents(), true);

        $tags = [];

        foreach ($payload['data'] as $tag) {
            $tags[$tag['attributes']['slug']] = $tag['attributes'];
        }

        return $tags;
    }

    /**
     * @test
     */
    public function localisedLastDiscussion_includes_discussion_titles(): void
    {
        $tags = $this->getTagAttributes();
        $localised = $tags['multi-lang-tag']['localisedLastDiscussion'];

        $this->assertEquals('English discussion', $localised['1']['title']);
        $this->assertEquals('German discussion', $localised['2']['title']);
        $this->assertEquals('French discussion', $localised['3']['title']);
    }

    /**
     * @test
     */
    public function localisedLastDiscussion_handles_empty_json(): void
    {
        $tags = $this->getTagAttributes();
        $localised = $tags['empty-tag']['localisedLastDiscussion'];

        // Empty JSON object should come back as empty array/null, not crash
        $this->assertEmpty($localised);
    }

    /**
     * @test
     */
    public function localisedLastDiscussion_handles_deleted_discussion(): void
    {
        $tags = $this->getTagAttributes();
        $localised = $tags['stale-tag']['localisedLastDiscussion'];

        // Discussion 999 doesn't exist — title should be empty string, not crash
        $this->assertEquals('', $localised['1']['title']);
    }
}
