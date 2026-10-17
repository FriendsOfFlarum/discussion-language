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
use Flarum\Group\Group;
use Flarum\Post\Post;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\DiscussionLanguage\DiscussionLanguage;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

class DiscussionLanguageTest extends TestCase
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
                ['id' => 3, 'username' => 'moderator', 'email' => 'moderator@machine.local', 'is_email_confirmed' => 1],
                ['id' => 4, 'username' => 'member', 'email' => 'member@machine.local', 'is_email_confirmed' => 1],
            ],
            'group_user' => [
                ['user_id' => 3, 'group_id' => Group::MODERATOR_ID],
            ],
            'group_permission' => [
                ['group_id' => Group::MODERATOR_ID, 'permission' => 'discussion.changeLanguageModerate'],
                // Members may see and reply in the restricted tag, moderators may also change languages there.
                ['group_id' => Group::MEMBER_ID, 'permission' => 'tag1.viewForum'],
                ['group_id' => Group::MEMBER_ID, 'permission' => 'tag1.discussion.reply'],
                ['group_id' => Group::MODERATOR_ID, 'permission' => 'tag1.discussion.changeLanguageModerate'],
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'Restricted', 'slug' => 'restricted', 'is_restricted' => true],
            ],
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'Just started', 'user_id' => 2, 'first_post_id' => 1, 'language_id' => 1, 'created_at' => Carbon::now()],
                ['id' => 2, 'title' => 'Started yesterday', 'user_id' => 2, 'first_post_id' => 2, 'language_id' => 1, 'created_at' => Carbon::now()->subDay()],
                ['id' => 3, 'title' => 'Moderator started yesterday', 'user_id' => 3, 'first_post_id' => 3, 'language_id' => 1, 'created_at' => Carbon::now()->subDay()],
                ['id' => 4, 'title' => 'Just started in a restricted tag', 'user_id' => 2, 'first_post_id' => 4, 'language_id' => 1, 'created_at' => Carbon::now()],
                ['id' => 5, 'title' => 'Started yesterday in a restricted tag', 'user_id' => 2, 'first_post_id' => 5, 'language_id' => 1, 'created_at' => Carbon::now()->subDay()],
            ],
            'discussion_tag' => [
                ['discussion_id' => 4, 'tag_id' => 1],
                ['discussion_id' => 5, 'tag_id' => 1],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 2],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'user_id' => 2],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'user_id' => 3],
                ['id' => 4, 'discussion_id' => 4, 'number' => 1, 'user_id' => 2],
                ['id' => 5, 'discussion_id' => 5, 'number' => 1, 'user_id' => 2],
            ],
        ]);
    }

    protected function start(array $relationships): ResponseInterface
    {
        return $this->send(
            $this->request('POST', '/api/discussions', [
                'authenticatedAs' => 1,
                'json'            => [
                    'data' => [
                        'type'          => 'discussions',
                        'attributes'    => ['title' => 'A new discussion', 'content' => 'Its first post'],
                        'relationships' => $relationships,
                    ],
                ],
            ])
        );
    }

    protected function changeLanguage(int $discussionId, string $languageId, int $actor): ResponseInterface
    {
        return $this->send(
            $this->request('PATCH', "/api/discussions/$discussionId", [
                'authenticatedAs' => $actor,
                'json'            => [
                    'data' => [
                        'type'          => 'discussions',
                        'id'            => (string) $discussionId,
                        'relationships' => ['language' => ['data' => ['type' => 'discussion-languages', 'id' => $languageId]]],
                    ],
                ],
            ])
        );
    }

    protected function languageOf(int $discussionId): ?int
    {
        return Discussion::query()->find($discussionId)->language_id;
    }

    #[Test]
    public function discussion_starts_in_the_chosen_language(): void
    {
        $response = $this->start(['language' => ['data' => ['type' => 'discussion-languages', 'id' => '2']]]);

        $this->assertEquals(201, $response->getStatusCode());

        $payload = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('2', $payload['data']['relationships']['language']['data']['id']);
        $this->assertContains('discussion-languages:2', array_map(fn ($resource) => $resource['type'].':'.$resource['id'], $payload['included']));
        $this->assertEquals(2, $this->languageOf((int) $payload['data']['id']));
    }

    #[Test]
    public function discussion_cannot_start_without_a_language(): void
    {
        $response = $this->start([]);

        $this->assertEquals(422, $response->getStatusCode());

        $errors = json_decode($response->getBody()->getContents(), true)['errors'];

        $this->assertEquals('/data/relationships/language', $errors[0]['source']['pointer']);
        $this->assertEquals(5, Discussion::query()->count());
    }

    #[Test]
    public function discussion_cannot_start_in_an_unknown_language(): void
    {
        $response = $this->start(['language' => ['data' => ['type' => 'discussion-languages', 'id' => '99']]]);

        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
        $this->assertLessThan(500, $response->getStatusCode());
        $this->assertEquals(5, Discussion::query()->count());
    }

    #[Test]
    public function discussion_cannot_start_in_any_language(): void
    {
        // "Any language" is a choice for reading, not a language to write in.
        $response = $this->start(['language' => ['data' => ['type' => 'discussion-languages', 'id' => 'any']]]);

        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
        $this->assertLessThan(500, $response->getStatusCode());
        $this->assertEquals(5, Discussion::query()->count());
    }

    #[Test]
    public function discussions_come_with_their_language(): void
    {
        $response = $this->send($this->request('GET', '/api/discussions', ['authenticatedAs' => 2]));

        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode($response->getBody()->getContents(), true);

        foreach ($payload['data'] as $discussion) {
            $this->assertEquals('1', $discussion['relationships']['language']['data']['id']);
        }

        $this->assertContains('discussion-languages:1', array_map(fn ($resource) => $resource['type'].':'.$resource['id'], $payload['included']));
    }

    #[Test]
    public function author_may_change_the_language_while_the_setting_allows(): void
    {
        $response = $this->send($this->request('GET', '/api/discussions/1', ['authenticatedAs' => 2]));
        $this->assertTrue(json_decode($response->getBody()->getContents(), true)['data']['attributes']['canChangeLanguage']);

        $this->assertEquals(200, $this->changeLanguage(1, '2', 2)->getStatusCode());
        $this->assertEquals(2, $this->languageOf(1));
    }

    #[Test]
    public function author_may_not_change_the_language_once_the_setting_no_longer_allows(): void
    {
        $response = $this->send($this->request('GET', '/api/discussions/2', ['authenticatedAs' => 2]));
        $this->assertFalse(json_decode($response->getBody()->getContents(), true)['data']['attributes']['canChangeLanguage']);

        $this->assertEquals(403, $this->changeLanguage(2, '2', 2)->getStatusCode());
        $this->assertEquals(1, $this->languageOf(2));
    }

    #[Test]
    public function author_may_change_the_language_indefinitely_when_the_setting_allows(): void
    {
        $this->setting('fof-discussion-language.allow_language_change', '-1');

        $this->assertEquals(200, $this->changeLanguage(2, '2', 2)->getStatusCode());
        $this->assertEquals(2, $this->languageOf(2));
    }

    #[Test]
    public function others_may_not_change_the_language(): void
    {
        $this->assertEquals(403, $this->changeLanguage(1, '2', 4)->getStatusCode());
        $this->assertEquals(1, $this->languageOf(1));
    }

    #[Test]
    public function moderator_may_change_the_language_of_any_discussion(): void
    {
        $this->assertEquals(200, $this->changeLanguage(2, '2', 3)->getStatusCode());
        $this->assertEquals(2, $this->languageOf(2));
    }

    #[Test]
    public function moderator_may_change_the_language_of_their_own_discussion_too(): void
    {
        // In 1.x, being the author ruled out the moderate permission.
        $this->assertEquals(200, $this->changeLanguage(3, '2', 3)->getStatusCode());
        $this->assertEquals(2, $this->languageOf(3));
    }

    /**
     * flarum/tags denies, in a restricted tag, any ability without a permission
     * of its own there, and nothing can grant `tag{id}.discussion.changeLanguage`.
     */
    #[Test]
    public function author_may_change_the_language_in_a_restricted_tag(): void
    {
        $this->assertEquals(200, $this->changeLanguage(4, '2', 2)->getStatusCode());
        $this->assertEquals(2, $this->languageOf(4));
    }

    #[Test]
    public function moderator_of_a_restricted_tag_may_change_the_language_there(): void
    {
        $this->assertEquals(200, $this->changeLanguage(5, '2', 3)->getStatusCode());
        $this->assertEquals(2, $this->languageOf(5));
    }

    #[Test]
    public function others_may_not_change_the_language_in_a_restricted_tag(): void
    {
        $this->assertEquals(403, $this->changeLanguage(4, '2', 4)->getStatusCode());
        $this->assertEquals(1, $this->languageOf(4));
    }
}
