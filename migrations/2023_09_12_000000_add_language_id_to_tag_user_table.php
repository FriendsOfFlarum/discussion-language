<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $schema->table('tag_user', function (Blueprint $table) {
            $table->integer('dl_language_id')->unsigned()->nullable();

            $table->foreign('dl_language_id')->references('id')->on('discussion_languages')->onDelete('set null');

            $table->index(['user_id', 'subscription', 'dl_language_id']);
            $table->index(['dl_language_id']);
        });
    },

    'down' => function (Builder $schema) {
        $schema->table('tag_user', function (Blueprint $table) {
            $table->dropForeign(['dl_language_id']);
            
            $table->dropIndex(['dl_language_id']);
            $table->dropIndex(['user_id', 'subscription', 'dl_language_id']);
            
            $table->dropColumn('dl_language_id');
        });
    },
];
