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
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;
use Rinvex\Country\CountryLoader;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    /**
     * @var TabularDataReader
     */
    protected $records;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(SettingsRepositoryInterface $settings, ISO639 $iso, TranslatorInterface $translator)
    {
        $this->settings = $settings;
        $this->iso = $iso;
        $this->translator = $translator;
    }

    /**
     * @param \FoF\DiscussionLanguage\DiscussionLanguage $model
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

            'name' => $this->getLanguageName($model->code, $native),

            'emoji' => $showFlag ? (isset($country) ? $country->getEmoji() : null) : null,
        ];
    }

    public function discussion()
    {
        return $this->hasOne(Discussion::class, DiscussionSerializer::class);
    }

    public function getId($model): string
    {
        return $model->code === 'any' ? 'any' : $model->id;
    }

    protected function getLanguageName(string $code, bool $native)
    {
        if ($code === 'any') {
            return $this->translator->trans('fof-discussion-language.forum.index_language.any');
        }

        if ($this->records === null) {
            $csv = Reader::createFromPath(__DIR__.'/../../../resources/wikipedia-iso-639-2-codes.csv');
            $csv->setHeaderOffset(0);

            $stmt = Statement::create();

            $this->records = $stmt->process($csv);
        }

        $name = ucfirst(
            $native
                ? $this->iso->nativeByCode1($code)
                : $this->iso->languageByCode1($code)
        );

        // Use ISO 639-1 name to simplify display
        if ($name) {
            return $name;
        }

        /*
         * array:9 [▼
         *    "639-2[1]" => "aar"
         *    "639-3[2]" => "aar"
         *    "639-5[3]" => ""
         *    "639-1" => "aa"
         *    "Language name(s) from ISO 639-2[1]" => "Afar"
         *    "Scope" => "Individual"
         *    "Type" => "Living"
         *    "Native name(s)" => "Qafaraf; ’Afar Af; Afaraf; Qafar af"
         *    "Other name(s)" => ""
         *  ]
         */
        foreach ($this->records as $record) {
            $iso6391 = $record['639-1'];
            $iso6392 = $record['639-2'];
            $iso6393 = $record['639-3'];

            $englishName = $record['Language name(s)'];
            $nativeName = $record['Native name(s)'] ?: $englishName;

            if ($iso6391 == $code || $iso6392 == $code || $iso6393 == $code) {
                return $native ? $nativeName : $englishName;
            }
        }
    }
}
