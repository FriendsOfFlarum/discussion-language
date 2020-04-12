<?php


namespace FoF\DiscussionLanguage\Commands;


use Flarum\User\AssertPermissionTrait;
use FoF\DiscussionLanguage\DiscussionLanguage;
use FoF\DiscussionLanguage\Validators\DiscussionLanguageValidator;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;

class CreateLanguageCommandHandler
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

    public function handle(CreateLanguageCommand $command)
    {
        $actor = $command->actor;
        $data = $command->data;

        $this->assertAdmin($actor);

        $discussionLanguage = new DiscussionLanguage();
        $discussionLanguage->code = Arr::get($data, 'code');

        $this->validator->assertValid($discussionLanguage->getAttributes());

        $discussionLanguage->save();

        return $discussionLanguage;
    }
}
