<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Data;

use Flarum\Gdpr\Data\Type;
use Flarum\Tags\TagState;

/**
 * The language a user chose for each tag they follow, kept in the tag_user rows
 * that fof/follow-tags' own data type anonymises but leaves in place.
 */
class SubscriptionLanguages extends Type
{
    public function export(): ?array
    {
        // fof/follow-tags exports each of the user's tag_user rows, this column with them.
        return null;
    }

    public function anonymize(): void
    {
        TagState::query()
            ->where('user_id', $this->user->id)
            ->whereNotNull('dl_language_id')
            ->update(['dl_language_id' => null]);
    }

    public static function deleteDescription(): string
    {
        return self::anonymizeDescription();
    }

    public function delete(): void
    {
        $this->anonymize();
    }
}
