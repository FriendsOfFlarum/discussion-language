<?php


namespace FoF\DiscussionLanguage;


use Flarum\Database\AbstractModel;
use Flarum\Discussion\Discussion;

/**
 * @property $id string
 * @property $code string
 */
class DiscussionLanguage extends AbstractModel
{
    public function discussion() {
        return $this->belongsTo(Discussion::class, 'language_id');
    }
}
