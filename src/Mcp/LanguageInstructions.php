<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Mcp;

use Flarum\Mcp\Capability\McpContext;
use FoF\DiscussionLanguage\Languages;

class LanguageInstructions
{
    public function __invoke(McpContext $context): string
    {
        $languages = resolve(Languages::class);

        if ($context->actor->isGuest()) {
            return 'search_discussions takes a language code in `language` (or several, comma-separated). '.LanguageArguments::choices($languages);
        }

        $ids = $languages->codes();
        ksort($ids);

        return 'start_discussion needs the code of the discussion\'s language in `language`; search_discussions takes one there too (or several, comma-separated). '
            .LanguageArguments::choices($languages, $ids)
            .($ids ? ' Change a discussion\'s language, where its canChangeLanguage allows: update_resource on discussions with relationships {"language": {"type": "discussion-languages", "id": <ID>}}, by ID: '
                .implode(', ', array_map(fn (int $id, string $code) => "$code=$id", array_keys($ids), $ids)).'.' : '');
    }
}
