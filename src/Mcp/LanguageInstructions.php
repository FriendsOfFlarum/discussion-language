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
        return 'Every discussion has a language: start_discussion needs its code in `language`, and search_discussions takes `language` to search one or several. '
            .LanguageArguments::choices(resolve(Languages::class));
    }
}
