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

use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\DiscussionLanguage\DiscussionLanguage;
use PHPUnit\Framework\Attributes\Test;

class ForumLanguagesTest extends TestCase
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
                // Not in ISO 639-1: named from the ISO 639-2 list the extension ships.
                ['id' => 4, 'code' => 'aar', 'country' => null],
                ['id' => 5, 'code' => 'zzz', 'country' => null],
            ],
        ]);
    }

    /**
     * @return array<int, array{id: string, attributes: array<string, mixed>}> The forum's languages, in order.
     */
    protected function forumLanguages(): array
    {
        $response = $this->send($this->request('GET', '/api', ['authenticatedAs' => 2]));

        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode($response->getBody()->getContents(), true);

        $order = array_column($payload['data']['relationships']['discussionLanguages']['data'], 'id');
        $included = array_column(
            array_filter($payload['included'] ?? [], fn ($resource) => $resource['type'] === 'discussion-languages'),
            null,
            'id'
        );

        return array_map(fn (string $id) => $included[$id], $order);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function attributesByCode(): array
    {
        return array_column(array_column($this->forumLanguages(), 'attributes'), null, 'code');
    }

    #[Test]
    public function languages_are_named_in_english(): void
    {
        $languages = $this->attributesByCode();

        $this->assertEquals('English', $languages['en']['name']);
        $this->assertEquals('German', $languages['de']['name']);
        $this->assertEquals('French', $languages['fr']['name']);
        $this->assertEquals('Afar', $languages['aar']['name']);
        $this->assertNull($languages['zzz']['name']);
    }

    #[Test]
    public function languages_can_be_named_in_themselves(): void
    {
        $this->setting('fof-discussion-language.native', true);

        $languages = $this->attributesByCode();

        $this->assertEquals('Deutsch', $languages['de']['name']);
        $this->assertEquals('Français', $languages['fr']['name']);
    }

    #[Test]
    public function any_language_comes_first_by_default(): void
    {
        $languages = $this->forumLanguages();

        $this->assertEquals('any', $languages[0]['id']);
        $this->assertEquals('any', $languages[0]['attributes']['code']);
        $this->assertNotEmpty($languages[0]['attributes']['name']);
        $this->assertCount(6, $languages);
    }

    #[Test]
    public function any_language_is_left_out_when_not_offered(): void
    {
        $this->setting('fof-discussion-language.showAnyLangOpt', false);

        $this->assertNotContains('any', array_column($this->forumLanguages(), 'id'));
    }

    #[Test]
    public function languages_have_their_country_flag(): void
    {
        $languages = $this->attributesByCode();

        $this->assertEquals('🇩🇪', $languages['de']['emoji']);
        $this->assertNull($languages['zzz']['emoji']);
    }

    #[Test]
    public function languages_have_no_flag_when_flags_are_off(): void
    {
        $this->setting('fof-discussion-language.showFlags', false);

        $this->assertNull($this->attributesByCode()['de']['emoji']);
    }
}
