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
     *
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
     * @param array<string, mixed> $query     the discussions index query
     *
     * @return array<string, mixed>
     */
    public static function searchHandler(array $arguments, array $query, McpContext $context): array
    {
        $argument = trim((string) ($arguments['language'] ?? ''));

        if ($argument === '') {
            return $query;
        }

        $languages = resolve(Languages::class);
        $map = $languages->codes();

        // Forum languages are few; a longer list would only cost memory to split.
        if (strlen($argument) > 1000) {
            throw new ToolError('Too many languages. '.self::choices($languages, $map));
        }

        $codes = array_unique(array_filter(array_map('trim', explode(',', $argument)), fn (string $code) => $code !== ''));

        if (!$codes) {
            throw new ToolError('No language given. '.self::choices($languages, $map));
        }

        $query['filter']['language'] = implode(',', array_map(fn (string $code) => $map[self::find($languages, $map, $code)], $codes));

        return $query;
    }

    /**
     * @param array<string, mixed> $schema
     *
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
     * @param array<string, mixed>                                                           $arguments
     * @param array{attributes?: array<string, mixed>, relationships?: array<string, mixed>} $body
     *
     * @return array<string, mixed>
     */
    public static function startHandler(array $arguments, array $body, McpContext $context): array
    {
        $languages = resolve(Languages::class);
        $id = self::find($languages, $languages->codes(), trim((string) ($arguments['language'] ?? '')));

        $body['relationships']['language'] = ['data' => ['type' => 'discussion-languages', 'id' => (string) $id]];

        return $body;
    }

    /**
     * @param array<int, string>|null $codes
     */
    public static function choices(Languages $languages, ?array $codes = null): string
    {
        $codes ??= $languages->codes();
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
     * Codes stored apart only by case are ambiguous, so neither is guessed.
     *
     * @param array<int, string> $codes
     *
     * @throws ToolError
     */
    protected static function find(Languages $languages, array $codes, string $code): int
    {
        if (($id = array_search($code, $codes, true)) !== false) {
            return $id;
        }

        $matches = array_keys(array_filter($codes, fn (string $stored) => strcasecmp($stored, $code) === 0));

        if (count($matches) === 1) {
            return $matches[0];
        }

        throw new ToolError(
            ($code === '' ? 'A discussion needs a language.' : "Unknown language: $code.").' '.self::choices($languages, $codes)
        );
    }
}
