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

use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Deleted;
use Flarum\Discussion\Event\Hidden;
use Flarum\Discussion\Event\Restored;
use Flarum\Discussion\Event\Started;
use Flarum\Post\Event\Deleted as PostDeleted;
use Flarum\Post\Event\Hidden as PostHidden;
use Flarum\Post\Event\Posted;
use Flarum\Post\Event\Restored as PostRestored;
use Flarum\Tags\Event\DiscussionWasTagged;
use Flarum\Tags\Tag;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class UpdateTagMetadata
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(Started::class, [$this, 'whenDiscussionIsStarted']);
        $events->listen(DiscussionWasTagged::class, [$this, 'whenDiscussionWasTagged']);
        $events->listen(Deleted::class, [$this, 'whenDiscussionIsDeleted']);
        $events->listen(Hidden::class, [$this, 'whenDiscussionIsHidden']);
        $events->listen(Restored::class, [$this, 'whenDiscussionIsRestored']);

        $events->listen(Posted::class, [$this, 'whenPostIsPosted']);
        $events->listen(PostDeleted::class, [$this, 'whenPostIsDeleted']);
        $events->listen(PostHidden::class, [$this, 'whenPostIsHidden']);
        $events->listen(PostRestored::class, [$this, 'whenPostIsRestored']);
    }

    /**
     * @param Started $event
     */
    public function whenDiscussionIsStarted(Started $event): void
    {
        $this->updateTags($event->discussion, 1);
    }

    /**
     * @param DiscussionWasTagged $event
     */
    public function whenDiscussionWasTagged(DiscussionWasTagged $event): void
    {
        $oldTags = Tag::whereIn('id', Arr::pluck($event->oldTags, 'id'))->get();

        $this->updateTags($event->discussion, -1, $oldTags);
        $this->updateTags($event->discussion, 1);
    }

    /**
     * @param Deleted $event
     */
    public function whenDiscussionIsDeleted(Deleted $event): void
    {
        // flarum/tags loaded its tags before they were deleted, and detaches them.
        $this->updateTags($event->discussion, -1);
    }

    /**
     * @param Hidden $event
     */
    public function whenDiscussionIsHidden(Hidden $event): void
    {
        $this->updateTags($event->discussion, -1);
    }

    /**
     * @param Restored $event
     */
    public function whenDiscussionIsRestored(Restored $event): void
    {
        $this->updateTags($event->discussion, 1);
    }

    /**
     * @param Posted $event
     */
    public function whenPostIsPosted(Posted $event): void
    {
        $this->updateTags($event->post->discussion);
    }

    /**
     * @param PostDeleted $event
     */
    public function whenPostIsDeleted(PostDeleted $event): void
    {
        $this->updateTags($event->post->discussion);
    }

    /**
     * @param PostHidden $event
     */
    public function whenPostIsHidden(PostHidden $event): void
    {
        $this->updateTags($event->post->discussion, 0, null, $event->post);
    }

    /**
     * @param PostRestored $event
     */
    public function whenPostIsRestored(PostRestored $event): void
    {
        $this->updateTags($event->post->discussion, 0, null, $event->post);
    }

    /**
     * @param \Flarum\Discussion\Discussion $discussion
     * @param int                           $delta
     * @param Collection<Tag>|null          $tags
     * @param \Flarum\Post\Post             $post:      This is only used when a post has been hidden
     */
    protected function updateTags(Discussion $discussion, int $delta = 0, ?Collection $tags = null, ?\Flarum\Post\Post $post = null): void
    {
        if (!$tags) {
            /** @phpstan-ignore-next-line */
            $tags = $discussion->tags;
        }

        // If we've just hidden or restored a post, the discussion's last posted at metadata might not have updated yet.
        // Therefore, we must refresh the last post, even though that might be repeated in the future.
        if ($post) {
            $discussion->refreshLastPost();
        }

        // flarum/tags keeps each tag's discussion count: $delta only tells a
        // discussion that is new or back from one that is gone.
        foreach ($tags as $tag) {
            $localisedLastPost = $this->localisedLastDiscussions($tag);
            $lang = $this->languageKey($discussion);

            // If this is a new / restored discussion, it isn't private, it isn't null,
            // and it's more recent than what we have now, set it as last posted discussion.
            // One deleted with its only post is gone by the time its post's event is heard.
            if ($delta >= 0 && $discussion->exists && !$discussion->is_private && $discussion->hidden_at == null && (!Arr::exists($localisedLastPost, $lang) || ($discussion->last_posted_at->timestamp >= $localisedLastPost[$lang]['at']))) {
                $this->setLocalisedLastDiscussion($tag, $discussion);
            } elseif ($discussion->id == ($localisedLastPost[$lang]['id'] ?? null)) {
                // This is to persist refreshLastPost above. It is here instead of there so that
                // if it's not necessary, we save a DB query.
                if ($post) {
                    $discussion->save();
                }

                // This discussion is currently the last posted discussion, but since it didn't qualify for the above check,
                // it should not be the last posted discussion. Therefore, we should refresh the last posted discussion..
                if ($lastPostedDiscussion = $tag->discussions()->where('is_private', false)->where('language_id', $discussion->language_id)->whereNull('hidden_at')->latest('last_posted_at')->first()) {
                    $this->setLocalisedLastDiscussion($tag, $lastPostedDiscussion);
                } else {
                    // No discussions qualify. Remove the last discussion for this language.
                    $this->removeLocalisedLastDiscussion($tag, $lang);
                }
            }

            $tag->save();
        }
    }

    /**
     * @return array<string, array{id: int, at: int, user_id: int|null}>
     */
    protected function localisedLastDiscussions(Tag $tag): array
    {
        // Null for tags created while the extension was disabled.
        $localisedLastPost = json_decode($tag->localised_last_discussion ?? '{}', true);

        return is_array($localisedLastPost) ? $localisedLastPost : [];
    }

    /**
     * The key a discussion's language has in a tag's localised last discussions:
     * its ID, or an empty string for a discussion without a language, as 1.x
     * stored it.
     */
    protected function languageKey(Discussion $discussion): string
    {
        return (string) $discussion->language_id;
    }

    protected function setLocalisedLastDiscussion(Tag $tag, Discussion $discussion): void
    {
        $localisedLastPost = $this->localisedLastDiscussions($tag);
        $lang = $this->languageKey($discussion);

        $localisedLastPost[$lang] = [
            'id'      => $discussion->id,
            'at'      => $discussion->last_posted_at->timestamp,
            'user_id' => $discussion->last_posted_user_id,
        ];

        $tag->localised_last_discussion = json_encode($localisedLastPost);
    }

    protected function removeLocalisedLastDiscussion(Tag $tag, string $lang): void
    {
        $localisedLastPost = $this->localisedLastDiscussions($tag);

        unset($localisedLastPost[$lang]);

        // An object, not a list, when no language is left.
        $tag->localised_last_discussion = json_encode((object) $localisedLastPost);
    }
}
