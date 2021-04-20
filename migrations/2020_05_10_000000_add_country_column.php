<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        if ($schema->getConnection()->table('migrations')->where('migration', '2020_04_10_000000_add_country_column')->exists()) {
            return;
        }

        $schema->table('discussion_languages', function (Blueprint $table) {
            $table->string('country', 2)->nullable();
        });
    },
    'down' => function (Builder $schema) {
        if ($schema->getConnection()->table('migrations')->where('migration', '2020_04_10_000000_add_country_column')->exists()) {
            return;
        }

        $schema->table('discussion_languages', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    },
];
