<?php

namespace App\Resolver;

use App\ApiHelperTrait;
use App\Async\TornadoGqlAdapter;
use GraphQL\Executor\Promise\Promise as GqlPromise;
use M6Web\Tornado\EventLoop;
use M6Web\Tornado\HttpClient;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Psr\Http\Message\ResponseInterface;

class Sentence implements ResolverInterface, AliasedInterface
{
    /** @var HttpClient */
    private $httpClient;

    /** @var EventLoop */
    private $eventLoop;

    /** @var TornadoGqlAdapter */
    private $adapter;

    public function __construct(EventLoop $eventLoop, HttpClient $httpClient, TornadoGqlAdapter $adapter)
    {
        $this->httpClient = $httpClient;
        $this->eventLoop = $eventLoop;
        $this->adapter = $adapter;
    }

    public function findByText(string $textUrl): GqlPromise
    {
        return $this->adapter->fromTornadoToGql($this->eventLoop->async($this->findByTextAsync($textUrl)));
    }

    private function findByTextAsync(string $textUrl): \Generator
    {
        /** @var ResponseInterface $response */
        $response = yield $this->httpClient->sendRequest(
            ApiHelperTrait::getRequest($textUrl)
        );

        $text = \json_decode($response->getBody()->__toString(), true);

        return array_map(
            function($url) {
                return ['url' => $url];
            },
            $text['sentences']
        );
    }

    public static function getAliases()
    {
        return [
            'findByText' => 'findByText',
        ];
    }


}
