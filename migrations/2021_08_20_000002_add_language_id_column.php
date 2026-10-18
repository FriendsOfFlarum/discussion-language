<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Flarum\Database\Migration;

// Nullable: SQLite can't add a NOT NULL column without a default to a table
// that has rows, and PostgreSQL and SQLite reject a tag created without it.
return Migration::addColumns('tags', [
    'localised_last_discussion' => ['text', 'nullable' => true],
]);
