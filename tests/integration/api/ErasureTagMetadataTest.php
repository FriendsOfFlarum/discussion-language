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
use Flarum\Gdpr\Events\Erased;
use Flarum\Gdpr\Models\ErasureRequest;
use Flarum\Post\Post;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\DiscussionLanguage\DiscussionLanguage;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\Attributes\Test;

/**
 * flarum/gdpr's deletion mode deletes a user's posts in one query, without the
 * post events that keep each language's last discussion in a tag up to date.
 */
class ErasureTagMetadataTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected Carbon $threeDaysAgo;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'flarum-gdpr', 'fof-discussion-language');

        $this->threeDaysAgo = Carbon::now()->subDays(3);
        $twoDaysAgo = Carbon::now()->subDays(2);
        $anHourAgo = Carbon::now()->subHour();

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
            ],
            Discussion::class => [
                // English's last, through the reply of the user who is erased.
                ['id' => 1, 'title' => 'Replied to', 'user_id' => 1, 'first_post_id' => 1, 'last_post_id' => 2, 'last_post_number' => 2, 'comment_count' => 2, 'language_id' => 1, 'created_at' => $this->threeDaysAgo, 'last_posted_at' => $anHourAgo, 'last_posted_user_id' => 2],
                ['id' => 2, 'title' => 'Earlier English', 'user_id' => 1, 'first_post_id' => 3, 'last_post_id' => 3, 'last_post_number' => 1, 'language_id' => 1, 'created_at' => $twoDaysAgo, 'last_posted_at' => $twoDaysAgo, 'last_posted_user_id' => 1],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1, 'created_at' => $this->threeDaysAgo],
                ['id' => 2, 'discussion_id' => 1, 'number' => 2, 'user_id' => 2, 'created_at' => $anHourAgo],
                ['id' => 3, 'discussion_id' => 2, 'number' => 1, 'user_id' => 1, 'created_at' => $twoDaysAgo],
            ],
            Tag::class => [
                ['id'                           => 1, 'name' => 'General', 'slug' => 'general', 'position' => 0,
                    'localised_last_discussion' => json_encode(['1' => ['id' => 1, 'at' => $anHourAgo->timestamp, 'user_id' => 2]])],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1],
                ['discussion_id' => 2, 'tag_id' => 1],
            ],
        ]);
    }

    /**
     * As flarum/gdpr erases user 2: their reply goes in one query, and its
     * discussion is refreshed without an event.
     */
    protected function erase(string $mode): void
    {
        $this->app();

        if ($mode === ErasureRequest::MODE_DELETION) {
            Post::query()->where('user_id', 2)->delete();

            Discussion::query()->whereKey(1)->update(['last_post_id' => 1, 'last_post_number' => 1, 'last_posted_at' => $this->threeDaysAgo, 'last_posted_user_id' => 1]);
        }

        $user = User::query()->find(2);

        $this->app()->getContainer()->make(Dispatcher::class)->dispatch(new Erased($user->username, $user->email, $mode, $user));
    }

    protected function englishLastDiscussion(): ?int
    {
        return json_decode(Tag::query()->find(1)->localised_last_discussion, true)['1']['id'] ?? null;
    }

    #[Test]
    public function erasing_a_users_posts_brings_back_the_languages_last_discussion_before(): void
    {
        $this->erase(ErasureRequest::MODE_DELETION);

        $this->assertEquals(2, $this->englishLastDiscussion());
    }

    #[Test]
    public function anonymising_a_user_leaves_the_languages_last_discussion(): void
    {
        $this->erase(ErasureRequest::MODE_ANONYMIZATION);

        $this->assertEquals(1, $this->englishLastDiscussion());
    }
}
