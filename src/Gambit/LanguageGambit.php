<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) 2020 - 2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Gambit;

use Flarum\Search\AbstractRegexGambit;
use Flarum\Search\AbstractSearch;
use FoF\DiscussionLanguage\DiscussionLanguage;

class LanguageGambit extends AbstractRegexGambit
{
    /**
     * {@inheritdoc}
     */
    protected $pattern = 'language:(.+)';

    /**
     * {@inheritdoc}
     */
    protected function conditions(AbstractSearch $search, array $matches, $negate)
    {
        $codes = explode(',', trim($matches[1], '"'));

        $search->getQuery()->where(function ($query) use ($codes, $negate) {
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
}
