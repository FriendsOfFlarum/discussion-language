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

class TagLocalisedLastDiscussionTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags');
        $this->extension('fof-discussion-language');

        $last = fn (int $id) => ['id' => $id, 'at' => Carbon::now()->timestamp, 'user_id' => 1];

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
                ['id' => 3, 'code' => 'fr', 'country' => 'FR'],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'English discussion', 'user_id' => 1, 'first_post_id' => 1, 'language_id' => 1],
                ['id' => 2, 'title' => 'German discussion',  'user_id' => 1, 'first_post_id' => 2, 'language_id' => 2],
                ['id' => 3, 'title' => 'French discussion',  'user_id' => 1, 'first_post_id' => 3, 'language_id' => 3],
                ['id' => 4, 'title' => 'Hidden discussion',  'user_id' => 1, 'first_post_id' => 4, 'language_id' => 1, 'hidden_at' => Carbon::now(), 'hidden_user_id' => 1],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'user_id' => 1],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'user_id' => 1],
                ['id' => 4, 'discussion_id' => 4, 'number' => 1, 'user_id' => 1],
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'Multi-lang Tag', 'slug' => 'multi-lang-tag', 'position' => 0, 'localised_last_discussion' => json_encode(['1' => $last(1), '2' => $last(2), '3' => $last(3)])],
                ['id' => 2, 'name' => 'Empty Tag', 'slug' => 'empty-tag', 'position' => 1, 'localised_last_discussion' => '{}'],
                ['id' => 3, 'name' => 'Stale Tag', 'slug' => 'stale-tag', 'position' => 2, 'localised_last_discussion' => json_encode(['1' => $last(999)])],
                // Created while the extension was disabled.
                ['id' => 4, 'name' => 'Untouched Tag', 'slug' => 'untouched-tag', 'position' => 3, 'localised_last_discussion' => null],
                ['id' => 5, 'name' => 'Hidden Tag', 'slug' => 'hidden-tag', 'position' => 4, 'localised_last_discussion' => json_encode(['1' => $last(4)])],
            ],
        ]);
    }

    protected function localisedLastDiscussions(int $actor = 2): array
    {
        $response = $this->send($this->request('GET', '/api/tags', ['authenticatedAs' => $actor]));

        $this->assertEquals(200, $response->getStatusCode());

        $tags = array_column(json_decode($response->getBody()->getContents(), true)['data'], 'attributes');

        return array_column($tags, 'localisedLastDiscussion', 'slug');
    }

    #[Test]
    public function each_language_has_its_last_discussion_with_its_title(): void
    {
        $localised = $this->localisedLastDiscussions()['multi-lang-tag'];

        $this->assertEquals('English discussion', $localised['1']['title']);
        $this->assertEquals('German discussion', $localised['2']['title']);
        $this->assertEquals('French discussion', $localised['3']['title']);
        $this->assertEquals(1, $localised['1']['id']);
    }

    #[Test]
    public function a_tag_without_any_has_none(): void
    {
        $tags = $this->localisedLastDiscussions();

        $this->assertEmpty($tags['empty-tag']);
        $this->assertEmpty($tags['untouched-tag']);
    }

    #[Test]
    public function a_deleted_discussion_has_no_title(): void
    {
        $this->assertEquals('', $this->localisedLastDiscussions()['stale-tag']['1']['title']);
    }

    #[Test]
    public function a_discussion_the_actor_cannot_see_has_no_title(): void
    {
        $this->assertEquals('', $this->localisedLastDiscussions(2)['hidden-tag']['1']['title']);
        $this->assertEquals('Hidden discussion', $this->localisedLastDiscussions(1)['hidden-tag']['1']['title']);
    }

    #[Test]
    public function the_titles_of_every_tag_are_loaded_together(): void
    {
        // One query per tag would be an N+1, which the test case reports.
        $tags = [];
        $discussions = [];
        $posts = [];

        foreach (range(10, 17) as $id) {
            $tags[] = ['id' => $id, 'name' => "Tag $id", 'slug' => "tag-$id", 'position' => $id, 'localised_last_discussion' => json_encode(['1' => ['id' => $id, 'at' => 0, 'user_id' => 1]])];
            $discussions[] = ['id' => $id, 'title' => "Discussion $id", 'user_id' => 1, 'first_post_id' => $id, 'language_id' => 1];
            $posts[] = ['id' => $id, 'discussion_id' => $id, 'number' => 1, 'user_id' => 1];
        }

        $this->prepareDatabase([Tag::class => $tags, Discussion::class => $discussions, Post::class => $posts]);

        $localised = $this->localisedLastDiscussions();

        foreach (range(10, 17) as $id) {
            $this->assertEquals("Discussion $id", $localised["tag-$id"]['1']['title']);
        }
    }
}
