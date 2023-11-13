<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Commands;

use FoF\DiscussionLanguage\DiscussionLanguage;
use FoF\DiscussionLanguage\Validators\DiscussionLanguageValidator;
use Illuminate\Support\Arr;

class CreateLanguageCommandHandler
{
    /**
     * @var DiscussionLanguageValidator
     */
    private $validator;

    public function __construct(DiscussionLanguageValidator $validator)
    {
        $this->validator = $validator;
    }

    public function handle(CreateLanguageCommand $command)
    {
        $command->actor->assertAdmin();
        $data = $command->data;

        $discussionLanguage = new DiscussionLanguage();
        $discussionLanguage->code = Arr::get($data, 'code');
        $discussionLanguage->country = Arr::get($data, 'country');

        $this->validator->assertValid($discussionLanguage->getAttributes());

        $discussionLanguage->save();

        return $discussionLanguage;
    }
}
