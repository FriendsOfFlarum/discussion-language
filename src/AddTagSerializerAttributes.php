<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage;

use Flarum\Tags\Api\Serializer\TagSerializer;
use Flarum\Tags\Tag;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * This class is only called when `fof/follow-tags` is enabled.
 */
class AddTagSerializerAttributes
{
    const CACHE_KEY = 'fof-discussion-language.languages';

    private Cache $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function __invoke(TagSerializer $serializer, Tag $tag, array $attributes): array
    {
        $state = $tag->stateFor($serializer->getActor());

        if (isset($state->dl_language_id)) {
            $languages = $this->cache->rememberForever(self::CACHE_KEY, function () {
                return DiscussionLanguage::all()->keyBy('id');
            });

            $language = $languages->get($state->dl_language_id);

            $attributes['subscriptionLanguage'] = $language ? $language->code : null;
        } else {
            $attributes['subscriptionLanguage'] = null;
        }

        return $attributes;
    }
}
