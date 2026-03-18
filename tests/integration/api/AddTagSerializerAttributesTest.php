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

use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;

class AddTagSerializerAttributesTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags');
        $this->extension('fof-follow-tags');
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
            'tags' => [
                ['id' => 1, 'name' => 'English Tag', 'slug' => 'english-tag', 'position' => 0, 'parent_id' => null, 'discussion_count' => 0, 'is_restricted' => 0, 'is_hidden' => 0],
                ['id' => 2, 'name' => 'German Tag',  'slug' => 'german-tag',  'position' => 1, 'parent_id' => null, 'discussion_count' => 0, 'is_restricted' => 0, 'is_hidden' => 0],
                ['id' => 3, 'name' => 'French Tag',  'slug' => 'french-tag',  'position' => 2, 'parent_id' => null, 'discussion_count' => 0, 'is_restricted' => 0, 'is_hidden' => 0],
                ['id' => 4, 'name' => 'Null Lang Tag',  'slug' => 'null-lang-tag',  'position' => 3, 'parent_id' => null, 'discussion_count' => 0, 'is_restricted' => 0, 'is_hidden' => 0],
                ['id' => 5, 'name' => 'Bad Lang Tag',   'slug' => 'bad-lang-tag',   'position' => 4, 'parent_id' => null, 'discussion_count' => 0, 'is_restricted' => 0, 'is_hidden' => 0],
            ],
            'tag_user' => [
                ['tag_id' => 1, 'user_id' => 2, 'is_hidden' => 0, 'subscription' => 'follow', 'dl_language_id' => 1],
                ['tag_id' => 2, 'user_id' => 2, 'is_hidden' => 0, 'subscription' => 'follow', 'dl_language_id' => 2],
                ['tag_id' => 3, 'user_id' => 2, 'is_hidden' => 0, 'subscription' => 'follow', 'dl_language_id' => 3],
            ],
        ]);
    }

    protected function getTagAttributes(int $authenticatedAs): array
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
    public function subscriptionLanguage_is_returned_for_subscribed_tags(): void
    {
        $tags = $this->getTagAttributes(2);

        $this->assertEquals('en', $tags['english-tag']['subscriptionLanguage']);
        $this->assertEquals('de', $tags['german-tag']['subscriptionLanguage']);
        $this->assertEquals('fr', $tags['french-tag']['subscriptionLanguage']);
    }

    /**
     * @test
     */
    public function subscriptionLanguage_is_null_when_no_language_preference(): void
    {
        $this->prepareDatabase([
            'tag_user' => [
                ['tag_id' => 4, 'user_id' => 2, 'is_hidden' => 0, 'subscription' => 'follow', 'dl_language_id' => null],
            ],
        ]);

        $tags = $this->getTagAttributes(2);

        $this->assertNull($tags['null-lang-tag']['subscriptionLanguage']);
    }

    /**
     * @test
     */
    public function subscriptionLanguage_is_null_for_nonexistent_language_id(): void
    {
        $this->prepareDatabase([
            'tag_user' => [
                ['tag_id' => 5, 'user_id' => 2, 'is_hidden' => 0, 'subscription' => 'follow', 'dl_language_id' => 999],
            ],
        ]);

        $tags = $this->getTagAttributes(2);

        $this->assertNull($tags['bad-lang-tag']['subscriptionLanguage']);
    }

    /**
     * @test
     */
    public function cache_is_invalidated_when_language_is_updated(): void
    {
        // 1. Populate the cache by fetching tags
        $tags = $this->getTagAttributes(2);
        $this->assertEquals('en', $tags['english-tag']['subscriptionLanguage']);

        // 2. Update language code via admin API (triggers cache forget)
        $response = $this->send(
            $this->request('PATCH', '/api/fof/discussion-language/1', [
                'authenticatedAs' => 1,
                'json' => [
                    'data' => [
                        'attributes' => [
                            'code' => 'en-updated',
                        ],
                    ],
                ],
            ])
        );
        $this->assertEquals(200, $response->getStatusCode());

        // 3. Fetch tags again — should see the updated code, not stale cache
        $tags = $this->getTagAttributes(2);
        $this->assertEquals('en-updated', $tags['english-tag']['subscriptionLanguage']);
    }

}
