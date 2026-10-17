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

use Flarum\Locale\TranslatorInterface;
use Flarum\Settings\SettingsRepositoryInterface;
use IanM\ISO639\ISO639;
use Illuminate\Contracts\Cache\Repository as Cache;
use Rinvex\Country\CountryLoader;
use Throwable;

class Languages
{
    /**
     * Not 1.x's `fof-discussion-language.languages`, which a forum upgraded with
     * a warm cache still holds as a collection of 1.x models.
     */
    public const CODES_CACHE_KEY = 'fof-discussion-language.codes';

    /**
     * The names of the ISO 639-2 languages, by each of their codes: the
     * fallback for a code ISO 639-1 doesn't have.
     *
     * @var array<string, array{english: string, native: string}>|null
     */
    protected static ?array $iso6392 = null;

    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected ISO639 $iso,
        protected Cache $cache,
        protected TranslatorInterface $translator
    ) {
    }

    public function name(string $code): ?string
    {
        if ($code === DiscussionLanguage::ANY) {
            return $this->translator->trans('fof-discussion-language.forum.index_language.any');
        }

        $native = (bool) $this->settings->get('fof-discussion-language.native');

        $name = ucfirst($native ? $this->iso->nativeByCode1($code) : $this->iso->languageByCode1($code));

        if ($name) {
            return $name;
        }

        $entry = $this->iso6392()[$code] ?? null;

        return $entry ? ($native ? $entry['native'] : $entry['english']) : null;
    }

    public function flag(?string $country): ?string
    {
        if (!$country || !$this->settings->get('fof-discussion-language.showFlags')) {
            return null;
        }

        try {
            return CountryLoader::country($country)->getEmoji();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, string>
     */
    public function codes(): array
    {
        return $this->cache->rememberForever(self::CODES_CACHE_KEY, fn () => DiscussionLanguage::query()->pluck('code', 'id')->all());
    }

    public function forget(): void
    {
        $this->cache->forget(self::CODES_CACHE_KEY);
    }

    /**
     * @return array<string, array{english: string, native: string}>
     */
    protected function iso6392(): array
    {
        if (self::$iso6392 !== null) {
            return self::$iso6392;
        }

        // The build writes this from resources/wikipedia-iso-639-2-codes.csv.
        $records = json_decode((string) file_get_contents(__DIR__.'/../assets/iso-639-2.json'), true) ?: [];

        $index = [];

        foreach ($records as $record) {
            $entry = [
                'english' => $record['Language name(s)'],
                'native'  => $record['Native name(s)'] ?: $record['Language name(s)'],
            ];

            foreach (['639-1', '639-2', '639-3'] as $column) {
                if (!empty($record[$column])) {
                    $index[$record[$column]] = $entry;
                }
            }
        }

        return self::$iso6392 = $index;
    }
}
