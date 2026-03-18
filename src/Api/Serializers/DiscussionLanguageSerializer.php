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
use Illuminate\Contracts\Cache\Repository as Cache;
use League\Csv\Reader;
use League\Csv\Statement;
use Rinvex\Country\CountryLoader;
use Symfony\Contracts\Translation\TranslatorInterface;

class DiscussionLanguageSerializer extends AbstractSerializer
{
    protected $type = 'discussion-languages';

    public const CSV_CACHE_KEY = 'fof-discussion-language.csv-index';

    /** @var array<string, array{english: string, native: string}>|null */
    private static ?array $csvIndex = null;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var ISO639
     */
    protected $iso;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(SettingsRepositoryInterface $settings, ISO639 $iso, Cache $cache, TranslatorInterface $translator)
    {
        $this->settings = $settings;
        $this->iso = $iso;
        $this->cache = $cache;
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

    public function discussion(): \Tobscure\JsonApi\Relationship
    {
        return $this->hasOne(Discussion::class, DiscussionSerializer::class);
    }

    public function getId($model): string
    {
        return $model->code === 'any' ? 'any' : $model->id;
    }

    protected function getLanguageName(string $code, bool $native): ?string
    {
        if ($code === 'any') {
            return $this->translator->trans('fof-discussion-language.forum.index_language.any');
        }

        $name = ucfirst(
            $native
                ? $this->iso->nativeByCode1($code)
                : $this->iso->languageByCode1($code)
        );

        if ($name) {
            return $name;
        }

        // Fallback to cached CSV index for codes not in ISO 639-1
        if (self::$csvIndex === null) {
            self::$csvIndex = $this->cache->rememberForever(self::CSV_CACHE_KEY, function () {
                return self::buildCsvIndex();
            });
        }

        $entry = self::$csvIndex[$code] ?? null;

        return $entry ? ($native ? $entry['native'] : $entry['english']) : null;
    }

    private static function buildCsvIndex(): array
    {
        $index = [];
        $csv = Reader::from(__DIR__.'/../../../resources/wikipedia-iso-639-2-codes.csv');
        $csv->setHeaderOffset(0);

        foreach ((new Statement())->process($csv) as $record) {
            $entry = [
                'english' => $record['Language name(s)'],
                'native'  => $record['Native name(s)'] ?: $record['Language name(s)'],
            ];

            foreach (['639-1', '639-2', '639-3'] as $col) {
                if (!empty($record[$col])) {
                    $index[$record[$col]] = $entry;
                }
            }
        }

        return $index;
    }
}
