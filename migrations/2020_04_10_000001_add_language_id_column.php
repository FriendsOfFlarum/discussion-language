<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $schema->table('discussions', function (Blueprint $table) {
            $table->unsignedInteger('language_id')->nullable();

            $table->foreign('language_id')->references('id')->on('discussion_languages')->onDelete('set null');
        });
    },
    'down' => function (Builder $schema) {
        $schema->table('discussions', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropColumn('language_id');
        });
    }
];
