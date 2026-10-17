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
use Flarum\Tags\TagState;
use Flarum\User\User;
use FoF\FollowTags\Notifications\NewDiscussionBlueprint;
use FoF\FollowTags\Notifications\NewDiscussionTagBlueprint;
use FoF\FollowTags\Notifications\NewPostBlueprint;

/**
 * Leaves out of a fof/follow-tags notification about a discussion those who
 * follow it, by the tags it has, only in other languages.
 */
class CheckNotificationRecipients
{
    /**
     * @param User[] $users
     *
     * @return User[]
     */
    public function __invoke(BlueprintInterface $blueprint, array $users): array
    {
        // The subscriptions fof/follow-tags chose the recipients by. A tag they
        // don't follow says nothing about the languages they want.
        $subscriptions = match (true) {
            $blueprint instanceof NewDiscussionBlueprint, $blueprint instanceof NewDiscussionTagBlueprint => ['follow', 'lurk'],
            $blueprint instanceof NewPostBlueprint => ['lurk'],
            default                                => null,
        };

        if (!$subscriptions) {
            return $users;
        }

        $discussion = $blueprint->getSubject();

        if (!$discussion instanceof Discussion || !$discussion->language_id || !$users) {
            return $users;
        }

        $tagIds = $discussion->getConnection()
            ->table('discussion_tag')
            ->where('discussion_id', $discussion->id)
            ->pluck('tag_id')
            ->all();

        if (!$tagIds) {
            return $users;
        }

        $userIds = array_map(fn (User $user) => $user->id, $users);

        // The languages each user follows the discussion's tags in, from one query.
        $languages = TagState::query()
            ->whereIn('tag_id', $tagIds)
            ->whereIn('user_id', $userIds)
            ->whereIn('subscription', $subscriptions)
            ->get(['user_id', 'dl_language_id'])
            ->groupBy('user_id');

        return array_values(array_filter($users, function (User $user) use ($languages, $discussion) {
            $states = $languages->get($user->id);

            // They follow none of its tags, so there are no languages to judge by.
            if (!$states) {
                return true;
            }

            // One tag followed in every language, or in the discussion's, is enough.
            return $states->contains(
                fn (TagState $state) => $state->dl_language_id === null || (int) $state->dl_language_id === (int) $discussion->language_id
            );
        }));
    }
}
