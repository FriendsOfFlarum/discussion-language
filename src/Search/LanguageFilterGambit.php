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
    public function getGambitPattern()
    {
        return 'language:(.+)';
    }

    public function getFilterKey(): string
    {
        return 'language';
    }

    public function filter(FilterState $filterState, string $filterValue, bool $negate)
    {
        $codes = explode(',', trim($filterValue, '"'));

        $this->constrain($filterState->getQuery(), $negate, $codes);
    }

    protected function constrain(Builder $query, bool $negate, array $codes)
    {
        $query->where(function ($query) use ($codes, $negate) {
            foreach ($codes as $code) {
                $id = DiscussionLanguage::where('code', $code)->value('id');

                $query->orWhereIn('discussions.id', function ($query) use ($id) {
                    $query->select('discussion_id')
                        ->from('discussion_tag')
                        ->where('language_id', $id);
                }, $negate);
            }
        });
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
