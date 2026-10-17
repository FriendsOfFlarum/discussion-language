<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Access;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class DiscussionPolicy extends AbstractPolicy
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function changeLanguage(User $actor, Discussion $discussion): ?string
    {
        // Forced: in a restricted tag, flarum/tags denies any ability without a
        // permission of its own there, and none can be granted for this one.
        // Both checks below already respect the tag's own permissions.
        if ($this->authorMayChange($actor, $discussion) || $actor->can('changeLanguageModerate', $discussion)) {
            return $this->forceAllow();
        }

        return null;
    }

    /**
     * An author may change the language for as long as the setting allows.
     */
    protected function authorMayChange(User $actor, Discussion $discussion): bool
    {
        if ($actor->isGuest() || (int) $discussion->user_id !== (int) $actor->id || !$actor->can('reply', $discussion)) {
            return false;
        }

        $allowEditLanguage = $this->settings->get('fof-discussion-language.allow_language_change');

        return $allowEditLanguage === '-1'
            || ($allowEditLanguage === 'reply' && $discussion->participant_count <= 1)
            || (is_numeric($allowEditLanguage) && $discussion->created_at->diffInMinutes(new Carbon(), true) < $allowEditLanguage);
    }
}
