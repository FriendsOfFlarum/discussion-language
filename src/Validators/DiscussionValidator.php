<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) 2020 - 2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Validators;

use Flarum\Foundation\AbstractValidator;

class DiscussionValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    protected $rules = [
        'language' => ['required', 'int', 'exists:discussion_languages,id'],
    ];

    /**
     * {@inheritdoc}
     */
    protected function getMessages()
    {
        $error = app('translator')->trans('fof-discussion-language.api.discussion.validation_error');

        return [
            'required' => $error,
            'exists'   => $error,
        ];
    }
}
