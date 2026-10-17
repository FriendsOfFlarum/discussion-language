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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class DiscussionLanguageResourceTest extends TestCase
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
            ],
        ]);
    }

    protected function create(array $attributes, int $actor = 1): \Psr\Http\Message\ResponseInterface
    {
        return $this->send(
            $this->request('POST', '/api/discussion-languages', [
                'authenticatedAs' => $actor,
                'json'            => ['data' => ['type' => 'discussion-languages', 'attributes' => $attributes]],
            ])
        );
    }

    #[Test]
    public function admin_can_create_language(): void
    {
        $response = $this->create(['code' => 'de', 'country' => 'DE']);

        $this->assertEquals(201, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true)['data'];

        $this->assertEquals('de', $data['attributes']['code']);
        $this->assertEquals('DE', $data['attributes']['country']);
        $this->assertEquals('German', $data['attributes']['name']);
        $this->assertTrue(DiscussionLanguage::query()->where('code', 'de')->exists());
    }

    #[Test]
    public function non_admin_cannot_create_language(): void
    {
        $this->assertEquals(403, $this->create(['code' => 'de', 'country' => 'DE'], 2)->getStatusCode());
        $this->assertFalse(DiscussionLanguage::query()->where('code', 'de')->exists());
    }

    public static function invalidLanguages(): array
    {
        return [
            'no code'        => [['country' => 'DE']],
            'no country'     => [['code' => 'de']],
            'one-letter code' => [['code' => 'd', 'country' => 'DE']],
            'existing code'  => [['code' => 'en', 'country' => 'US']],
            'country name'   => [['code' => 'de', 'country' => 'Germany']],
        ];
    }

    #[Test]
    #[DataProvider('invalidLanguages')]
    public function invalid_language_is_rejected(array $attributes): void
    {
        $this->assertEquals(422, $this->create($attributes)->getStatusCode());
    }

    #[Test]
    public function admin_can_change_a_language(): void
    {
        $response = $this->send(
            $this->request('PATCH', '/api/discussion-languages/1', [
                'authenticatedAs' => 1,
                'json'            => ['data' => ['type' => 'discussion-languages', 'id' => '1', 'attributes' => ['country' => 'US']]],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('US', DiscussionLanguage::query()->find(1)->country);
    }

    #[Test]
    public function non_admin_cannot_change_a_language(): void
    {
        $response = $this->send(
            $this->request('PATCH', '/api/discussion-languages/1', [
                'authenticatedAs' => 2,
                'json'            => ['data' => ['type' => 'discussion-languages', 'id' => '1', 'attributes' => ['country' => 'US']]],
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('GB', DiscussionLanguage::query()->find(1)->country);
    }

    #[Test]
    public function admin_can_delete_a_language(): void
    {
        $response = $this->send($this->request('DELETE', '/api/discussion-languages/1', ['authenticatedAs' => 1]));

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull(DiscussionLanguage::query()->find(1));
    }

    #[Test]
    public function non_admin_cannot_delete_a_language(): void
    {
        $response = $this->send($this->request('DELETE', '/api/discussion-languages/1', ['authenticatedAs' => 2]));

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertNotNull(DiscussionLanguage::query()->find(1));
    }
}
