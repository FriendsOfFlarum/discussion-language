<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\tests\integration\api;

use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;

class CreateLanguageTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags');
        $this->extension('fof-discussion-language');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
            ],
        ]);
    }

    /**
     * @test
     */
    public function non_admin_cannot_create_language()
    {
        $response = $this->send(
            $this->request('POST', '/api/fof/discussion-language', [
                'json' => [
                    'data' => [
                        'attributes' => [
                            'country' => 'Test Country',
                            'code'    => 'test',
                        ],
                    ],
                ],
                'authenticatedAs' => 2,
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function admin_can_create_language()
    {
        $response = $this->send(
            $this->request('POST', '/api/fof/discussion-language', [
                'json' => [
                    'data' => [
                        'attributes' => [
                            'country' => 'Test Country',
                            'code'    => 'test',
                        ],
                    ],
                ],
                'authenticatedAs' => 1,
            ])
        );

        $this->assertEquals(201, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('Test Country', $data['data']['attributes']['country']);
        $this->assertEquals('test', $data['data']['attributes']['code']);
    }
}
