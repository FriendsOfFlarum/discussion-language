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
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'General', 'slug' => 'general', 'position' => 0, 'is_primary' => true],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'In English', 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1, 'last_post_number' => 1, 'language_id' => 1],
                ['id' => 2, 'title' => 'Auf Deutsch', 'user_id' => 1, 'first_post_id' => 2, 'comment_count' => 1, 'last_post_number' => 1, 'language_id' => 2],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Hello</p></t>'],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Hallo</p></t>'],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1],
                ['discussion_id' => 2, 'tag_id' => 1],
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
            'language' => 'de',
        ], $auth));

        $this->assertSame(2, Discussion::query()->findOrFail($created['id'])->language_id);
    }

    #[Test]
    public function start_discussion_lists_the_codes_for_an_unknown_or_missing_language(): void
    {
        $auth = $this->bearer($this->developerToken(2));

        foreach ([['language' => 'fr'], ['language' => 'any'], ['language' => ' ']] as $language) {
            $error = $this->toolOutput($this->callTool('start_discussion', [
                'title' => 'Started over MCP',
                'content' => 'Bonjour',
                'tags' => ['general'],
                ...$language,
            ], $auth), expectError: true);

            $this->assertStringContainsString('en (English), de (German)', $error['error']);
        }

        $this->assertSame(2, Discussion::query()->count());
    }

    #[Test]
    public function search_discussions_filters_by_language(): void
    {
        $auth = $this->bearer($this->developerToken(2));

        $schema = array_column($this->rpcResult($this->modern('tools/list', [], $auth))['tools'], 'inputSchema', 'name')['search_discussions'];
        $this->assertArrayHasKey('language', $schema['properties']);

        $ids = fn (array $arguments) => array_column($this->toolOutput($this->callTool('search_discussions', $arguments, $auth))['data'], 'id');

        $this->assertSame(['2'], $ids(['language' => 'de']));
        $this->assertEqualsCanonicalizing(['1', '2'], $ids(['language' => 'en,de']));
        $this->assertEqualsCanonicalizing(['1', '2'], $ids([]));
    }

    #[Test]
    public function the_overview_lists_the_forums_languages(): void
    {
        $overview = json_decode($this->rpcResult($this->modern(
            'resources/read',
            ['uri' => 'flarum://forum'],
            $this->bearer($this->developerToken(2)),
            'flarum://forum'
        ))['contents'][0]['text'], true);

        $instructions = array_column($overview['extensions'], 'instructions', 'id')['fof-discussion-language'];

        $this->assertStringContainsString('en (English), de (German)', $instructions);
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
