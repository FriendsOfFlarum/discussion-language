<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Commands;

use Flarum\User\AssertPermissionTrait;
use FoF\DiscussionLanguage\DiscussionLanguage;

class DeleteLanguageCommandHandler
{
    use AssertPermissionTrait;

    public function handle(DeleteLanguageCommand $command)
    {
        $actor = $command->actor;

        $this->assertAdmin($actor);

        $language = DiscussionLanguage::findOrFail($command->id);

        $language->delete();
    }
}
