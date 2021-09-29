<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Flarum\Tags\Tag;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        Tag::all()->each(function (Tag $tag) {
            $tag->localised_last_discussion = '{}';
            $tag->save();
        });
    },
    'down' => function (Builder $schema) {
        // Do nothing
    },
];
