<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\DiscussionLanguage\DiscussionLanguage;

class ForumResourceFields
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Relationship\ToMany::make('discussionLanguages')
                ->type('discussion-languages')
                ->includable()
                ->get(function (object $forum, Context $context) {
                    $languages = DiscussionLanguage::query()->whereVisibleTo($context->getActor())->get()->all();

                    if ($this->settings->get('fof-discussion-language.showAnyLangOpt')) {
                        array_unshift($languages, DiscussionLanguage::any());
                    }

                    return $languages;
                }),
        ];
    }
}
