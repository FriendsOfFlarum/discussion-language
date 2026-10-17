<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Tags\Tag;
use FoF\DiscussionLanguage\Languages;

/**
 * The language a fof/follow-tags subscriber is notified of new discussions in.
 */
class TagSubscriptionFields
{
    public function __construct(
        protected Languages $languages
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Str::make('subscriptionLanguage')
                ->nullable()
                ->get(function (Tag $tag, Context $context) {
                    $actor = $context->getActor();

                    if ($actor->isGuest()) {
                        return null;
                    }

                    // The tag listing loads each tag's state for the actor.
                    $languageId = $tag->stateFor($actor)->dl_language_id;

                    return $languageId ? ($this->languages->codes()[$languageId] ?? null) : null;
                })
                // fof/follow-tags lets it be saved with a subscription by whoever subscribes.
                ->writable(fn (Tag $tag, Context $context) => $context->updating() && ! $context->getActor()->isGuest())
                ->set(function (Tag $tag, ?string $code, Context $context) {
                    // As in 1.x, no language leaves the one chosen before.
                    if (! $code) {
                        return;
                    }

                    // fof/follow-tags saves its own copy of the state, so this saves the row too.
                    $state = $tag->stateFor($context->getActor());

                    // Any language, or one the forum no longer has, is every language.
                    $languageId = array_search($code, $this->languages->codes(), true);
                    $state->dl_language_id = $languageId === false ? null : $languageId;

                    $state->save();

                    // A setter after this one then has the saved row, not a new one.
                    $tag->setRelation('state', $state);
                }),
        ];
    }
}
