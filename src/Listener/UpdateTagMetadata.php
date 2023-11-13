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
    public function subscribe(Dispatcher $events)
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
    public function whenDiscussionIsStarted(Started $event)
    {
        $this->updateTags($event->discussion, 1);
    }

    /**
     * @param DiscussionWasTagged $event
     */
    public function whenDiscussionWasTagged(DiscussionWasTagged $event)
    {
        $oldTags = Tag::whereIn('id', Arr::pluck($event->oldTags, 'id'))->get();

        $this->updateTags($event->discussion, -1, $oldTags);
        $this->updateTags($event->discussion, 1);
    }

    /**
     * @param Deleted $event
     */
    public function whenDiscussionIsDeleted(Deleted $event)
    {
        $this->updateTags($event->discussion, -1);

        /** @phpstan-ignore-next-line */
        $event->discussion->tags()->detach();
    }

    /**
     * @param Hidden $event
     */
    public function whenDiscussionIsHidden(Hidden $event)
    {
        $this->updateTags($event->discussion, -1);
    }

    /**
     * @param Restored $event
     */
    public function whenDiscussionIsRestored(Restored $event)
    {
        $this->updateTags($event->discussion, 1);
    }

    /**
     * @param Posted $event
     */
    public function whenPostIsPosted(Posted $event)
    {
        $this->updateTags($event->post->discussion);
    }

    /**
     * @param PostDeleted $event
     */
    public function whenPostIsDeleted(PostDeleted $event)
    {
        $this->updateTags($event->post->discussion);
    }

    /**
     * @param PostHidden $event
     */
    public function whenPostIsHidden(PostHidden $event)
    {
        $this->updateTags($event->post->discussion, 0, null, $event->post);
    }

    /**
     * @param PostRestored $event
     */
    public function whenPostIsRestored(PostRestored $event)
    {
        $this->updateTags($event->post->discussion, 0, null, $event->post);
    }

    /**
     * @param \Flarum\Discussion\Discussion $discussion
     * @param int                           $delta
     * @param Collection<Tag>|null          $tags
     * @param \Flarum\Post\Post             $post:      This is only used when a post has been hidden
     */
    protected function updateTags(Discussion $discussion, $delta = 0, $tags = null, $post = null)
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

        foreach ($tags as $tag) {
            // We do not count private discussions or hidden discussions in tags
            if (!$discussion->is_private) {
                $tag->discussion_count += $delta;
            }

            $raw_json_string = $tag->localised_last_discussion;
            if (strlen($raw_json_string) < 2) {
                $raw_json_string = '{}';
            }

            $localisedLastPost = json_decode($raw_json_string, true);
            $lang = $discussion->language_id;

            // If this is a new / restored discussion, it isn't private, it isn't null,
            // and it's more recent than what we have now, set it as last posted discussion.
            if ($delta >= 0 && !$discussion->is_private && $discussion->hidden_at == null && (!Arr::exists($localisedLastPost, $lang) || ($discussion->last_posted_at->timestamp >= $localisedLastPost[$lang]['at']))) {
                $this->setLocalisedLastDiscussion($tag, $discussion);
            } elseif ($discussion->id == $tag->last_posted_discussion_id) {
                // This is to persist refreshLastPost above. It is here instead of there so that
                // if it's not necessary, we save a DB query.
                if ($post) {
                    $discussion->save();
                }

                // This discussion is currently the last posted discussion, but since it didn't qualify for the above check,
                // it should not be the last posted discussion. Therefore, we should refresh the last posted discussion..
                if ($lastPostedDiscussion = $tag->discussions()->where('is_private', false)->where('language_id', $lang)->whereNull('hidden_at')->latest('last_posted_at')->first()) {
                    $this->setLocalisedLastDiscussion($tag, $lastPostedDiscussion);
                } else {
                    // No discussions qualify. Remove the last discussion for this language.
                    $this->removeLocalisedLastDiscussion($tag, $lang);
                }
            }

            $tag->save();
        }
    }

    private function setLocalisedLastDiscussion(Tag $tag, Discussion $discussion)
    {
        $localisedLastPost = json_decode($tag->localised_last_discussion, true);
        $lang = $discussion->language_id;

        $localisedLastPost[$lang] = [
            'id'      => $discussion->id,
            'at'      => $discussion->last_posted_at->timestamp,
            'user_id' => $discussion->last_posted_user_id,
        ];

        $tag->localised_last_discussion = json_encode($localisedLastPost);
    }

    private function removeLocalisedLastDiscussion(Tag $tag, $lang)
    {
        $localisedLastPost = json_decode($tag->localised_last_discussion, true);

        if (Arr::exists($localisedLastPost, $lang)) {
            unset($localisedLastPost[$lang]);
        }

        $tag->localised_last_discussion = json_encode($localisedLastPost);
    }
}
