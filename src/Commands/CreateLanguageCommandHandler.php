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

use FoF\DiscussionLanguage\AddTagSerializerAttributes;
use FoF\DiscussionLanguage\DiscussionLanguage;
use FoF\DiscussionLanguage\Validators\DiscussionLanguageValidator;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Arr;

class CreateLanguageCommandHandler
{
    private DiscussionLanguageValidator $validator;
    private Cache $cache;

    public function __construct(DiscussionLanguageValidator $validator, Cache $cache)
    {
        $this->validator = $validator;
        $this->cache = $cache;
    }

    public function handle(CreateLanguageCommand $command): DiscussionLanguage
    {
        $command->actor->assertAdmin();
        $data = $command->data;

        $discussionLanguage = new DiscussionLanguage();
        $discussionLanguage->code = Arr::get($data, 'code');
        $discussionLanguage->country = Arr::get($data, 'country');

        $this->validator->assertValid($discussionLanguage->getAttributes());

        $discussionLanguage->save();

        $this->cache->forget(AddTagSerializerAttributes::CACHE_KEY);

        return $discussionLanguage;
    }
}
