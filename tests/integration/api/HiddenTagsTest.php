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

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\TestCase;
use FoF\DiscussionLanguage\DiscussionLanguage;
use PHPUnit\Framework\Attributes\Test;

/**
 * flarum/tags leaves discussions in tags hidden from All Discussions out of a
 * list with no filters. A list filtered by language is still that list (#27).
 */
class HiddenTagsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setting('default_route', '/all');

        $this->extension('flarum-tags', 'fof-discussion-language');

        $this->prepareDatabase([
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'Shown', 'slug' => 'shown', 'position' => 0],
                ['id' => 2, 'name' => 'Hidden', 'slug' => 'hidden', 'position' => 1, 'is_hidden' => true],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'Shown English', 'user_id' => 1, 'first_post_id' => 1, 'language_id' => 1],
                ['id' => 2, 'title' => 'Hidden English', 'user_id' => 1, 'first_post_id' => 2, 'language_id' => 1],
                ['id' => 3, 'title' => 'Shown German', 'user_id' => 1, 'first_post_id' => 3, 'language_id' => 2],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'user_id' => 1],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'user_id' => 1],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1],
                ['discussion_id' => 2, 'tag_id' => 2],
                ['discussion_id' => 3, 'tag_id' => 1],
            ],
        ]);
    }

    /**
     * @return int[]
     */
    protected function listed(array $filter): array
    {
        $response = $this->send($this->request('GET', '/api/discussions')->withQueryParams(['filter' => $filter]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $ids = array_map(fn (array $discussion) => (int) $discussion['id'], json_decode((string) $response->getBody(), true)['data']);
        sort($ids);

        return $ids;
    }

    #[Test]
    public function all_discussions_in_a_language_leave_out_hidden_tags(): void
    {
        $this->assertEquals([1], $this->listed(['language' => 'en']));
    }

    #[Test]
    public function a_hidden_tags_own_discussions_in_a_language_are_listed(): void
    {
        $this->assertEquals([2], $this->listed(['language' => 'en', 'tag' => 'hidden']));
    }

    #[Test]
    public function all_discussions_in_every_language_leave_out_hidden_tags_as_before(): void
    {
        $this->assertEquals([1, 3], $this->listed([]));
        $this->assertEquals([1, 3], $this->listed(['language' => 'any']));
    }

    #[Test]
    public function the_forums_list_in_the_readers_language_leaves_out_hidden_tags(): void
    {
        $this->setting('fof-discussion-language.showAnyLangOpt', '0');

        $response = $this->send($this->request('GET', '/all'));

        $this->assertEquals(200, $response->getStatusCode());

        preg_match('/<script id="flarum-json-payload" type="application\/json">(.*?)<\/script>/s', (string) $response->getBody(), $matches);
        $titles = array_map(fn (array $discussion) => $discussion['attributes']['title'], json_decode($matches[1], true)['apiDocument']['data']);

        $this->assertEquals(['Shown English'], $titles);
    }
}
