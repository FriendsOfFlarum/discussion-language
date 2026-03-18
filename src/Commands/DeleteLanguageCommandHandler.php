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
use Illuminate\Contracts\Cache\Repository as Cache;

class DeleteLanguageCommandHandler
{
    private Cache $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function handle(DeleteLanguageCommand $command): void
    {
        $command->actor->assertAdmin();

        $language = DiscussionLanguage::findOrFail($command->id);

        $language->delete();

        $this->cache->forget(AddTagSerializerAttributes::CACHE_KEY);
        $this->cache->forget(DiscussionLanguageSerializer::CSV_CACHE_KEY);
    }
}
