<?php


namespace Mrzard\OpenExchangeRates\Service;


interface HttpClientInterface
{
    /**
     * Create and return a new {@see HttpRequestInterface} object.
     *
     * @param string                $method  HTTP method
     * @param string|array|object   $url     URL or URI template
     * @param array                 $options Array of request options to apply.
     *
     * @return HttpRequestInterface
     */
    public function request($method, $url = null, array $options = []);

    /**
     * Sends a single request
     *
     * @param HttpRequestInterface $request Request to send
     *
     * @return HttpResponseInterface
     */
    public function send(HttpRequestInterface $request);
}