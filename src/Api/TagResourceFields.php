<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;
use Flarum\Tags\Tag;
use Psr\Http\Message\ServerRequestInterface;
use WeakMap;

class TagResourceFields
{
    /**
     * The titles of the discussions the tags in a response name, loaded for all
     * of them at once: the discussions each tag needs, and those already known.
     *
     * @var WeakMap<ServerRequestInterface, array{wanted: array<int, true>, titles: array<int, string>}>
     */
    protected WeakMap $titles;

    public function __construct()
    {
        $this->titles = new WeakMap();
    }

    public function __invoke(): array
    {
        return [
            Schema\Arr::make('localisedLastDiscussion')
                ->nullable()
                ->get(function (Tag $tag, Context $context) {
                    // Pre-1.x tags, and those created while the extension was off, have none.
                    $discussions = json_decode($tag->localised_last_discussion ?? '{}', true);

                    if (! is_array($discussions) || ! $discussions) {
                        return [];
                    }

                    $titles = $this->titles[$context->request] ??= ['wanted' => [], 'titles' => []];

                    foreach ($discussions as $discussion) {
                        $titles['wanted'][(int) ($discussion['id'] ?? 0)] = true;
                    }

                    $this->titles[$context->request] = $titles;

                    // Resolved once every tag in the response has said what it wants.
                    return function () use ($discussions, $context) {
                        $titles = $this->resolveTitles($context);

                        foreach ($discussions as $language => $discussion) {
                            $discussions[$language]['title'] = $titles[(int) ($discussion['id'] ?? 0)] ?? '';
                        }

                        return $discussions;
                    };
                }),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function resolveTitles(Context $context): array
    {
        $state = $this->titles[$context->request];
        $missing = array_keys(array_diff_key($state['wanted'], $state['titles']));

        if ($missing) {
            // Those the actor can't see, or that are gone, have no title to give.
            $state['titles'] += array_fill_keys($missing, '');

            $found = Discussion::whereVisibleTo($context->getActor())
                ->whereIn('id', $missing)
                ->pluck('title', 'id');

            foreach ($found as $id => $title) {
                $state['titles'][(int) $id] = (string) $title;
            }

            $this->titles[$context->request] = $state;
        }

        return $state['titles'];
    }
}
