<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Listener;

use FoF\DiscussionLanguage\DiscussionLanguage;
use FoF\FollowTags\Event\SubscriptionChanging;
use Illuminate\Support\Arr;

/**
 * This class is only called when `fof/follow-tags` is enabled.
 */
class SaveLanguageOnSubscription
{
    public function handle(SubscriptionChanging $event): void
    {
        $language = Arr::get($event->request->getParsedBody(), 'data.language');

        if (!$language) {
            return;
        }

        // Fetch the language ID using the provided language string
        $languageId = DiscussionLanguage::query()
                                        ->where('code', $language)
                                        ->value('id');

        $event->state->dl_language_id = $languageId;
    }
}
