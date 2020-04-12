<?php


namespace FoF\DiscussionLanguage\Validators;


use Flarum\Foundation\AbstractValidator;

class DiscussionLanguageValidator extends AbstractValidator
{
    protected $rules = [
        'code' => ['required']
    ];
}
