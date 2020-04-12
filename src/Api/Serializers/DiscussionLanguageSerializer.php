<?php


namespace FoF\DiscussionLanguage\Api\Serializers;


use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Discussion\Discussion;

class DiscussionLanguageSerializer extends AbstractSerializer
{
    protected $type = 'discussion-languages';

    /**
     * @inheritDoc
     */
    protected function getDefaultAttributes($model)
    {
        return [
            'code' => $model->code
        ];
    }

    public function discussion() {
        return $this->hasOne(Discussion::class, DiscussionSerializer::class);
    }
}
