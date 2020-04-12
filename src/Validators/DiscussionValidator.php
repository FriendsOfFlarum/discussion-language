<?php


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
            'exists' => $error,
        ];
    }
}
