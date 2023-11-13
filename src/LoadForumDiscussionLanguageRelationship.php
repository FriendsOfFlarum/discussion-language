<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage;

use Flarum\Api\Controller\ShowForumController;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Expose the complete `DiscussionLanguage` list to clients by adding it as a
 * relationship to the /api endpoint. Since the `Forum` model
 * doesn't actually have a `DiscussionLanguage` relationship, we will manually load and
 * assign the languages data to it using `prepareDataForSerialization`.
 */
class LoadForumDiscussionLanguageRelationship
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param ShowForumController    $controller
     * @param                        $data
     * @param ServerRequestInterface $request
     */
    public function __invoke(ShowForumController $controller, &$data, ServerRequestInterface $request)
    {
        $data['discussionLanguages'] = $this->getLanguages();
    }

    protected function getLanguages(): Collection
    {
        $langs = DiscussionLanguage::query()->get();

        if ($this->settings->get('fof-discussion-language.showAnyLangOpt')) {
            $code = 'any';

            // Add a fake "any" language to the list
            $langs->prepend(new DiscussionLanguage(compact('code')));
        }

        return $langs;
    }
}
