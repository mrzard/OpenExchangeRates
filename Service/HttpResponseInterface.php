<?php


namespace Mrzard\OpenExchangeRates\Service;


use Psr\Http\Message\ResponseInterface;

interface HttpResponseInterface
{
    /**
     * @return ResponseInterface
     */
    public function getResponse();
}