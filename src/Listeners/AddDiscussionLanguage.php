<?php


namespace FoF\DiscussionLanguage\Listeners;


use Flarum\Discussion\Event\Saving;
use Flarum\User\AssertPermissionTrait;
use FoF\DiscussionLanguage\Validators\DiscussionValidator;
use Illuminate\Support\Arr;

class AddDiscussionLanguage
{
    use AssertPermissionTrait;

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
        $languageId = Arr::get($event->data, 'relationships.language.data.id');

        if (!$event->discussion->exists || $languageId) {
            $this->assertCan($event->actor, 'changeLanguage', $event->discussion);

            $this->validator->assertValid([ 'language' => $languageId ]);

            $event->discussion->language_id = $languageId;
        }
    }
}
