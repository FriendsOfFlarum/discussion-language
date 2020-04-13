<?php


namespace FoF\DiscussionLanguage\Commands;


use Flarum\User\AssertPermissionTrait;
use FoF\DiscussionLanguage\DiscussionLanguage;
use FoF\DiscussionLanguage\Validators\DiscussionLanguageValidator;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;

class UpdateLanguageCommandHandler
{
    use AssertPermissionTrait;

    /**
     * @var Dispatcher
     */
    private $events;

    /**
     * @var DiscussionLanguageValidator
     */
    private $validator;

    public function __construct(Dispatcher $events, DiscussionLanguageValidator $validator)
    {
        $this->events = $events;
        $this->validator = $validator;
    }

    public function handle(UpdateLanguageCommand $command)
    {
        $actor = $command->actor;
        $data = $command->data;

        $this->assertAdmin($actor);


        $discussionLanguage = DiscussionLanguage::findOrFail($command->id);

        if (Arr::has($data, 'code')) {
            $discussionLanguage->code = Arr::get($data, 'code');
        }

        $this->validator->assertValid($discussionLanguage->getDirty());

        $discussionLanguage->save();

        return $discussionLanguage;
    }
}
