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

use Flarum\Api\Controller\CreateDiscussionController;
use Flarum\Api\Controller\ListDiscussionsController;
use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Api\Controller\ShowForumController;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Saving;
use Flarum\Discussion\Filter\DiscussionFilterer;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Extend;
use Flarum\Tags\Api\Serializer\TagSerializer;
use Flarum\Tags\Event\Creating as TagCreating;
use Flarum\Tags\Tag;
use Flarum\Tags\TagState;
use FoF\DiscussionLanguage\Api\Serializers\DiscussionLanguageSerializer;
use FoF\DiscussionLanguage\Api\Serializers\TagLocalizedLastDiscussionSerializer;
use FoF\FollowTags\Event\SubscriptionChanging;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    (new Extend\Routes('api'))
        ->post('/fof/discussion-language', 'fof.discussion-language.create', Api\Controllers\CreateLanguageController::class)
        ->patch('/fof/discussion-language/{id}', 'fof.discussion-language.update', Api\Controllers\UpdateLanguageController::class)
        ->delete('/fof/discussion-language/{id}', 'fof.discussion-language.delete', Api\Controllers\DeleteLanguageController::class),

    (new Extend\ServiceProvider())
        ->register(Provider\LanguageFilterProvider::class),

    (new Extend\Middleware('forum'))
        ->add(Middleware\AddLanguageFilter::class),

    (new Extend\Model(Discussion::class))
        ->cast('language_id', 'int')
        ->hasOne('language', DiscussionLanguage::class, 'id', 'language_id'),

    (new Extend\Model(Tag::class))
        ->cast('localised_last_discussion', 'string'),

    (new Extend\Model(TagState::class))
        ->cast('dl_language_id', 'int'),

    (new Extend\Event())
        ->listen(Saving::class, Listener\AddDiscussionLanguage::class)
        ->listen(TagCreating::class, Listener\TagCreating::class)
        ->subscribe(Listener\UpdateTagMetadata::class),

    (new Extend\SimpleFlarumSearch(DiscussionSearcher::class))
        ->addGambit(Search\LanguageFilterGambit::class),

    (new Extend\Filter(DiscussionFilterer::class))
        ->addFilter(Search\LanguageFilterGambit::class),

    (new Extend\Policy())
        ->modelPolicy(Discussion::class, Access\DiscussionPolicy::class),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->hasMany('discussionLanguages', DiscussionLanguageSerializer::class),

    (new Extend\ApiSerializer(DiscussionSerializer::class))
        ->hasOne('language', DiscussionLanguageSerializer::class)
        ->attributes(function (DiscussionSerializer $serializer, Discussion $discussion, array $attributes) {
            $attributes['canChangeLanguage'] = $serializer->getActor()->can('changeLanguage', $discussion);

            return $attributes;
        }),

    (new Extend\ApiController(ShowForumController::class))
        ->addInclude(['discussionLanguages'])
        ->prepareDataForSerialization(LoadForumDiscussionLanguageRelationship::class),

    (new Extend\ApiController(ListDiscussionsController::class))
        ->addInclude(['language']),

    (new Extend\ApiController(ShowDiscussionController::class))
        ->addInclude(['language']),

    (new Extend\ApiController(CreateDiscussionController::class))
        ->addInclude(['language']),

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

    (new Extend\ApiSerializer(TagSerializer::class))
        ->attributes(TagLocalizedLastDiscussionSerializer::class),

    (new Extend\Conditional())
        ->whenExtensionEnabled('fof-follow-tags', function () {
            return [
                (new Extend\ApiSerializer(TagSerializer::class))
                    ->attributes(AddTagSerializerAttributes::class),

                (new Extend\Event())
                    ->listen(SubscriptionChanging::class, Listener\SaveLanguageOnSubscription::class),

                (new Extend\Notification())
                    ->beforeSending(CheckNotificationRecipients::class),
            ];
        }),
];
