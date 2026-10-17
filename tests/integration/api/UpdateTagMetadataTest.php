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
use PHPUnit\Framework\Attributes\Test;

/**
 * Each tag keeps the last discussion in each language, for the tags page.
 */
class UpdateTagMetadataTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags');
        $this->extension('fof-discussion-language');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
            ],
        ]);
    }

    /**
     * @return array<string, array{id: int, at: int, user_id: int|null}>
     */
    protected function localisedLastDiscussions(int $tagId): array
    {
        return json_decode(Tag::query()->find($tagId)->localised_last_discussion, true);
    }

    #[Test]
    public function starting_a_discussion_counts_it_once_in_its_tags(): void
    {
        $this->prepareDatabase([
            Tag::class => [
                ['id' => 1, 'name' => 'General', 'slug' => 'general', 'position' => 0, 'discussion_count' => 0, 'localised_last_discussion' => '{}'],
            ],
        ]);

        $response = $this->send(
            $this->request('POST', '/api/discussions', [
                'authenticatedAs' => 1,
                'json'            => [
                    'data' => [
                        'type'          => 'discussions',
                        'attributes'    => ['title' => 'A new discussion', 'content' => 'Its first post'],
                        'relationships' => [
                            'language' => ['data' => ['type' => 'discussion-languages', 'id' => '2']],
                            'tags'     => ['data' => [['type' => 'tags', 'id' => '1']]],
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(201, $response->getStatusCode());

        $discussionId = (int) json_decode($response->getBody()->getContents(), true)['data']['id'];

        // flarum/tags counts it; counting it here too counted every discussion twice.
        $this->assertEquals(1, Tag::query()->find(1)->discussion_count);
        $this->assertEquals($discussionId, $this->localisedLastDiscussions(1)['2']['id']);
    }

    /**
     * English's last discussion, an earlier English one, and the tag's last one, in German.
     */
    protected function languagesWithTheirLastDiscussions(): void
    {
        $at = fn (Carbon $time) => $time->timestamp;
        $anHourAgo = Carbon::now()->subHour();
        $twoDaysAgo = Carbon::now()->subDays(2);
        $threeDaysAgo = Carbon::now()->subDays(3);

        $localised = json_encode([
            '1' => ['id' => 1, 'at' => $at($twoDaysAgo), 'user_id' => 1],
            '2' => ['id' => 2, 'at' => $at($anHourAgo), 'user_id' => 1],
        ]);

        $this->prepareDatabase([
            Discussion::class => [
                ['id' => 1, 'title' => 'Latest English', 'user_id' => 1, 'first_post_id' => 1, 'last_post_id' => 1, 'last_post_number' => 1, 'language_id' => 1, 'last_posted_at' => $twoDaysAgo, 'last_posted_user_id' => 1],
                ['id' => 2, 'title' => 'Latest overall', 'user_id' => 1, 'first_post_id' => 2, 'last_post_id' => 2, 'last_post_number' => 1, 'language_id' => 2, 'last_posted_at' => $anHourAgo, 'last_posted_user_id' => 1],
                ['id' => 3, 'title' => 'Earlier English', 'user_id' => 1, 'first_post_id' => 3, 'last_post_id' => 3, 'last_post_number' => 1, 'language_id' => 1, 'last_posted_at' => $threeDaysAgo, 'last_posted_user_id' => 1],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1, 'created_at' => $twoDaysAgo],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'user_id' => 1, 'created_at' => $anHourAgo],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'user_id' => 1, 'created_at' => $threeDaysAgo],
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'General', 'slug' => 'general', 'position' => 0, 'discussion_count' => 3, 'last_posted_discussion_id' => 2, 'last_posted_at' => $anHourAgo, 'localised_last_discussion' => $localised],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1],
                ['discussion_id' => 2, 'tag_id' => 1],
                ['discussion_id' => 3, 'tag_id' => 1],
            ],
        ]);
    }

    #[Test]
    public function hiding_a_languages_last_discussion_brings_back_the_one_before(): void
    {
        $this->languagesWithTheirLastDiscussions();

        $response = $this->send(
            $this->request('PATCH', '/api/discussions/1', [
                'authenticatedAs' => 1,
                'json'            => ['data' => ['type' => 'discussions', 'id' => '1', 'attributes' => ['isHidden' => true]]],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $localised = $this->localisedLastDiscussions(1);

        // English's last discussion was not the tag's last one, so 1.x left it there.
        $this->assertEquals(3, $localised['1']['id']);
        $this->assertEquals(2, $localised['2']['id']);
    }

    #[Test]
    public function deleting_the_only_post_of_a_languages_last_discussion_brings_back_the_one_before(): void
    {
        $this->languagesWithTheirLastDiscussions();

        // Deleting its only post deletes the discussion, with no event of its own.
        $response = $this->send(
            $this->request('DELETE', '/api/posts/1', ['authenticatedAs' => 1])
        );

        $this->assertEquals(204, $response->getStatusCode(), (string) $response->getBody());
        $this->assertNull(Discussion::query()->find(1));

        $localised = $this->localisedLastDiscussions(1);

        $this->assertEquals(3, $localised['1']['id']);
        $this->assertEquals(2, $localised['2']['id']);
    }

    protected function changeLanguage(int $discussionId, string $languageId): void
    {
        $response = $this->send(
            $this->request('PATCH', "/api/discussions/$discussionId", [
                'authenticatedAs' => 1,
                'json'            => ['data' => ['type' => 'discussions', 'id' => (string) $discussionId, 'relationships' => [
                    'language' => ['data' => ['type' => 'discussion-languages', 'id' => $languageId]],
                ]]],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
    }

    #[Test]
    public function changing_a_languages_last_discussion_to_another_brings_back_the_one_before(): void
    {
        $this->languagesWithTheirLastDiscussions();

        // English's last becomes German, but German's last is more recent.
        $this->changeLanguage(1, '2');

        $localised = $this->localisedLastDiscussions(1);

        $this->assertEquals(3, $localised['1']['id']);
        $this->assertEquals(2, $localised['2']['id']);
    }

    #[Test]
    public function changing_a_discussion_to_a_language_makes_it_that_languages_last_if_it_is_the_latest(): void
    {
        $this->languagesWithTheirLastDiscussions();

        // German's only discussion, the tag's latest, becomes English.
        $this->changeLanguage(2, '1');

        $localised = $this->localisedLastDiscussions(1);

        $this->assertEquals(2, $localised['1']['id']);
        $this->assertArrayNotHasKey('2', $localised);
    }
}
