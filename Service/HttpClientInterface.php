<?php


namespace Mrzard\OpenExchangeRates\Service;


interface HttpClientInterface
{
    /**
     * Sends a single request
     *
     * @param $method  String Method of the request
     * @param $uri     String Uri of the request
     * @param $options array  Request options
     *
     * @return HttpResponseInterface
     */
    public function request($method, $uri = null, array $options = []);
}