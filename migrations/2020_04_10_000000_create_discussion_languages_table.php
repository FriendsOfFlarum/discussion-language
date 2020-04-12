<?php

use Flarum\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return Migration::createTable('discussion_languages', function (Blueprint $table) {
    $table->increments('id');
    $table->string('code')->unique();
});
