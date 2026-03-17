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

use Flarum\Filter\FilterInterface;
use Flarum\Filter\FilterState;
use Flarum\Search\AbstractRegexGambit;
use Flarum\Search\SearchState;
use FoF\DiscussionLanguage\DiscussionLanguage;
use Illuminate\Database\Query\Builder;

class LanguageFilterGambit extends AbstractRegexGambit implements FilterInterface
{
    public function getGambitPattern(): string
    {
        return 'language:(.+)';
    }

    public function getFilterKey(): string
    {
        return 'language';
    }

    public function filter(FilterState $filterState, string $filterValue, bool $negate): void
    {
        $codes = explode(',', trim($filterValue, '"'));

        $this->constrain($filterState->getQuery(), $negate, $codes);
    }

    protected function constrain(Builder $query, bool $negate, array $codes): void
    {
        $codes = array_filter($codes, fn ($c) => $c !== 'any');

        if (empty($codes)) {
            return;
        }

        $sub = DiscussionLanguage::whereIn('code', $codes)->select('id');

        if ($negate) {
            $query->where(function ($query) use ($sub) {
                $query->whereNotIn('discussions.language_id', $sub)
                    ->orWhereNull('discussions.language_id');
            });
        } else {
            $query->whereIn('discussions.language_id', $sub);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function conditions(SearchState $search, array $matches, $negate)
    {
        $codes = explode(',', trim($matches[1], '"'));

        $this->constrain($search->getQuery(), $negate, $codes);
    }
}
