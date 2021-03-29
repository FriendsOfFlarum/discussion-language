<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Api\Serializers;

use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Settings\SettingsRepositoryInterface;
use IanM\ISO639\ISO639;
use Rinvex\Country\CountryLoader;

class DiscussionLanguageSerializer extends AbstractSerializer
{
    protected $type = 'discussion-languages';

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var ISO639
     */
    protected $iso;

    public function __construct(SettingsRepositoryInterface $settings, ISO639 $iso)
    {
        $this->settings = $settings;
        $this->iso = $iso;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultAttributes($model)
    {
        $native = (bool) $this->settings->get('fof-discussion-language.native');
        $showFlag = (bool) $this->settings->get('fof-discussion-language.showFlags');

        try {
            $country = CountryLoader::country($model->country);
        } catch (\Throwable $ignored) {
        }

        return [
            'code'    => $model->code,
            'country' => $model->country,

            'name' => ucfirst(
                $native
                    ? $this->iso->nativeByCode1($model->code)
                    : $this->iso->languageByCode1($model->code)
            ) ?: null,

            'emoji' => $showFlag ? (isset($country) ? $country->getEmoji() : null) : null,
        ];
    }

    public function discussion()
    {
        return $this->hasOne(Discussion::class, DiscussionSerializer::class);
    }
}
