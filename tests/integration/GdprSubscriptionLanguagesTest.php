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

use Flarum\Gdpr\DataProcessor;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\DiscussionLanguage\Data\SubscriptionLanguages;
use FoF\DiscussionLanguage\DiscussionLanguage;
use PHPUnit\Framework\Attributes\Test;

/**
 * flarum/gdpr erases the language a user chose for each tag they follow, which
 * fof/follow-tags' own data type leaves in place.
 */
class GdprSubscriptionLanguagesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'flarum-gdpr', 'fof-discussion-language');

        $this->prepareDatabase([
            User::class => [
                ['id' => 2, 'username' => 'erased', 'email' => 'erased@machine.local', 'is_email_confirmed' => 1],
                ['id' => 3, 'username' => 'other', 'email' => 'other@machine.local', 'is_email_confirmed' => 1],
            ],
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'General', 'slug' => 'general'],
                ['id' => 2, 'name' => 'Support', 'slug' => 'support'],
            ],
            'tag_user' => [
                ['tag_id' => 1, 'user_id' => 2, 'dl_language_id' => 1],
                ['tag_id' => 2, 'user_id' => 2, 'dl_language_id' => 1],
                ['tag_id' => 1, 'user_id' => 3, 'dl_language_id' => 1],
            ],
        ]);
    }

    protected function type(): SubscriptionLanguages
    {
        return $this->app()->getContainer()->make(SubscriptionLanguages::class, [
            'user'           => User::query()->find(2),
            'erasureRequest' => null,
        ]);
    }

    /**
     * @return array<int, int|null> each tag's language for the user, by tag
     */
    protected function languagesOf(int $userId): array
    {
        return $this->database()->table('tag_user')->where('user_id', $userId)->orderBy('tag_id')->pluck('dl_language_id', 'tag_id')->all();
    }

    #[Test]
    public function the_type_is_registered(): void
    {
        $this->app();

        $this->assertArrayHasKey(SubscriptionLanguages::class, $this->app()->getContainer()->make(DataProcessor::class)->types());
    }

    #[Test]
    public function anonymising_a_user_clears_their_languages_and_only_theirs(): void
    {
        $this->type()->anonymize();

        $this->assertEquals([1 => null, 2 => null], $this->languagesOf(2));
        $this->assertEquals([1 => 1], $this->languagesOf(3));
    }

    #[Test]
    public function deleting_a_user_clears_their_languages(): void
    {
        $this->type()->delete();

        $this->assertEquals([1 => null, 2 => null], $this->languagesOf(2));
    }

    #[Test]
    public function the_export_leaves_them_to_follow_tags(): void
    {
        // fof/follow-tags exports each of the user's tag_user rows, this column with them.
        $this->assertNull($this->type()->export());
    }
}
