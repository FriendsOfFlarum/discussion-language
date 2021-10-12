<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Listener;

use Flarum\Tags\Event\Creating;

class TagCreating
{
    public function handle(Creating $event)
    {
        $event->tag->localised_last_discussion = '{}';

        return $event;
    }
}
