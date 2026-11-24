<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Mcp;

use Flarum\Mcp\Capability\McpContext;
use Flarum\Mcp\Exception\ToolError;
use FoF\DiscussionLanguage\Languages;

class LanguageArguments
{
    /**
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    public static function searchSchema(array $schema): array
    {
        $schema['properties']['language'] = [
            'type'        => 'string',
            'description' => 'Only discussions in this language, by code (e.g. "en"), or in any of several, comma-separated. The forum overview lists the codes.',
        ];

        return $schema;
    }

    /**
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $query the discussions index query
     * @return array<string, mixed>
     */
    public static function searchHandler(array $arguments, array $query, McpContext $context): array
    {
        $codes = array_filter(array_map('trim', explode(',', (string) ($arguments['language'] ?? ''))), fn (string $code) => $code !== '');

        if ($codes) {
            $languages = resolve(Languages::class);

            $query['filter']['language'] = implode(',', array_map(
                fn (string $code) => $languages->codes()[self::find($languages, $code)],
                $codes
            ));
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    public static function startSchema(array $schema): array
    {
        $schema['properties']['language'] = [
            'type'        => 'string',
            'description' => 'The code of the language the discussion is written in (e.g. "en"). The forum overview lists the codes.',
        ];
        $schema['required'][] = 'language';

        return $schema;
    }

    /**
     * @param array<string, mixed> $arguments
     * @param array{attributes?: array<string, mixed>, relationships?: array<string, mixed>} $body
     * @return array<string, mixed>
     */
    public static function startHandler(array $arguments, array $body, McpContext $context): array
    {
        $id = self::find(resolve(Languages::class), trim((string) ($arguments['language'] ?? '')));

        $body['relationships']['language'] = ['data' => ['type' => 'discussion-languages', 'id' => (string) $id]];

        return $body;
    }

    public static function choices(Languages $languages): string
    {
        $codes = $languages->codes();
        ksort($codes);

        if (!$codes) {
            return 'The forum has no languages yet: an admin adds them in the extension\'s settings.';
        }

        return 'Use one of: '.implode(', ', array_map(
            fn (string $code) => ($name = $languages->name($code)) ? "$code ($name)" : $code,
            $codes
        )).'.';
    }

    /**
     * The ID of the language with this code, or else the only one whose code
     * differs from it in case alone.
     *
     * @throws ToolError
     */
    protected static function find(Languages $languages, string $code): int
    {
        $codes = $languages->codes();

        if (($id = array_search($code, $codes, true)) !== false) {
            return $id;
        }

        $matches = array_keys(array_filter($codes, fn (string $stored) => strcasecmp($stored, $code) === 0));

        if (count($matches) === 1) {
            return $matches[0];
        }

        throw new ToolError(
            ($code === '' ? 'A discussion needs a language.' : "Unknown language: $code.").' '.self::choices($languages)
        );
    }
}
