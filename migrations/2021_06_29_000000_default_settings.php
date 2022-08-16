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
        // do nothing, default settings are now handled by the extender
    },
    'down' => function (Builder $schema) {
        // do nothing, default settings are now handled by the extender
    },
];
