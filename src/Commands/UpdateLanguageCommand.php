<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Commands;

use Flarum\User\User;

class UpdateLanguageCommand
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var int
     */
    public $id;

    /**
     * @var array
     */
    public $data;

    /**
     * @param User  $actor
     * @param int   $id
     * @param array $data
     */
    public function __construct(User $actor, int $id, array $data)
    {
        $this->actor = $actor;
        $this->id = $id;
        $this->data = $data;
    }
}
