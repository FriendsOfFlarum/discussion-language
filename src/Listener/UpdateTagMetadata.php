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

use Flarum\Approval\Event\PostWasApproved;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Deleted;
use Flarum\Discussion\Event\Hidden;
use Flarum\Discussion\Event\Restored;
use Flarum\Discussion\Event\Started;
use Flarum\Gdpr\Events\Erased;
use Flarum\Gdpr\Models\ErasureRequest;
use Flarum\Post\Event\Deleted as PostDeleted;
use Flarum\Post\Event\Hidden as PostHidden;
use Flarum\Post\Event\Posted;
use Flarum\Post\Event\Restored as PostRestored;
use Flarum\Tags\Event\DiscussionWasTagged;
use Flarum\Tags\Tag;
use FoF\DiscussionLanguage\Event\LanguageChanged;
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
        $events->listen(LanguageChanged::class, [$this, 'whenLanguageChanged']);

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

    public function whenLanguageChanged(LanguageChanged $event): void
    {
        foreach ($this->tagsOf($event->discussion) as $tag) {
            $this->refreshLanguage($tag, $this->key($event->oldLanguageId));
            $this->refreshLanguage($tag, $this->languageKey($event->discussion));

            $tag->save();
        }
    }

    /**
     * Content awaiting flarum/approval is private, so none of the above counted it.
     * Registered only when flarum/approval is enabled.
     */
    public function whenPostWasApproved(PostWasApproved $event): void
    {
        $discussion = $event->post->discussion;

        // flarum/approval makes the discussion public when it saves it, which may
        // be after this listener.
        if ($discussion->is_private) {
            $discussion->afterSave(fn (Discussion $discussion) => $this->refreshLanguageOf($discussion));
        } else {
            $this->refreshLanguageOf($discussion);
        }
    }

    /**
     * flarum/gdpr's deletion mode deletes a user's posts in one query, without
     * the post events above. Registered only when flarum/gdpr is enabled.
     */
    public function whenUserWasErased(Erased $event): void
    {
        if ($event->mode !== ErasureRequest::MODE_DELETION) {
            return;
        }

        // Deleting posts can only change or remove a language's last discussion.
        Tag::query()->whereNotNull('localised_last_discussion')->each(function (Tag $tag) {
            foreach (array_keys($this->localisedLastDiscussions($tag)) as $lang) {
                $this->refreshLanguage($tag, (string) $lang);
            }

            $tag->save();
        });
    }

    protected function refreshLanguageOf(Discussion $discussion): void
    {
        if ($discussion->is_private || $discussion->hidden_at !== null) {
            return;
        }

        foreach ($this->tagsOf($discussion) as $tag) {
            $this->refreshLanguage($tag, $this->languageKey($discussion));

            $tag->save();
        }
    }

    /**
     * @param \Flarum\Discussion\Discussion $discussion
     * @param int                           $delta
     * @param Collection<Tag>|null          $tags
     * @param \Flarum\Post\Post             $post:      This is only used when a post has been hidden
     */
    protected function updateTags(Discussion $discussion, int $delta = 0, ?Collection $tags = null, ?\Flarum\Post\Post $post = null): void
    {
        $tags ??= $this->tagsOf($discussion);

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
            if ($delta >= 0 && $discussion->exists && !$discussion->is_private && $discussion->hidden_at == null && $discussion->last_posted_at && (!Arr::exists($localisedLastPost, $lang) || ($discussion->last_posted_at->timestamp >= $localisedLastPost[$lang]['at']))) {
                $this->setLocalisedLastDiscussion($tag, $discussion);
            } elseif ($discussion->id == ($localisedLastPost[$lang]['id'] ?? null)) {
                // This is to persist refreshLastPost above. It is here instead of there so that
                // if it's not necessary, we save a DB query.
                if ($post) {
                    $discussion->save();
                }

                // This discussion is currently the last posted discussion, but since it didn't qualify for the above check,
                // it should not be the last posted discussion. Therefore, we should refresh the last posted discussion..
                $this->refreshLanguage($tag, $lang);
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
    /**
     * @return Collection<int, Tag>
     */
    protected function tagsOf(Discussion $discussion): Collection
    {
        // A relation flarum/tags adds to the model.
        /** @phpstan-ignore-next-line */
        return $discussion->tags;
    }

    protected function languageKey(Discussion $discussion): string
    {
        return $this->key($discussion->language_id);
    }

    protected function key(?int $languageId): string
    {
        return (string) $languageId;
    }

    /**
     * Sets a language's last discussion in the tag to the latest one, or none.
     */
    protected function refreshLanguage(Tag $tag, string $lang): void
    {
        $lastPostedDiscussion = $tag->discussions()
            ->where('is_private', false)
            ->whereNull('hidden_at')
            ->whereNotNull('last_posted_at')
            ->where('language_id', $lang === '' ? null : (int) $lang)
            ->latest('last_posted_at')
            ->first();

        if ($lastPostedDiscussion) {
            $this->setLocalisedLastDiscussion($tag, $lastPostedDiscussion);
        } else {
            $this->removeLocalisedLastDiscussion($tag, $lang);
        }
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
