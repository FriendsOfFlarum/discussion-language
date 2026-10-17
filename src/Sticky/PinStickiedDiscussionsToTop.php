<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Sticky;

use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\SearchCriteria;
use Flarum\Sticky\PinStickiedDiscussionsToTop as StickyPinStickiedDiscussionsToTop;
use FoF\DiscussionLanguage\Search\IgnoresLanguageFilter;

/**
 * Stands in for flarum/sticky's mutator, which leaves a list alone once any
 * filter but a tag's narrows it.
 */
class PinStickiedDiscussionsToTop
{
    use IgnoresLanguageFilter;

    public function __construct(
        protected StickyPinStickiedDiscussionsToTop $sticky
    ) {
    }

    public function __invoke(DatabaseSearchState $state, SearchCriteria $criteria): void
    {
        ($this->sticky)($this->withoutLanguageFilter($state), $criteria);
    }
}
