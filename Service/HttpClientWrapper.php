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
    public function send($request)
    {
        $request = $request instanceof HttpRequestInterface ? $request->request() : new HttpRequestWrapper($request);

        return $this->wrapResponse($this->wrappedClient->send($request));
    }

    /**
     * @inheritdoc
     */
    public function request($method, $uri = null, array $options = [])
    {
        $response = $this->wrappedClient->request($method, $uri, $options);

        return $this->wrapResponse($response);
    }

    private function wrapResponse($response)
    {
        if ($response instanceof HttpResponseInterface) {
            return $response;
        }
        if (!method_exists($response, 'getBody')) {

            throw new \ErrorException('Supplied client\'s response don\'t have method `getBody()`');
        }
        return new HttpResponseWrapper($response);
    }
}