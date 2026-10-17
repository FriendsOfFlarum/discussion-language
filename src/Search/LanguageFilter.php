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
use Flarum\Search\Filter\FilterInterface;
use Flarum\Search\SearchState;
use Flarum\Search\ValidateFilterTrait;
use FoF\DiscussionLanguage\DiscussionLanguage;
use Illuminate\Database\Eloquent\Builder;

/**
 * @implements FilterInterface<DatabaseSearchState>
 */
class LanguageFilter implements FilterInterface
{
    use ValidateFilterTrait;

    public function getFilterKey(): string
    {
        return 'language';
    }

    /**
     * @param DatabaseSearchState $state
     */
    public function filter(SearchState $state, string|array $value, bool $negate): void
    {
        $this->constrain($state->getQuery(), $this->asStringArray($value), $negate);
    }

    /**
     * @param string[] $codes
     */
    protected function constrain(Builder $query, array $codes, bool $negate): void
    {
        $codes = array_filter($codes, fn (string $code) => $code !== DiscussionLanguage::ANY);

        if (empty($codes)) {
            return;
        }

        $languageIds = DiscussionLanguage::query()->whereIn('code', $codes)->select('id');

        if ($negate) {
            $query->where(function (Builder $query) use ($languageIds) {
                $query->whereNotIn('discussions.language_id', $languageIds)
                    ->orWhereNull('discussions.language_id');
            });
        } else {
            $query->whereIn('discussions.language_id', $languageIds);
        }
    }
}
