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
        $uses = $context->actor->isGuest()
            ? 'search_discussions takes a language code in `language` (or several, comma-separated).'
            : 'start_discussion needs the code of the discussion\'s language in `language`; search_discussions takes one there too (or several, comma-separated).';

        return $uses.' '.LanguageArguments::choices(resolve(Languages::class));
    }
}
