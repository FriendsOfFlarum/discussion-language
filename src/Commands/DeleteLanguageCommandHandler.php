<?php


namespace FoF\DiscussionLanguage\Commands;


use Flarum\User\AssertPermissionTrait;
use FoF\DiscussionLanguage\DiscussionLanguage;
use FoF\DiscussionLanguage\Validators\DiscussionLanguageValidator;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;

class DeleteLanguageCommandHandler
{
    use AssertPermissionTrait;

    public function handle(DeleteLanguageCommand $command)
    {
        $actor = $command->actor;

        $this->assertAdmin($actor);

        $language = DiscussionLanguage::findOrFail($command->id);

        $language->delete();
    }
}
