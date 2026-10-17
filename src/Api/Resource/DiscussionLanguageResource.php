<?php

/*
 * This file is part of fof/discussion-language.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionLanguage\Api\Resource;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use FoF\DiscussionLanguage\DiscussionLanguage;
use FoF\DiscussionLanguage\Languages;
use Illuminate\Database\Eloquent\Builder;
use Tobyz\JsonApiServer\Context;

/**
 * @extends AbstractDatabaseResource<DiscussionLanguage>
 */
class DiscussionLanguageResource extends AbstractDatabaseResource
{
    public function __construct(
        protected Languages $languages
    ) {
    }

    public function type(): string
    {
        return 'discussion-languages';
    }

    public function model(): string
    {
        return DiscussionLanguage::class;
    }

    public function scope(Builder $query, Context $context): void
    {
        $query->whereVisibleTo($context->getActor());
    }

    /**
     * @param DiscussionLanguage $model
     */
    public function getId(object $model, Context $context): string
    {
        // The forum's "any language" choice is never stored, so it has no key.
        return $model->code === DiscussionLanguage::ANY ? DiscussionLanguage::ANY : (string) $model->getKey();
    }

    public function find(string $id, Context $context): ?object
    {
        // Keys are integers: anything else, `any` among them, is no language.
        // PostgreSQL rejects comparing it with the key rather than finding none.
        if (!ctype_digit($id)) {
            return null;
        }

        return parent::find($id, $context);
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Create::make()
                ->admin(),
            Endpoint\Update::make()
                ->admin(),
            Endpoint\Delete::make()
                ->admin(),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('code')
                ->requiredOnCreate()
                ->writable()
                ->minLength(2)
                ->unique('discussion_languages', 'code', true),
            Schema\Str::make('country')
                ->requiredOnCreate()
                ->writable()
                ->nullable()
                ->minLength(2)
                // The column holds an ISO 3166 alpha-2 code.
                ->maxLength(2),
            Schema\Str::make('name')
                ->nullable()
                ->get(fn (DiscussionLanguage $language) => $this->languages->name($language->code)),
            Schema\Str::make('emoji')
                ->nullable()
                ->get(fn (DiscussionLanguage $language) => $this->languages->flag($language->country)),
        ];
    }

    public function saved(object $model, Context $context): ?object
    {
        $this->languages->forget();

        return parent::saved($model, $context);
    }

    public function deleted(object $model, Context $context): void
    {
        $this->languages->forget();

        parent::deleted($model, $context);
    }
}
