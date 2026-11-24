<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        // Not through Eloquent: `flarum install` runs migrations before models have a connection.
        $schema->getConnection()->table('tags')->update(['localised_last_discussion' => '{}']);
    },
    'down' => function (Builder $schema) {
        // Do nothing
    },
];
