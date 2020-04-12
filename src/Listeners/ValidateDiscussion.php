<?php


namespace FoF\DiscussionLanguage\Listeners;


use Flarum\Discussion\Event\Saving;
use FoF\DiscussionLanguage\Validators\DiscussionValidator;
use Illuminate\Support\Arr;

class ValidateDiscussion
{
    /**
     * @var DiscussionValidator
     */
    protected $validator;

    public function __construct(DiscussionValidator $validator)
    {
        $this->validator = $validator;
    }

    public function handle(Saving $event)
    {
        if ($event->discussion->exists) return;

        $languageId = Arr::get($event->data, 'relationships.language.data.id');

        $this->validator->assertValid([ 'language' => $languageId ]);

        $event->discussion->language_id = $languageId;
    }
}
