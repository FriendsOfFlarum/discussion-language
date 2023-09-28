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
    protected $model = Discussion::class;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function changeLanguage(User $actor, Discussion $discussion)
    {
        if ($discussion->user_id == $actor->id && $actor->can('reply', $discussion)) {
            $allowEditLanguage = $this->settings->get('fof-discussion-language.allow_language_change');

            if ($allowEditLanguage === '-1'
                || ($allowEditLanguage === 'reply' && $discussion->participant_count <= 1)
                || (is_numeric($allowEditLanguage) && $discussion->created_at->diffInMinutes(new Carbon()) < $allowEditLanguage)
            ) {
                return $this->allow();
            }
        } elseif ($actor->can('changeLanguageModerate', $discussion)) {
            return $this->allow();
        }
    }
}
