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
use FoF\DiscussionLanguage\Api\Serializers\DiscussionLanguageSerializer;
use FoF\DiscussionLanguage\DiscussionLanguage;
use FoF\DiscussionLanguage\Validators\DiscussionLanguageValidator;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Arr;

class UpdateLanguageCommandHandler
{
    private DiscussionLanguageValidator $validator;
    private Cache $cache;

    public function __construct(DiscussionLanguageValidator $validator, Cache $cache)
    {
        $this->validator = $validator;
        $this->cache = $cache;
    }

    public function handle(UpdateLanguageCommand $command): DiscussionLanguage
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

        $this->cache->forget(AddTagSerializerAttributes::CACHE_KEY);
        $this->cache->forget(DiscussionLanguageSerializer::CSV_CACHE_KEY);

        return $discussionLanguage;
    }
}
