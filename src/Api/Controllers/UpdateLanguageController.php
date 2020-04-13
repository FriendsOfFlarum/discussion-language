<?php


namespace FoF\DiscussionLanguage\Api\Controllers;


use Flarum\Api\Controller\AbstractShowController;
use FoF\DiscussionLanguage\Api\Serializers\DiscussionLanguageSerializer;
use FoF\DiscussionLanguage\Commands\UpdateLanguageCommand;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class UpdateLanguageController extends AbstractShowController
{
    public $serializer = DiscussionLanguageSerializer::class;

    /**
     * @var Dispatcher
     */
    protected $bus;

    /**
     * @param Dispatcher $bus
     */
    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @inheritDoc
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        return $this->bus->dispatch(
            new UpdateLanguageCommand($request->getAttribute('actor'), Arr::get($request->getQueryParams(), 'id'), Arr::get($request->getParsedBody(), 'data.attributes', []))
        );
    }
}
