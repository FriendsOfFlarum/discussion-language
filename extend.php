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

use Flarum\Api\Endpoint;
use Flarum\Api\Resource\DiscussionResource;
use Flarum\Api\Resource\ForumResource;
use Flarum\Approval\Event\PostWasApproved;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Extend;
use Flarum\Gdpr\Events\Erased;
use Flarum\Gdpr\Extend\UserData;
use Flarum\Search\Database\DatabaseSearchDriver;
use Flarum\Tags\Api\Resource\TagResource;
use Flarum\Tags\Tag;
use Flarum\Tags\TagState;
use FoF\FollowTags\Extend\Subscription;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->jsDirectory(__DIR__.'/js/dist/forum')
        ->css(__DIR__.'/resources/less/forum.less'),

    // No jsDirectory(): the admin frontend has no chunks to publish.
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    new Extend\ApiResource(Api\Resource\DiscussionLanguageResource::class),

    (new Extend\ApiResource(ForumResource::class))
        ->fields(Api\ForumResourceFields::class)
        ->endpoint(Endpoint\Show::class, fn (Endpoint\Show $endpoint) => $endpoint->addDefaultInclude(['discussionLanguages'])),

    (new Extend\ApiResource(DiscussionResource::class))
        ->fields(Api\DiscussionResourceFields::class)
        ->endpoint(
            [Endpoint\Index::class, Endpoint\Show::class, Endpoint\Create::class],
            fn (Endpoint\Index|Endpoint\Show|Endpoint\Create $endpoint) => $endpoint->addDefaultInclude(['language'])
        ),

    (new Extend\ApiResource(TagResource::class))
        ->fields(Api\TagResourceFields::class),

    (new Extend\Middleware('forum'))
        ->add(Middleware\AddLanguageFilter::class),

    (new Extend\Model(Discussion::class))
        ->cast('language_id', 'int')
        ->belongsTo('language', DiscussionLanguage::class, 'language_id'),

    (new Extend\Model(Tag::class))
        ->cast('localised_last_discussion', 'string')
        ->default('localised_last_discussion', '{}'),

    (new Extend\Model(TagState::class))
        ->cast('dl_language_id', 'int'),

    (new Extend\Event())
        ->subscribe(Listener\UpdateTagMetadata::class),

    (new Extend\Policy())
        ->modelPolicy(Discussion::class, Access\DiscussionPolicy::class),

    (new Extend\SearchDriver(DatabaseSearchDriver::class))
        ->addFilter(DiscussionSearcher::class, Search\LanguageFilter::class),

    (new Extend\Settings())
        ->default('fof-discussion-language.showFlags', true)
        ->default('fof-discussion-language.showAnyLangOpt', true)
        ->default('fof-discussion-language.allow_language_change', '10')
        ->default('fof-discussion-language.native', false)
        ->default('fof-discussion-language.filter_language_on_http_request', false)
        ->default('fof-discussion-language.useLocaleForTagsPageLastDiscussion', false)
        ->default('fof-discussion-language.composerLocaleDefault', false)
        ->serializeToForum('fof-discussion-language.composerLocaleDefault', 'fof-discussion-language.composerLocaleDefault', 'boolVal')
        ->serializeToForum('fof-discussion-language.showAnyLangOpt', 'fof-discussion-language.showAnyLangOpt', 'boolVal')
        ->serializeToForum('fof-discussion-language.useLocaleForTagsPageLastDiscussion', 'fof-discussion-language.useLocaleForTagsPageLastDiscussion', 'boolVal'),

    // flarum/tags' and flarum/sticky's search mutators, seeing a list filtered by language as the list it is.
    (new Extend\ServiceProvider())
        ->register(Provider\LanguageFilterProvider::class),

    (new Extend\Conditional())
        ->whenExtensionEnabled('flarum-approval', fn () => [
            (new Extend\Event())
                ->listen(PostWasApproved::class, [Listener\UpdateTagMetadata::class, 'whenPostWasApproved']),
        ])
        ->whenExtensionEnabled('flarum-gdpr', fn () => [
            (new Extend\Event())
                ->listen(Erased::class, [Listener\UpdateTagMetadata::class, 'whenUserWasErased']),

            (new UserData())
                ->addType(Data\SubscriptionLanguages::class),
        ])
        ->whenExtensionEnabled('fof-follow-tags', fn () => array_filter([
            (new Extend\ApiResource(TagResource::class))
                ->fields(Api\TagSubscriptionFields::class),

            (new Extend\Notification())
                ->beforeSending(CheckNotificationRecipients::class),

            // From fof/follow-tags 2.0.0, a subscriber may save the language with
            // their subscription. Before it, the language chosen in 1.x still applies.
            class_exists(Subscription::class)
                ? (new Subscription())->attribute('subscriptionLanguage')
                : null,
        ])),
];
