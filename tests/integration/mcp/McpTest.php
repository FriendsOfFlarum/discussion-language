<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Tests\integration\mcp;

use Flarum\Discussion\Discussion;
use Flarum\Mcp\Testing\McpTestCase;
use Flarum\Post\Post;
use Flarum\Tags\Tag;
use Flarum\User\User;
use FoF\DiscussionLanguage\DiscussionLanguage;
use PHPUnit\Framework\Attributes\Test;

class McpTest extends McpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'fof-discussion-language');

        $this->prepareDatabase([
            User::class => [$this->normalUser()],
            'group_permission' => [['group_id' => 2, 'permission' => 'mcp.use']],
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
                ['id' => 3, 'code' => 'fr', 'country' => 'FR'],
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'General', 'slug' => 'general', 'position' => 0, 'is_primary' => true],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'In English', 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1, 'last_post_number' => 1, 'language_id' => 1],
                ['id' => 2, 'title' => 'Auf Deutsch', 'user_id' => 1, 'first_post_id' => 2, 'comment_count' => 1, 'last_post_number' => 1, 'language_id' => 2],
                ['id' => 3, 'title' => 'En français', 'user_id' => 1, 'first_post_id' => 3, 'comment_count' => 1, 'last_post_number' => 1, 'language_id' => 3],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Hello</p></t>'],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Hallo</p></t>'],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Bonjour</p></t>'],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1],
                ['discussion_id' => 2, 'tag_id' => 1],
                ['discussion_id' => 3, 'tag_id' => 1],
            ],
        ]);
    }

    #[Test]
    public function start_discussion_takes_a_language_code(): void
    {
        $auth = $this->bearer($this->developerToken(2));

        $schema = array_column($this->rpcResult($this->modern('tools/list', [], $auth))['tools'], 'inputSchema', 'name')['start_discussion'];
        $this->assertArrayHasKey('language', $schema['properties']);
        $this->assertContains('language', $schema['required']);

        $created = $this->toolOutput($this->callTool('start_discussion', [
            'title' => 'Started over MCP',
            'content' => 'Hallo',
            'tags' => ['general'],
            'language' => 'DE',
        ], $auth));

        $this->assertSame(2, Discussion::query()->findOrFail($created['id'])->language_id);
    }

    #[Test]
    public function start_discussion_lists_the_codes_for_an_unknown_or_empty_language(): void
    {
        $auth = $this->bearer($this->developerToken(2));

        foreach ([['language' => 'xx'], ['language' => 'any'], ['language' => ' ']] as $language) {
            $error = $this->toolOutput($this->callTool('start_discussion', [
                'title' => 'Started over MCP',
                'content' => 'Bonjour',
                'tags' => ['general'],
                ...$language,
            ], $auth), expectError: true);

            $this->assertStringContainsString('en (English), de (German)', $error['error']);
        }

        $this->assertSame(3, Discussion::query()->count());
    }

    #[Test]
    public function search_discussions_filters_by_language(): void
    {
        $auth = $this->bearer($this->developerToken(2));

        $schema = array_column($this->rpcResult($this->modern('tools/list', [], $auth))['tools'], 'inputSchema', 'name')['search_discussions'];
        $this->assertArrayHasKey('language', $schema['properties']);

        $ids = fn (array $arguments) => array_column($this->toolOutput($this->callTool('search_discussions', $arguments, $auth))['data'], 'id');

        $this->assertSame(['2'], $ids(['language' => 'de']));
        $this->assertSame(['2'], $ids(['language' => 'DE']));
        $this->assertEqualsCanonicalizing(['1', '2'], $ids(['language' => 'en, de']));
        $this->assertEqualsCanonicalizing(['1', '2', '3'], $ids([]));

        $error = $this->toolOutput($this->callTool('search_discussions', ['language' => 'en,xx'], $auth), expectError: true);
        $this->assertStringContainsString('Unknown language: xx.', $error['error']);
        $this->assertStringContainsString('en (English), de (German), fr (French)', $error['error']);
    }

    #[Test]
    public function the_overview_lists_the_forums_languages(): void
    {
        $instructions = array_column($this->overview($this->bearer($this->developerToken(2)))['extensions'], 'instructions', 'id')['fof-discussion-language'];

        $this->assertStringContainsString('start_discussion', $instructions);
        $this->assertStringContainsString('en (English), de (German), fr (French)', $instructions);

        $guest = array_column($this->overview()['extensions'], 'instructions', 'id')['fof-discussion-language'];

        $this->assertStringNotContainsString('start_discussion', $guest);
        $this->assertStringContainsString('en (English), de (German), fr (French)', $guest);
    }

    #[Test]
    public function the_overview_says_who_may_change_any_discussions_language(): void
    {
        $this->prepareDatabase([
            User::class => [['id' => 3, 'username' => 'moderator', 'email' => 'mod@machine.local', 'is_email_confirmed' => 1]],
            'group_user' => [['user_id' => 3, 'group_id' => 4]],
            'group_permission' => [['group_id' => 4, 'permission' => 'discussion.changeLanguageModerate']],
        ]);

        $can = fn (int $userId) => array_column($this->overview($this->bearer($this->developerToken($userId)))['you']['can'], 'label', 'ability');

        $this->assertSame('Change any discussion\'s language', $can(3)['discussion.changeLanguageModerate'] ?? null);
        $this->assertArrayNotHasKey('discussion.changeLanguageModerate', $can(2));
    }

    /**
     * @param array<string, string> $auth
     * @return array<string, mixed>
     */
    protected function overview(array $auth = []): array
    {
        return json_decode($this->rpcResult($this->modern('resources/read', ['uri' => 'flarum://forum'], $auth, 'flarum://forum'))['contents'][0]['text'], true);
    }

    #[Test]
    public function describe_api_explains_the_language_relationship(): void
    {
        $auth = $this->bearer($this->developerToken(2));

        $discussions = $this->toolOutput($this->callTool('describe_api', ['type' => 'discussions'], $auth));
        $relationships = array_column($discussions['relationships'], null, 'name');
        $this->assertStringStartsWith('The language the discussion is written in.', $relationships['language']['description']);
    }
}
