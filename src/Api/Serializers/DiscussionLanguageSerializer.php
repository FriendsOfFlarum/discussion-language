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
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\TabularDataReader;
use Rinvex\Country\CountryLoader;

class DiscussionLanguageSerializer extends AbstractSerializer
{
    protected $type = 'discussion-languages';

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var TabularDataReader
     */
    protected $records;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
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

            'name' => $this->getLanguageName($model->code, $native),

            'emoji' => $showFlag ? (isset($country) ? $country->getEmoji() : null) : null,
        ];
    }

    public function discussion()
    {
        return $this->hasOne(Discussion::class, DiscussionSerializer::class);
    }

    protected function getLanguageName(string $code, bool $native)
    {
        if (!$this->records) {
            $csv = Reader::createFromPath(__DIR__.'/../../../resources/wikipedia-iso-639-2-codes.csv');
            $csv->setHeaderOffset(5);
//            $csv->skipInputBOM()

            $stmt = Statement::create();

            $this->records = $stmt->process($csv);
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
