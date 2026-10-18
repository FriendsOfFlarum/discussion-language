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

// Installs from before 2.0 added the column NOT NULL, which PostgreSQL and
// SQLite enforce whenever a tag is created without it, as while the extension
// is disabled.
return [
    'up' => function (Builder $schema) {
        $schema->table('tags', function (Blueprint $table) {
            $table->text('localised_last_discussion')->nullable()->change();
        });
    },
    'down' => function (Builder $schema) {
        $schema->getConnection()->table('tags')->whereNull('localised_last_discussion')->update(['localised_last_discussion' => '{}']);

        $schema->table('tags', function (Blueprint $table) {
            $table->text('localised_last_discussion')->nullable(false)->change();
        });
    },
];
