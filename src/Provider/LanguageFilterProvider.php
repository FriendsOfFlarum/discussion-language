<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Provider;

use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Sticky\PinStickiedDiscussionsToTop;
use FoF\DiscussionLanguage\Sticky\PinStickiedDiscussionsToTop as LanguageAwarePinStickiedDiscussionsToTop;

/**
 * No extender replaces another extension's search mutator, so flarum/sticky's
 * is swapped for ours, which calls it, where the searcher's mutators are kept.
 */
class LanguageFilterProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->extend('flarum.search.mutators', function (array $mutators) {
            foreach ($mutators[DiscussionSearcher::class] ?? [] as $key => $mutator) {
                if ($mutator === PinStickiedDiscussionsToTop::class) {
                    $mutators[DiscussionSearcher::class][$key] = LanguageAwarePinStickiedDiscussionsToTop::class;
                }
            }

            return $mutators;
        });
    }
}
