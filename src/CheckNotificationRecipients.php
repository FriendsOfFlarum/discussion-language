<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage;

use Flarum\Discussion\Discussion;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Tags\Tag;
use Flarum\User\User;

class CheckNotificationRecipients
{
    /**
     * @param BlueprintInterface $blueprint
     * @param User[]             $users
     *
     * @return User[]
     */
    public function __invoke(BlueprintInterface $blueprint, array $users): array
    {
        if (!$this->isFollowTagsBlueprint($blueprint)) {
            return $users;
        }

        $discussion = $blueprint->getSubject();

        if (!$discussion || !$discussion instanceof Discussion || !$discussion->language_id) {
            return $users;
        }

        /**
         * @var Tag[] $tags
         *
         * @phpstan-ignore-next-line
         */
        $tags = $discussion->tags()->get();

        // Build a map of users and an array of their respective dl_language_id values
        $userLanguageMap = [];
        foreach ($users as $user) {
            $userLanguageMap[$user->id] = $userLanguageMap[$user->id] ?? [];
            foreach ($tags as $tag) {
                $stateLanguageId = $tag->stateFor($user)->dl_language_id;
                if (!in_array($stateLanguageId, $userLanguageMap[$user->id], true)) {
                    $userLanguageMap[$user->id][] = $stateLanguageId;
                }
            }
        }

        // Filter users based on the map
        return array_filter($users, function ($user) use ($userLanguageMap, $discussion) {
            // User's language preferences must contain the discussion's language_id or be null
            return in_array(null, $userLanguageMap[$user->id], true) || in_array($discussion->language_id, $userLanguageMap[$user->id], true);
        });
    }

    /**
     * Determines if the provided blueprint belongs to `fof/follow-tags`.
     *
     * Checks the class name of the blueprint to see if it starts with `FoF\FollowTags\Notification`.
     *
     * @param BlueprintInterface $blueprint The blueprint to check.
     *
     * @return bool True if the blueprint is a FollowTags notification, false otherwise.
     */
    protected function isFollowTagsBlueprint(BlueprintInterface $blueprint): bool
    {
        return strpos(get_class($blueprint), 'FoF\FollowTags\Notification') === 0;
    }
}
