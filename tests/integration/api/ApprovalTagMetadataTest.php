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
use Flarum\Testing\integration\TestCase;
use FoF\DiscussionLanguage\DiscussionLanguage;
use PHPUnit\Framework\Attributes\Test;

/**
 * Content awaiting flarum/approval is private, so it becomes its language's
 * last discussion in a tag only once approved.
 */
class ApprovalTagMetadataTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'flarum-flags', 'flarum-approval', 'fof-discussion-language');

        $twoDaysAgo = Carbon::now()->subDays(2);
        $yesterday = Carbon::now()->subDay();
        $anHourAgo = Carbon::now()->subHour();

        $this->prepareDatabase([
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'Approved English', 'user_id' => 1, 'first_post_id' => 1, 'last_post_id' => 1, 'last_post_number' => 1, 'language_id' => 1, 'created_at' => $twoDaysAgo, 'last_posted_at' => $twoDaysAgo, 'last_posted_user_id' => 1],
                ['id' => 2, 'title' => 'English awaiting approval', 'user_id' => 1, 'first_post_id' => 2, 'last_post_id' => 2, 'last_post_number' => 1, 'language_id' => 1, 'created_at' => $anHourAgo, 'last_posted_at' => $anHourAgo, 'last_posted_user_id' => 1, 'is_approved' => false, 'is_private' => true],
                ['id' => 3, 'title' => 'Approved English, more recent', 'user_id' => 1, 'first_post_id' => 3, 'last_post_id' => 3, 'last_post_number' => 1, 'language_id' => 1, 'created_at' => $yesterday, 'last_posted_at' => $yesterday, 'last_posted_user_id' => 1],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1, 'created_at' => $twoDaysAgo],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'user_id' => 1, 'created_at' => $anHourAgo, 'is_approved' => false, 'is_private' => true],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'user_id' => 1, 'created_at' => $yesterday],
                // A reply to the oldest discussion, awaiting approval.
                ['id' => 4, 'discussion_id' => 1, 'number' => 2, 'user_id' => 1, 'created_at' => $anHourAgo, 'is_approved' => false, 'is_private' => true],
            ],
            Tag::class => [
                ['id'                           => 1, 'name' => 'General', 'slug' => 'general', 'position' => 0,
                    'localised_last_discussion' => json_encode(['1' => ['id' => 3, 'at' => $yesterday->timestamp, 'user_id' => 1]])],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1],
                ['discussion_id' => 2, 'tag_id' => 1],
                ['discussion_id' => 3, 'tag_id' => 1],
            ],
        ]);
    }

    protected function approve(int $postId): void
    {
        $response = $this->send(
            $this->request('PATCH', "/api/posts/$postId", [
                'authenticatedAs' => 1,
                'json'            => ['data' => ['type' => 'posts', 'id' => (string) $postId, 'attributes' => ['isApproved' => true]]],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
    }

    protected function englishLastDiscussion(): ?int
    {
        return json_decode(Tag::query()->find(1)->localised_last_discussion, true)['1']['id'] ?? null;
    }

    #[Test]
    public function an_approved_discussion_becomes_its_languages_last(): void
    {
        $this->approve(2);

        $this->assertEquals(2, $this->englishLastDiscussion());
    }

    #[Test]
    public function an_approved_reply_makes_its_discussion_its_languages_last(): void
    {
        $this->approve(4);

        $this->assertEquals(1, $this->englishLastDiscussion());
    }
}
