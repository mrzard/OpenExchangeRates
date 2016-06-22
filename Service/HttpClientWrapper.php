<?php


namespace Mrzard\OpenExchangeRates\Service;


class HttpClientWrapper implements HttpClientInterface
{
    /**
     * @var object
     */
    private $wrappedClient;

    public function __construct($client)
    {
        $this->wrappedClient = $client;
    }

    /**
     * @return object
     */
    public function getWrappedClient()
    {
        return $this->wrappedClient;
    }

    /**
     * @inheritdoc
     */
    public function request($method, $url = null, array $options = [])
    {
        $request = $this->wrappedClient->request($method, $url, $options);
        return $this->wrapRequest($request);
    }

    private function wrapRequest($request)
    {
        return $request instanceof HttpRequestInterface ? $request : new HttpRequestWrapper($request);
    }

    /**
     * @inheritdoc
     */
    public function send(HttpRequestInterface $request)
    {
        return $this->wrapResponse($this->wrappedClient->send($request->getRequest()));
    }

    private function wrapResponse($response)
    {
        if ($response instanceof HttpResponseInterface) {
            return $response;
        }
        if (!method_exists($response, 'json')) {
            throw new \ErrorException('Supplied client\'s response don\'t have method `json()`');
        }
        return new HttpResponseWrapper($response);
    }
}