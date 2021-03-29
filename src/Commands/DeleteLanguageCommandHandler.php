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

use FoF\DiscussionLanguage\DiscussionLanguage;

class DeleteLanguageCommandHandler
{
    public function handle(DeleteLanguageCommand $command)
    {
        $command->actor->assertAdmin();

        $language = DiscussionLanguage::findOrFail($command->id);

        $language->delete();
    }
}
