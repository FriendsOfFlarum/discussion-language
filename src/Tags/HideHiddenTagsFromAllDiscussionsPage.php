<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Tags;

use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\SearchCriteria;
use Flarum\Tags\Search\HideHiddenTagsFromAllDiscussionsPage as TagsHideHiddenTagsFromAllDiscussionsPage;
use FoF\DiscussionLanguage\Search\IgnoresLanguageFilter;

/**
 * Stands in for flarum/tags' mutator, which leaves a list alone once any filter
 * narrows it, so tags hidden from All Discussions showed in it in a language.
 */
class HideHiddenTagsFromAllDiscussionsPage
{
    use IgnoresLanguageFilter;

    public function __construct(
        protected TagsHideHiddenTagsFromAllDiscussionsPage $tags
    ) {
    }

    public function __invoke(DatabaseSearchState $state, SearchCriteria $criteria): void
    {
        ($this->tags)($this->withoutLanguageFilter($state), $criteria);
    }
}
