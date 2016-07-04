<?php


namespace Mrzard\OpenExchangeRates\Service;


class HttpResponseWrapper implements HttpResponseInterface
{
    /** @var object */
    private $wrappedResponse;

    /**
     * @param object $wrappedResponse
     */
    public function __construct($wrappedResponse)
    {
        $this->wrappedResponse = $wrappedResponse;
    }

    /**
     * @return object
     */
    public function getWrappedResponse()
    {
        return $this->wrappedResponse;
    }

    /**
     * @inheritdoc
     */
    public function getBody()
    {
        return $this->wrappedResponse->getBody();
    }
}