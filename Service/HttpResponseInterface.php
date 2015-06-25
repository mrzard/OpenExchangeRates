<?php


namespace Mrzard\OpenExchangeRates\Service;


interface HttpResponseInterface
{
    /**
     * Parse the JSON response body and return the JSON decoded data.
     * @return mixed
     */
    public function json();
}