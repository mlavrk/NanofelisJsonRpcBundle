<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Response;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;

class RpcResponse implements RpcResponseInterface
{
    /**
     * RpcResponse constructor.
     */
    public function __construct(private mixed $data, private mixed $id = null)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function getContent(): array
    {
        return [
            'jsonrpc' => RpcRequest::JSON_RPC_VERSION,
            'result' => $this->data,
            'id' => $this->id,
        ];
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): void
    {
        $this->data = $data;
    }
}
