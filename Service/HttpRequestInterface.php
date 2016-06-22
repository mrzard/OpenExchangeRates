<?php


namespace Mrzard\OpenExchangeRates\Service;


interface HttpRequestInterface
{
    /**
     * @return object
     */
    public function request();
}