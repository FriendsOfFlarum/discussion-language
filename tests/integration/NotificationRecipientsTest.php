<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Tests\integration;

use Flarum\Database\AbstractModel;
use Flarum\Discussion\Discussion;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Post\Post;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\DiscussionLanguage\CheckNotificationRecipients;
use FoF\DiscussionLanguage\DiscussionLanguage;
use FoF\FollowTags\Notifications\NewDiscussionBlueprint;
use FoF\FollowTags\Notifications\NewDiscussionTagBlueprint;
use FoF\FollowTags\Notifications\NewPostBlueprint;
use PHPUnit\Framework\Attributes\Test;

/**
 * Those who follow a discussion's tags only in other languages aren't told
 * about it by fof/follow-tags. Only the tags they follow it by count.
 */
class NotificationRecipientsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags');
        $this->extension('fof-follow-tags');
        $this->extension('fof-discussion-language');

        $users = [];

        foreach (range(2, 10) as $id) {
            $users[] = ['id' => $id, 'username' => "user$id", 'email' => "user$id@machine.local", 'is_email_confirmed' => 1];
        }

        $this->prepareDatabase([
            User::class => $users,
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
                ['id' => 3, 'code' => 'fr', 'country' => 'FR'],
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'General', 'slug' => 'general', 'position' => 0],
                ['id' => 2, 'name' => 'Support', 'slug' => 'support', 'parent_id' => 1],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'German discussion', 'user_id' => 1, 'first_post_id' => 1, 'language_id' => 2],
                ['id' => 2, 'title' => 'No language discussion', 'user_id' => 1, 'first_post_id' => 2, 'language_id' => null],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'user_id' => 1],
                ['id' => 3, 'discussion_id' => 1, 'number' => 2, 'user_id' => 1],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1],
                ['discussion_id' => 1, 'tag_id' => 2],
                ['discussion_id' => 2, 'tag_id' => 1],
            ],
            'tag_user' => [
                // Both tags, in English.
                ['tag_id' => 1, 'user_id' => 2, 'subscription' => 'follow', 'dl_language_id' => 1],
                ['tag_id' => 2, 'user_id' => 2, 'subscription' => 'follow', 'dl_language_id' => 1],
                // Only the child tag, in English.
                ['tag_id' => 2, 'user_id' => 3, 'subscription' => 'follow', 'dl_language_id' => 1],
                // One tag in German.
                ['tag_id' => 1, 'user_id' => 4, 'subscription' => 'follow', 'dl_language_id' => 2],
                ['tag_id' => 2, 'user_id' => 4, 'subscription' => 'follow', 'dl_language_id' => 1],
                // One tag in every language.
                ['tag_id' => 1, 'user_id' => 5, 'subscription' => 'follow', 'dl_language_id' => 1],
                ['tag_id' => 2, 'user_id' => 5, 'subscription' => 'follow', 'dl_language_id' => null],
                // User 6 has no state at all.
                // English and French.
                ['tag_id' => 1, 'user_id' => 7, 'subscription' => 'follow', 'dl_language_id' => 1],
                ['tag_id' => 2, 'user_id' => 7, 'subscription' => 'follow', 'dl_language_id' => 3],
                // A state without a subscription, and the child tag in English.
                ['tag_id' => 1, 'user_id' => 8, 'subscription' => null, 'dl_language_id' => null],
                ['tag_id' => 2, 'user_id' => 8, 'subscription' => 'follow', 'dl_language_id' => 1],
                // Lurking the child tag in German.
                ['tag_id' => 2, 'user_id' => 9, 'subscription' => 'lurk', 'dl_language_id' => 2],
                // Following the parent tag in every language, lurking the child in English.
                ['tag_id' => 1, 'user_id' => 10, 'subscription' => 'follow', 'dl_language_id' => null],
                ['tag_id' => 2, 'user_id' => 10, 'subscription' => 'lurk', 'dl_language_id' => 1],
            ],
        ]);
    }

    protected function discussion(int $id): Discussion
    {
        // Boots the app, which seeds the database.
        $this->app();

        return Discussion::query()->find($id);
    }

    /**
     * @param int[] $userIds those fof/follow-tags chose
     *
     * @return int[]
     */
    protected function recipients(BlueprintInterface $blueprint, array $userIds): array
    {
        $users = User::query()->whereIn('id', $userIds)->orderBy('id')->get()->all();

        $recipients = $this->app()->getContainer()->make(CheckNotificationRecipients::class)($blueprint, $users);

        return array_map(fn (User $user) => $user->id, $recipients);
    }

    #[Test]
    public function those_following_only_in_other_languages_are_left_out(): void
    {
        $this->assertEquals([], $this->recipients(new NewDiscussionBlueprint($this->discussion(1)), [2, 7]));
    }

    #[Test]
    public function the_tags_they_do_not_follow_take_no_part(): void
    {
        $this->assertEquals([], $this->recipients(new NewDiscussionBlueprint($this->discussion(1)), [3, 8]));
    }

    #[Test]
    public function following_one_of_the_tags_in_its_language_is_enough(): void
    {
        $this->assertEquals([4, 9], $this->recipients(new NewDiscussionBlueprint($this->discussion(1)), [4, 9]));
    }

    #[Test]
    public function following_one_of_the_tags_in_every_language_is_enough(): void
    {
        $this->assertEquals([5, 10], $this->recipients(new NewDiscussionBlueprint($this->discussion(1)), [5, 10]));
    }

    #[Test]
    public function those_following_none_of_the_tags_are_not_judged(): void
    {
        $this->assertEquals([6], $this->recipients(new NewDiscussionBlueprint($this->discussion(1)), [6]));
    }

    #[Test]
    public function replies_are_judged_by_the_tags_lurked(): void
    {
        $this->app();

        $this->assertEquals([9], $this->recipients(new NewPostBlueprint(Post::query()->find(3)), [9, 10]));
    }

    #[Test]
    public function retagging_is_judged_by_the_tags_followed(): void
    {
        $discussion = $this->discussion(1);

        $this->assertEquals([4, 9], $this->recipients(new NewDiscussionTagBlueprint(User::query()->find(1), $discussion), [2, 3, 4, 9]));
    }

    #[Test]
    public function the_followers_languages_are_read_together(): void
    {
        $blueprint = new NewDiscussionBlueprint($this->discussion(1));
        $database = $this->database();

        $database->enableQueryLog();
        $database->flushQueryLog();

        $this->recipients($blueprint, range(2, 10));

        // The users themselves, the discussion's tags, and their followers' states.
        $this->assertLessThanOrEqual(3, count($database->getQueryLog()));
    }

    #[Test]
    public function a_discussion_without_a_language_reaches_everyone(): void
    {
        $this->assertEquals([2, 3], $this->recipients(new NewDiscussionBlueprint($this->discussion(2)), [2, 3]));
    }

    #[Test]
    public function other_notifications_reach_everyone(): void
    {
        $blueprint = new class($this->discussion(1)) implements BlueprintInterface {
            public function __construct(protected Discussion $discussion)
            {
            }

            public function getFromUser(): ?User
            {
                return null;
            }

            public function getSubject(): ?AbstractModel
            {
                return $this->discussion;
            }

            public function getData(): mixed
            {
                return null;
            }

            public static function getType(): string
            {
                return 'discussionRenamed';
            }

            public static function getSubjectModel(): string
            {
                return Discussion::class;
            }
        };

        $this->assertEquals([2, 7], $this->recipients($blueprint, [2, 7]));
    }
}
