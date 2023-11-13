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

class UpdateLanguageCommandHandler
{
    /**
     * @var DiscussionLanguageValidator
     */
    private $validator;

    public function __construct(DiscussionLanguageValidator $validator)
    {
        $this->validator = $validator;
    }

    public function handle(UpdateLanguageCommand $command)
    {
        $command->actor->assertAdmin();
        $data = $command->data;

        $discussionLanguage = DiscussionLanguage::findOrFail($command->id);

        if (Arr::has($data, 'code')) {
            $discussionLanguage->code = Arr::get($data, 'code');
        }

        if (Arr::has($data, 'country')) {
            $discussionLanguage->country = Arr::get($data, 'country');
        }

        $this->validator->assertValid($discussionLanguage->getDirty());

        $discussionLanguage->save();

        return $discussionLanguage;
    }
}
