<?php

namespace App\Async;

use GraphQL\Executor\Promise\Promise as GqlPromise;
use M6Web\Tornado\EventLoop;
use M6Web\Tornado\Promise;
use Overblog\GraphQLBundle\Executor\Promise\PromiseAdapterInterface;

class TornadoGqlAdapter implements PromiseAdapterInterface
{
    /** @var EventLoop */
    private $eventLoop;

    public function __construct(EventLoop $eventLoop)
    {
        $this->eventLoop = $eventLoop;
    }

    public function fromTornadoToGql(Promise $tornadoPromise): GqlPromise
    {
        return new GqlPromise($tornadoPromise, $this);
    }

    public function wait(GqlPromise $gqlPromise)
    {
        return $this->eventLoop->wait($gqlPromise->adoptedPromise);
    }

    public function isThenable($value)
    {
        return $value instanceof Promise;
    }

    public function convertThenable($thenable)
    {
        if ($thenable instanceof GqlPromise) {
            return $thenable;
        }

        assert($thenable instanceof Promise);

        return $this->fromTornadoToGql($thenable);
    }

    public function then(GqlPromise $promise, ?callable $onFulfilled = null, ?callable $onRejected = null)
    {
        return $this->fromTornadoToGql(
            $this->eventLoop->async(
                $this->thenAsGenerator($promise->adoptedPromise, $onFulfilled, $onRejected)
            )
        );
    }

    public function create(callable $resolver)
    {
        $deferred = $this->eventLoop->deferred();
        $resolver([$deferred, 'resolve'], [$deferred, 'reject']);

        return $this->fromTornadoToGql($deferred->getPromise());
    }

    public function createFulfilled($value = null)
    {
        return $this->fromTornadoToGql($this->eventLoop->promiseFulfilled($value));
    }

    public function createRejected($reason)
    {
        return $this->fromTornadoToGql($this->eventLoop->promiseRejected($reason));
    }

    public function all(array $promisesOrValues)
    {
        $promises = array_map(
            function($promiseOrValue) {
                return $promiseOrValue instanceof GqlPromise ?
                    $promiseOrValue->adoptedPromise : $this->eventLoop->promiseFulfilled($promiseOrValue);
            },
            $promisesOrValues
        );

        return $this->fromTornadoToGql($this->eventLoop->promiseAll(...$promises));
    }

    private function thenAsGenerator(Promise $promise, ?callable $onFulfilled, ?callable $onRejected): \Generator
    {
        try {
            $value = yield from $this->extractValue(yield $promise);

            if($onFulfilled === null) {
                return  $value;
            }

            return yield from $this->extractValue($onFulfilled($value));
        } catch(\Throwable $throwable) {
            if($onRejected !== null) {
                return yield from $this->extractValue($onRejected($throwable));
            }
            throw $throwable;
        }
    }

    private function extractValue($value): \Generator
    {
        while($value instanceof GqlPromise) {
            $value = yield $value->adoptedPromise;
        }
        return $value;
    }
}
