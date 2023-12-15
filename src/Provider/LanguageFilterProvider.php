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

use Flarum\Discussion\Filter\DiscussionFilterer;
use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Sticky\PinStickiedDiscussionsToTop;
use FoF\DiscussionLanguage\Sticky\PinStickiedDiscussionsToTop as CustomPinStickiedDiscussionsToTop;

class LanguageFilterProvider extends AbstractServiceProvider
{
    public function register()
    {
        $this->container->extend('flarum.filter.filter_mutators', function ($mutators) {
            if (isset($mutators[DiscussionFilterer::class]) && is_array($mutators[DiscussionFilterer::class])) {
                foreach ($mutators[DiscussionFilterer::class] as $key => $mutator) {
                    if ($mutator === PinStickiedDiscussionsToTop::class) {
                        $mutators[DiscussionFilterer::class][$key] = CustomPinStickiedDiscussionsToTop::class;
                    }
                }
            }

            return $mutators;
        });
    }
}
