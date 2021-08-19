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
use FoF\DiscussionLanguage\DiscussionLanguage;
use Illuminate\Database\Query\Builder;

class LanguageFilter implements FilterInterface
{
    public function getFilterKey(): string
    {
        return 'language';
    }

    public function filter(FilterState $filterState, string $filterValue, bool $negate)
    {
        $this->constrain($filterState->getQuery(), $negate, $filterValue);
    }

    protected function constrain(Builder $query, bool $negate, string $filterValue)
    {
        $codes = explode(',', trim($filterValue, '"'));

        $query->where(function ($query) use ($codes, $negate) {
            foreach ($codes as $code) {
                $id = DiscussionLanguage::where('code', $code)->value('id');

                $query->orWhereIn('discussion.id', function ($query) use ($id) {
                    $query->select('discussion_id')
                        ->from('dicussion_tag')
                        ->where('language_id', $id);
                }, $negate);
            }
        });
    }
}
