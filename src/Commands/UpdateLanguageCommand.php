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
    public function __construct(public User $actor, public int $id, public array $data)
    {
    }
}
