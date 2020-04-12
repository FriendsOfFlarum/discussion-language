<?php


namespace FoF\DiscussionLanguage\Commands;


use Flarum\User\User;

class DeleteLanguageCommand
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var string
     */
    public $id;

    /**
     * @param User $actor
     * @param string $id
     */
    public function __construct(User $actor, $id)
    {
        $this->actor = $actor;
        $this->id = $id;
    }
}
