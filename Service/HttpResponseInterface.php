<?php


namespace Mrzard\OpenExchangeRates\Service;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

interface HttpResponseInterface
{
    /**
     * @return StreamInterface
     */
    public function getBody();

    /**
     * @return ResponseInterface
     */
    public function getWrappedResponse();
}