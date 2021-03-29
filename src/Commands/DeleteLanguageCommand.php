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

class DeleteLanguageCommand
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var string
     */
    public $id;

    /**
     * @param User   $actor
     * @param string $id
     */
    public function __construct(User $actor, $id)
    {
        $this->actor = $actor;
        $this->id = $id;
    }
}
