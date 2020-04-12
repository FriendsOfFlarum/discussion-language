<?php


namespace FoF\DiscussionLanguage\Api\Controllers;


use Flarum\Api\Controller\AbstractDeleteController;
use FoF\DiscussionLanguage\Commands\DeleteLanguageCommand;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;

class DeleteLanguageController extends AbstractDeleteController
{
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
    protected function delete(ServerRequestInterface $request)
    {
        return $this->bus->dispatch(
            new DeleteLanguageCommand($request->getAttribute('actor'), Arr::get($request->getQueryParams(), 'id'))
        );
    }
}
