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
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\DiscussionLanguage\DiscussionLanguage;
use PHPUnit\Framework\Attributes\Test;

/**
 * flarum/subscriptions leaves discussions the reader ignores out of All
 * Discussions. A list filtered by language is still that list.
 */
class IgnoredDiscussionsTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->setting('default_route', '/all');

        $this->extension('flarum-tags', 'flarum-subscriptions', 'fof-discussion-language');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'Read English', 'user_id' => 1, 'first_post_id' => 1, 'language_id' => 1, 'comment_count' => 1],
                ['id' => 2, 'title' => 'Ignored English', 'user_id' => 1, 'first_post_id' => 2, 'language_id' => 1, 'comment_count' => 1],
                ['id' => 3, 'title' => 'Read German', 'user_id' => 1, 'first_post_id' => 3, 'language_id' => 2, 'comment_count' => 1],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>One</p></t>'],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Two</p></t>'],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Three</p></t>'],
            ],
            'discussion_user' => [
                ['discussion_id' => 2, 'user_id' => 2, 'subscription' => 'ignore'],
            ],
        ]);
    }

    /**
     * @return int[]
     */
    protected function listed(array $filter): array
    {
        $response = $this->send(
            $this->request('GET', '/api/discussions', ['authenticatedAs' => 2])->withQueryParams(['filter' => $filter])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $ids = array_map(fn (array $discussion) => (int) $discussion['id'], json_decode((string) $response->getBody(), true)['data']);
        sort($ids);

        return $ids;
    }

    #[Test]
    public function all_discussions_leave_out_ignored_ones(): void
    {
        $this->assertEquals([1, 3], $this->listed([]));
    }

    #[Test]
    public function all_discussions_in_a_language_leave_out_ignored_ones(): void
    {
        $this->assertEquals([1], $this->listed(['language' => 'en']));
        $this->assertEquals([1, 3], $this->listed(['language' => 'any']));
    }

    #[Test]
    public function ignored_discussions_are_still_listed_when_asked_for(): void
    {
        $this->assertEquals([2], $this->listed(['language' => 'en', 'subscription' => 'ignoring']));
    }

    #[Test]
    public function the_forums_list_in_the_readers_language_leaves_out_ignored_ones(): void
    {
        $this->setting('fof-discussion-language.showAnyLangOpt', '0');

        $response = $this->send($this->request('GET', '/all', ['authenticatedAs' => 2]));

        $this->assertEquals(200, $response->getStatusCode());

        preg_match('/<script id="flarum-json-payload" type="application\/json">(.*?)<\/script>/s', (string) $response->getBody(), $matches);
        $titles = array_map(fn (array $discussion) => $discussion['attributes']['title'], json_decode($matches[1], true)['apiDocument']['data']);

        $this->assertEquals(['Read English'], $titles);
    }
}
