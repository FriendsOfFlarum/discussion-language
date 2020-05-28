<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Listeners;

use Flarum\Discussion\Event\Saving;
use Flarum\User\AssertPermissionTrait;
use FoF\DiscussionLanguage\Validators\DiscussionValidator;
use Illuminate\Support\Arr;

class AddDiscussionLanguage
{
    use AssertPermissionTrait;

    /**
     * @var DiscussionValidator
     */
    protected $validator;

    public function __construct(DiscussionValidator $validator)
    {
        $this->validator = $validator;
    }

    public function handle(Saving $event)
    {
        // Check to see if we should skip adding the language if this is a private discussion created by fof/byobu
        if (isset($event->data['relationships']['recipientUsers']) || isset($event->data['relationships']['recipientGroups'])) {
            return;
        }

        $languageId = Arr::get($event->data, 'relationships.language.data.id');

        if (!$event->discussion->exists || $languageId) {
            $this->assertCan($event->actor, 'changeLanguage', $event->discussion);

            $this->validator->assertValid(['language' => $languageId]);

            $event->discussion->language_id = $languageId;
        }
    }
}
