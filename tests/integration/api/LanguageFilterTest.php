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
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\DiscussionLanguage\DiscussionLanguage;
use PHPUnit\Framework\Attributes\Test;

/**
 * The `language` filter. In 2.0 a `language:` gambit in the search is the
 * frontend's to turn into this filter, so the API only takes the filter.
 */
class LanguageFilterTest extends TestCase
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
            ],
            DiscussionLanguage::class => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
                ['id' => 3, 'code' => 'fr', 'country' => 'FR'],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'English discussion', 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1, 'language_id' => 1],
                ['id' => 2, 'title' => 'German discussion',  'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 1, 'first_post_id' => 2, 'comment_count' => 1, 'language_id' => 2],
                ['id' => 3, 'title' => 'French discussion',  'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 1, 'first_post_id' => 3, 'comment_count' => 1, 'language_id' => 3],
                ['id' => 4, 'title' => 'No language discussion', 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 1, 'first_post_id' => 4, 'comment_count' => 1, 'language_id' => null],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'user_id' => 1],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'user_id' => 1],
                ['id' => 4, 'discussion_id' => 4, 'number' => 1, 'user_id' => 1],
            ],
        ]);
    }

    protected function titles(array $filter): array
    {
        $response = $this->send($this->request('GET', '/api/discussions')->withQueryParams(['filter' => $filter]));

        $this->assertEquals(200, $response->getStatusCode());

        $titles = array_column(array_column(json_decode($response->getBody()->getContents(), true)['data'], 'attributes'), 'title');
        sort($titles);

        return $titles;
    }

    #[Test]
    public function filter_returns_only_matching_language(): void
    {
        $this->assertEquals(['English discussion'], $this->titles(['language' => 'en']));
        $this->assertEquals(['German discussion'], $this->titles(['language' => 'de']));
        $this->assertEquals(['French discussion'], $this->titles(['language' => 'fr']));
    }

    #[Test]
    public function filter_with_unknown_language_returns_no_discussions(): void
    {
        $this->assertEquals([], $this->titles(['language' => 'xx']));
    }

    #[Test]
    public function filter_negation_excludes_matching_language(): void
    {
        // Discussions without a language are kept, as they aren't in it.
        $this->assertEquals(
            ['French discussion', 'German discussion', 'No language discussion'],
            $this->titles(['-language' => 'en'])
        );
    }

    #[Test]
    public function filter_comma_separated_codes_match_multiple_languages(): void
    {
        $this->assertEquals(['English discussion', 'German discussion'], $this->titles(['language' => 'en,de']));
    }

    #[Test]
    public function filter_list_of_codes_matches_multiple_languages(): void
    {
        $this->assertEquals(['English discussion', 'German discussion'], $this->titles(['language' => ['en', 'de']]));
    }

    #[Test]
    public function filter_any_returns_every_discussion(): void
    {
        $this->assertCount(4, $this->titles(['language' => 'any']));
    }

    #[Test]
    public function filter_any_alongside_a_language_returns_that_language(): void
    {
        $this->assertEquals(['English discussion'], $this->titles(['language' => 'any,en']));
    }

    #[Test]
    public function filter_with_a_nested_value_is_rejected(): void
    {
        $response = $this->send($this->request('GET', '/api/discussions')->withQueryParams(['filter' => ['language' => [['en']]]]));

        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
        $this->assertLessThan(500, $response->getStatusCode());
    }
}
