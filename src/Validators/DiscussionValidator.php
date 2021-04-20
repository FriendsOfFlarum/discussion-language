<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
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
        $error = resolve('translator')->trans('fof-discussion-language.api.discussion.validation_error');

        return [
            'required' => $error,
            'exists'   => $error,
        ];
    }
}
