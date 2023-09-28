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

/**
 * This class is only called when `fof/follow-tags` is enabled.
 */
class AddTagSerializerAttributes
{
    public function __invoke(TagSerializer $serializer, Tag $tag, array $attributes): array
    {
        $state = $tag->stateFor($serializer->getActor());

        if (isset($state->dl_language_id)) {
            $language = DiscussionLanguage::find($state->dl_language_id);

            $attributes['subscriptionLanguage'] = $language ? $language->code : null;
        } else {
            $attributes['subscriptionLanguage'] = null;
        }

        return $attributes;
    }
}
