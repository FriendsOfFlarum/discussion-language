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

use Flarum\Search\Filter\FilterInterface;
use Flarum\Search\SearchState;
use FoF\DiscussionLanguage\DiscussionLanguage;
use Illuminate\Database\Query\Builder;

class LanguageFilter implements FilterInterface
{

    public function getFilterKey(): string
    {
        return 'language';
    }

    public function filter(SearchState $state, array|string $value, bool $negate): void
    {
        $codes = explode(',', trim($value, '"'));

        $this->constrain($state->getQuery(), $negate, $codes);
    }

    protected function constrain(\Illuminate\Database\Eloquent\Builder $query, bool $negate, array $codes): void
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
}
