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
use Flarum\Discussion\Discussion;
use Flarum\Locale\TranslatorInterface;
use FoF\DiscussionLanguage\DiscussionLanguage;
use Illuminate\Support\Arr;

class DiscussionResourceFields
{
    public function __construct(
        protected TranslatorInterface $translator
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('canChangeLanguage')
                ->get(fn (Discussion $discussion, Context $context) => $context->getActor()->can('changeLanguage', $discussion)),

            Schema\Relationship\ToOne::make('language')
                ->type('discussion-languages')
                ->includable()
                ->writable()
                ->required(fn (Context $context) => $context->creating() && ! $this->isPrivate($context))
                ->validationMessages([
                    'language.required' => $this->translator->trans('fof-discussion-language.api.discussion.validation_error'),
                ])
                ->set(function (Discussion $discussion, ?DiscussionLanguage $language, Context $context) {
                    // fof/byobu's private discussions have no language, and an
                    // update without one leaves the language as it is.
                    if ($this->isPrivate($context) || ! $language) {
                        return;
                    }

                    // Whoever may start a discussion chooses its language.
                    if ($discussion->exists) {
                        $context->getActor()->assertCan('changeLanguage', $discussion);
                    }

                    $discussion->language_id = $language->id;
                }),
        ];
    }

    /**
     * Whether the request makes the discussion one of fof/byobu's private ones.
     */
    protected function isPrivate(Context $context): bool
    {
        return Arr::has($context->body(), 'data.relationships.recipientUsers')
            || Arr::has($context->body(), 'data.relationships.recipientGroups');
    }
}
