<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Event;

use Flarum\Discussion\Discussion;
use Flarum\User\User;

/**
 * A discussion was moved into another language, after it was saved.
 */
class LanguageChanged
{
    public function __construct(
        public readonly Discussion $discussion,
        public readonly ?int $oldLanguageId,
        public readonly User $actor
    ) {
    }
}
