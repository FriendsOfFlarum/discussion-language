<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        /**
         * @var SettingsRepositoryInterface
         */
        $settings = resolve('flarum.settings');

        $settings->set('fof-discussion-language.showFlags', true);
    },
    'down' => function (Builder $schema) {
        /**
         * @var SettingsRepositoryInterface
         */
        $settings = resolve('flarum.settings');

        $settings->delete('fof-discussion-language.showFlags');
    },
];
