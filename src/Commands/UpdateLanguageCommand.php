<?php


namespace FoF\DiscussionLanguage\Commands;


use Flarum\User\User;

class UpdateLanguageCommand
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var int
     */
    public $id;

    /**
     * @var array
     */
    public $data;

    /**
     * @param User $actor
     * @param int $id
     * @param array $data
     */
    public function __construct(User $actor, int $id, array $data)
    {
        $this->actor = $actor;
        $this->id = $id;
        $this->data = $data;
    }
}
