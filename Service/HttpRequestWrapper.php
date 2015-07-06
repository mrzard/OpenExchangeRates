<?php


namespace Mrzard\OpenExchangeRates\Service;


class HttpRequestWrapper implements HttpRequestInterface
{
    /** @var object */
    private $wrappedRequest;

    /**
     * @param object $wrappedRequest
     */
    public function __construct($wrappedRequest)
    {
        $this->wrappedRequest = $wrappedRequest;
    }

    /**
     * @return object
     */
    public function getRequest()
    {
        return $this->wrappedRequest;
    }
}