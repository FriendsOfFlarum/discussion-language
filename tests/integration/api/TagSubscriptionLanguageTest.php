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

use Flarum\Tags\Tag;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\DiscussionLanguage\DiscussionLanguage;
use PHPUnit\Framework\Attributes\Test;

/**
 * The language fof/follow-tags subscribers chose to be notified in, which 1.x
 * stored in tag_user.dl_language_id.
 */
class TagSubscriptionLanguageTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags');
        $this->extension('fof-follow-tags');
        $this->extension('fof-discussion-language');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
                ['id' => 3, 'code' => 'fr', 'country' => 'FR'],
            ],
            Tag::class => [
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

    #[Test]
    public function subscriptionLanguage_is_returned_for_subscribed_tags(): void
    {
        $tags = $this->getTagAttributes(2);

        $this->assertEquals('en', $tags['english-tag']['subscriptionLanguage']);
        $this->assertEquals('de', $tags['german-tag']['subscriptionLanguage']);
        $this->assertEquals('fr', $tags['french-tag']['subscriptionLanguage']);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function guests_have_no_subscription_language(): void
    {
        $response = $this->send($this->request('GET', '/api/tags'));

        $this->assertEquals(200, $response->getStatusCode());

        foreach (json_decode($response->getBody()->getContents(), true)['data'] as $tag) {
            $this->assertNull($tag['attributes']['subscriptionLanguage']);
        }
    }

    #[Test]
    public function cache_is_invalidated_when_language_is_updated(): void
    {
        // 1. Populate the cache by fetching tags
        $tags = $this->getTagAttributes(2);
        $this->assertEquals('en', $tags['english-tag']['subscriptionLanguage']);

        // 2. Update language code via admin API (triggers cache forget)
        $response = $this->send(
            $this->request('PATCH', '/api/discussion-languages/1', [
                'authenticatedAs' => 1,
                'json'            => [
                    'data' => [
                        'type'       => 'discussion-languages',
                        'id'         => '1',
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

    protected function subscribe(int $tagId, array $attributes, ?int $actor = 2): \Psr\Http\Message\ResponseInterface
    {
        return $this->send(
            $this->request('PATCH', "/api/tags/$tagId", [
                'authenticatedAs' => $actor,
                'json'            => ['data' => ['type' => 'tags', 'id' => (string) $tagId, 'attributes' => $attributes]],
            ])
        );
    }

    protected function subscriptionLanguageId(int $tagId, int $userId = 2): ?int
    {
        $state = $this->database()->table('tag_user')->where('tag_id', $tagId)->where('user_id', $userId)->first();

        return $state?->dl_language_id === null ? null : (int) $state->dl_language_id;
    }

    #[Test]
    public function subscriber_chooses_the_language_with_a_new_subscription(): void
    {
        $response = $this->subscribe(4, ['subscription' => 'follow', 'subscriptionLanguage' => 'de']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('de', json_decode($response->getBody()->getContents(), true)['data']['attributes']['subscriptionLanguage']);
        $this->assertEquals(2, $this->subscriptionLanguageId(4));
        $this->assertEquals('follow', $this->database()->table('tag_user')->where('tag_id', 4)->where('user_id', 2)->value('subscription'));
    }

    #[Test]
    public function subscriber_changes_the_language_of_a_subscription(): void
    {
        $this->assertEquals(200, $this->subscribe(1, ['subscription' => 'follow', 'subscriptionLanguage' => 'fr'])->getStatusCode());
        $this->assertEquals(3, $this->subscriptionLanguageId(1));
    }

    #[Test]
    public function subscriber_chooses_every_language(): void
    {
        $this->assertEquals(200, $this->subscribe(1, ['subscription' => 'follow', 'subscriptionLanguage' => 'any'])->getStatusCode());
        $this->assertNull($this->subscriptionLanguageId(1));
    }

    #[Test]
    public function changing_a_subscription_without_a_language_keeps_it(): void
    {
        $this->assertEquals(200, $this->subscribe(1, ['subscription' => 'lurk'])->getStatusCode());
        $this->assertEquals(1, $this->subscriptionLanguageId(1));
    }

    #[Test]
    public function guests_cannot_choose_a_language(): void
    {
        // Refused before it reaches the field: a guest's write has no CSRF token.
        $status = $this->subscribe(4, ['subscription' => 'follow', 'subscriptionLanguage' => 'de'], null)->getStatusCode();

        $this->assertGreaterThanOrEqual(400, $status);
        $this->assertLessThan(500, $status);
        $this->assertNull($this->database()->table('tag_user')->where('tag_id', 4)->first());
    }

    #[Test]
    public function the_language_is_chosen_with_a_subscription_not_on_its_own(): void
    {
        // fof/follow-tags lets a subscriber change only their subscription, and what goes with it.
        $this->assertEquals(403, $this->subscribe(1, ['subscriptionLanguage' => 'fr'])->getStatusCode());
        $this->assertEquals(1, $this->subscriptionLanguageId(1));
    }
}
