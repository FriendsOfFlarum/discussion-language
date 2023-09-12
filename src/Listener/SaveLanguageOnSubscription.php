<?php

namespace FoF\DiscussionLanguage\Listener;

use FoF\FollowTags\Event\SubscriptionChanging;
use FoF\DiscussionLanguage\DiscussionLanguage;
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
        $languageId = DiscussionLanguage::where('code', $language)->pluck('id')->first();

        $event->state->dl_language_id = $languageId;
    }
}
