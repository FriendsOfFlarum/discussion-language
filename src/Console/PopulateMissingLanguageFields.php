<?php

namespace FoF\DiscussionLanguage\Console;

use Flarum\Console\AbstractCommand;
use Illuminate\Database\Capsule\Manager as DB;

class PopulateMissingLanguageFields extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('discussion-language:populate-missing-language-fields')
            ->setDescription('Populates empty dl_language_id fields in tag_user table with the users preferred language');
    }

    protected function fire()
    {
        $requiredTables = ['tag_user', 'users', 'discussion_languages', 'test'];

        $schemaBuilder = $this->getSchemaBuilder();

        foreach ($requiredTables as $table) {
            if (!$schemaBuilder->hasTable($table)) {
                $this->error("Required table for command missing: $table");
                return;
            }
        }

        $this->info('Starting to populate missing language fields...');

        $languageMapping = DB::table('discussion_languages')->pluck('id', 'code')->toArray();

        $updatedRows = 0;

        DB::transaction(function () use (&$updatedRows, $languageMapping) {
            DB::table('tag_user')->whereNull('dl_language_id')
                ->orderBy('user_id')
                ->orderBy('tag_id')
                ->chunk(100, function ($tagUsers) use (&$updatedRows, $languageMapping) {
                    foreach ($tagUsers as $tagUser) {
                        $locale = DB::table('users')
                            ->where('id', $tagUser->user_id)
                            ->value('preferences->locale');

                        if ($locale && isset($languageMapping[$locale])) {
                            DB::table('tag_user')
                                ->where('user_id', $tagUser->user_id)
                                ->where('tag_id', $tagUser->tag_id)
                                ->update(['dl_language_id' => $languageMapping[$locale]]);

                            $updatedRows++;
                        }
                    }
                });
        });

        $this->info("Total entries updated: $updatedRows.");
    }

    private function getSchemaBuilder()
    {
        return resolve('flarum.db')->getSchemaBuilder();
    }
}
