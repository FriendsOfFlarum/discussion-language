<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage;

use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Saving;
use Flarum\Event\ConfigureDiscussionGambits;
use Flarum\Extend;
use Illuminate\Contracts\Events\Dispatcher;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    (new Extend\Routes('api'))
        ->post('/fof/discussion-language', 'fof.discussion-language.create', Api\Controllers\CreateLanguageController::class)
        ->patch('/fof/discussion-language/{id}', 'fof.discussion-language.update', Api\Controllers\UpdateLanguageController::class)
        ->delete('/fof/discussion-language/{id}', 'fof.discussion-language.delete', Api\Controllers\DeleteLanguageController::class),
    
    (new Extend\Middleware('forum'))
        ->add(Middleware\AddLanguageFilter::class),

    (new Extend\Model(Discussion::class))
        ->hasOne('language', DiscussionLanguage::class, 'id', 'language_id'),

    function (Dispatcher $events) {
        $events->subscribe(Listeners\AddRelationships::class);
        $events->listen(Saving::class, Listeners\AddDiscussionLanguage::class);

        $events->listen(ConfigureDiscussionGambits::class, function (ConfigureDiscussionGambits $event) {
            $event->gambits->add(Gambit\LanguageGambit::class);
        });

        $events->subscribe(Access\DiscussionPolicy::class);
    },
];
