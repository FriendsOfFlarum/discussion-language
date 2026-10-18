<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Search;

use Flarum\Search\Database\DatabaseSearchState;

/**
 * For another extension's search mutator that leaves a list alone once any
 * filter narrows it. A list filtered by language is still the list it was, in
 * the reader's language, so the mutator is shown it without that filter.
 */
trait IgnoresLanguageFilter
{
    /**
     * The search as it would be without the language filter, on the same query,
     * so what a mutator does to it is done to the list.
     */
    protected function withoutLanguageFilter(DatabaseSearchState $state): DatabaseSearchState
    {
        $filters = array_filter($state->getActiveFilters(), fn ($filter) => !$filter instanceof LanguageFilter);

        if (count($filters) === count($state->getActiveFilters())) {
            return $state;
        }

        $withoutLanguage = new DatabaseSearchState($state->getActor(), $state->isFulltextSearch(), $state->getDefaultSort());
        $withoutLanguage->setQuery($state->getQuery());

        foreach ($filters as $filter) {
            $withoutLanguage->addActiveFilter($filter);
        }

        return $withoutLanguage;
    }
}
