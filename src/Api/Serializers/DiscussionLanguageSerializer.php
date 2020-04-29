<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Api\Serializers;

use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Discussion\Discussion;

class DiscussionLanguageSerializer extends AbstractSerializer
{
    protected $type = 'discussion-languages';

    /**
     * {@inheritdoc}
     */
    protected function getDefaultAttributes($model)
    {
        return [
            'code' => $model->code,
            'country' => $model->country,
        ];
    }

    public function discussion()
    {
        return $this->hasOne(Discussion::class, DiscussionSerializer::class);
    }
}
