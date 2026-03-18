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

class DiscussionLanguageSerializerTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-discussion-language');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
            ],
            'discussion_languages' => [
                ['id' => 1, 'code' => 'en', 'country' => 'GB'],
                ['id' => 2, 'code' => 'de', 'country' => 'DE'],
                ['id' => 3, 'code' => 'fr', 'country' => 'FR'],
            ],
        ]);
    }

    protected function getForumLanguages(int $authenticatedAs = 1): array
    {
        $response = $this->send(
            $this->request('GET', '/api', [
                'authenticatedAs' => $authenticatedAs,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $payload = json_decode($response->getBody()->getContents(), true);

        $languages = [];

        foreach ($payload['included'] ?? [] as $resource) {
            if ($resource['type'] === 'discussion-languages') {
                $languages[$resource['attributes']['code']] = $resource['attributes'];
            }
        }

        return $languages;
    }

    /**
     * @test
     */
    public function forum_api_includes_language_names(): void
    {
        $languages = $this->getForumLanguages();

        $this->assertNotEmpty($languages, 'Forum API should include discussion-languages');

        foreach ($languages as $code => $attrs) {
            $this->assertArrayHasKey('name', $attrs, "Language '{$code}' should have a name attribute");
            $this->assertNotNull($attrs['name'], "Language '{$code}' name should not be null");
        }
    }

    /**
     * @test
     */
    public function language_name_resolves_for_standard_codes(): void
    {
        $languages = $this->getForumLanguages();

        $this->assertEquals('English', $languages['en']['name']);
        $this->assertEquals('German', $languages['de']['name']);
        $this->assertEquals('French', $languages['fr']['name']);
    }

    /**
     * @test
     */
    public function language_name_resolves_any_code(): void
    {
        $this->prepareDatabase([
            'discussion_languages' => [
                ['id' => 10, 'code' => 'any', 'country' => null],
            ],
        ]);

        $languages = $this->getForumLanguages();

        $this->assertArrayHasKey('any', $languages);
        $this->assertNotNull($languages['any']['name']);
        // The 'any' code returns a translated string, just verify it's not empty
        $this->assertNotEmpty($languages['any']['name']);
    }

    /**
     * @test
     */
    public function language_name_returns_null_for_unknown_code(): void
    {
        $this->prepareDatabase([
            'discussion_languages' => [
                ['id' => 20, 'code' => 'zzz', 'country' => null],
            ],
        ]);

        $languages = $this->getForumLanguages();

        $this->assertArrayHasKey('zzz', $languages);
        $this->assertNull($languages['zzz']['name']);
    }

    /**
     * @test
     */
    public function language_name_resolves_csv_fallback_codes(): void
    {
        // 'vo' (Volapük) is in the CSV but the ISO 639 library may not resolve it
        // 'aar' is a 639-2/639-3 code for Afar — only in CSV
        $this->prepareDatabase([
            'discussion_languages' => [
                ['id' => 30, 'code' => 'aar', 'country' => null],
            ],
        ]);

        $languages = $this->getForumLanguages();

        $this->assertArrayHasKey('aar', $languages);
        // 'aar' should resolve to 'Afar' from the CSV
        $this->assertEquals('Afar', $languages['aar']['name']);
    }
}
