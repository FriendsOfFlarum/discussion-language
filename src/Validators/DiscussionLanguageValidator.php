<?php


namespace FoF\DiscussionLanguage\Validators;


use Flarum\Foundation\AbstractValidator;

class DiscussionLanguageValidator extends AbstractValidator
{
    protected $rules = [
        'code' => ['required', 'string', 'min:2', 'unique:discussion_languages']
    ];
}
