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

use Flarum\Database\AbstractModel;
use Flarum\Discussion\Discussion;

/**
 * @property $id string
 * @property $code string
 * @property $country string
 */
class DiscussionLanguage extends AbstractModel
{
    public function discussion()
    {
        return $this->belongsTo(Discussion::class, 'language_id');
    }
}
