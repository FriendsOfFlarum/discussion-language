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

use Flarum\Database\AbstractModel;
use Flarum\Database\ScopeVisibilityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int|null    $id
 * @property string      $code
 * @property string|null $country
 */
class DiscussionLanguage extends AbstractModel
{
    use HasFactory;
    use ScopeVisibilityTrait;

    /**
     * The code of the language that stands for every language. It is never
     * stored: the forum's list of languages leads with it when the setting to
     * offer it is on.
     */
    public const ANY = 'any';

    public $fillable = ['code', 'country'];

    protected $casts = [
        'id' => 'integer',
    ];

    public static function any(): self
    {
        return new self(['code' => self::ANY]);
    }
}
