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
use Flarum\Tags\Search\HideHiddenTagsFromAllDiscussionsPage;
use FoF\DiscussionLanguage\Sticky\PinStickiedDiscussionsToTop as LanguageAwarePinStickiedDiscussionsToTop;
use FoF\DiscussionLanguage\Tags\HideHiddenTagsFromAllDiscussionsPage as LanguageAwareHideHiddenTagsFromAllDiscussionsPage;

/**
 * No extender replaces another extension's search mutator, so these are swapped
 * for ours, which call them, where the searcher's mutators are kept. flarum/sticky's
 * is only there when it is enabled.
 */
class LanguageFilterProvider extends AbstractServiceProvider
{
    protected const REPLACEMENTS = [
        PinStickiedDiscussionsToTop::class          => LanguageAwarePinStickiedDiscussionsToTop::class,
        HideHiddenTagsFromAllDiscussionsPage::class => LanguageAwareHideHiddenTagsFromAllDiscussionsPage::class,
    ];

    public function register(): void
    {
        $this->container->extend('flarum.search.mutators', function (array $mutators) {
            foreach ($mutators[DiscussionSearcher::class] ?? [] as $key => $mutator) {
                if (is_string($mutator) && isset(static::REPLACEMENTS[$mutator])) {
                    $mutators[DiscussionSearcher::class][$key] = static::REPLACEMENTS[$mutator];
                }
            }

            return $mutators;
        });
    }
}
