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
        $schema->table('tag_user', function (Blueprint $table) use ($schema) {
            if (!$schema->hasColumn('tag_user', 'dl_language_id')) {
                $table->integer('dl_language_id')->unsigned()->nullable();

                $table->foreign('dl_language_id')->references('id')->on('discussion_languages')->onDelete('set null');
            }

            $table->index(['user_id', 'dl_language_id']);
            $table->index(['dl_language_id']);
        });
    },

    'down' => function (Builder $schema) {
        $schema->table('tag_user', function (Blueprint $table) {
            $table->dropForeign(['dl_language_id']);

            $table->dropIndex(['dl_language_id']);
            $table->dropIndex(['user_id', 'dl_language_id']);

            $table->dropColumn('dl_language_id');
        });
    },
];
