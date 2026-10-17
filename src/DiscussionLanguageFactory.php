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

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscussionLanguage>
 */
class DiscussionLanguageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'    => $this->faker->unique()->languageCode(),
            'country' => $this->faker->countryCode(),
        ];
    }
}
