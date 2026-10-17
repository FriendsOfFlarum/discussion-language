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
use FoF\DiscussionLanguage\Search\LanguageFilter;

/**
 * Stands in for flarum/sticky's mutator, which leaves a list alone once any
 * filter but a tag's narrows it. A list filtered by language is still the list
 * it was, in the reader's language, so sticky sees it without that filter and
 * decides as it would for the list itself.
 */
class PinStickiedDiscussionsToTop
{
    public function __construct(
        protected StickyPinStickiedDiscussionsToTop $sticky
    ) {
    }

    public function __invoke(DatabaseSearchState $state, SearchCriteria $criteria): void
    {
        $filters = array_filter($state->getActiveFilters(), fn ($filter) => ! $filter instanceof LanguageFilter);

        if (count($filters) === count($state->getActiveFilters())) {
            ($this->sticky)($state, $criteria);

            return;
        }

        // The same query, so what sticky does to it is done to the list.
        $withoutLanguage = new DatabaseSearchState($state->getActor(), $state->isFulltextSearch(), $state->getDefaultSort());
        $withoutLanguage->setQuery($state->getQuery());

        foreach ($filters as $filter) {
            $withoutLanguage->addActiveFilter($filter);
        }

        ($this->sticky)($withoutLanguage, $criteria);
    }
}
