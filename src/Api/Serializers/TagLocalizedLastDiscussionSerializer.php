<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Api\Serializers;

use Flarum\Discussion\Discussion;
use Flarum\Tags\Api\Serializer\TagSerializer;
use Flarum\Tags\Tag;

class TagLocalizedLastDiscussionSerializer
{
    public function __invoke(TagSerializer $serializer, Tag $tag, array $attributes)
    {
        $json = json_decode($tag->localised_last_discussion, true);

        // Attach discussion title as this is needed for the tags page
        if ($json) {
            foreach ($json as $languageId => $data) {
                $discussion = optional(Discussion::find($data['id']));

                $data['title'] = $discussion->title ?: '';
                $json[$languageId] = $data;
            }
        }

        $attributes['localisedLastDiscussion'] = $json;

        return $attributes;
    }
}
