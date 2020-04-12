<?php


namespace FoF\DiscussionLanguage\Middleware;


use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddLanguageFilter implements MiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getQueryParams();

        if ($language = Arr::get($params, 'language')) {
            $request = $request->withQueryParams([
                'q' => Arr::get($params, 'q', '') . " language:$language"
            ]);
        }

        return $handler->handle($request);
    }
}
