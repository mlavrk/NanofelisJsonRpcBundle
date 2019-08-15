<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Event;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Symfony\Component\EventDispatcher\Event;

class RpcBeforeMethodEvent extends Event
{
    const NAME = 'nanofelis_json_rpc.before_request';

    /**
     * @var RpcRequest
     */
    private $rpcRequest;

    /**
     * @param RpcRpcRequest $payload
     */
    public function __construct(RpcRequest $rpcRequest)
    {
        $this->rpcRequest = $rpcRequest;
    }

    /**
     * @return RpcRequest
     */
    public function getRpcRequest(): RpcRequest
    {
        return $this->rpcRequest;
    }
}
