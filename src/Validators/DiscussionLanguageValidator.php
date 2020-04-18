<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Validators;

use Flarum\Foundation\AbstractValidator;

class DiscussionLanguageValidator extends AbstractValidator
{
    protected $rules = [
        'code' => ['required', 'string', 'min:2', 'unique:discussion_languages'],
    ];
}
