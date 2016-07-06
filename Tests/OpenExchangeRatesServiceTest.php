<?php

namespace Mrzard\OpenExchangeRatesBundle\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Mrzard\OpenExchangeRates\Service\OpenExchangeRatesService;

class OpenExchangeRatesServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get service configuration
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            array(
                'f4k3',
                array(
                    'base_currency' => 'USD',
                    'https' => false
                )
            )
        );
    }

    /**
     * @dataProvider getServiceConfig
     * Test that the functions can run
     * @param $appId
     * @param $config
     */
    public function testService($appId, array $config)
    {
        $fakeClient = $this->mockClient($this->mockResponse());
        $testingService = new OpenExchangeRatesService($appId, $config, $fakeClient);
        $latest = $testingService->getLatest(array(), null);
        static::assertTrue($latest['ok'], 'getLatest failed');

        $fakeClient = $this->mockClient($this->mockResponse());
        $testingService = new OpenExchangeRatesService($appId, $config, $fakeClient);
        $latest = $testingService->getLatest(array('EUR'), null);
        static::assertTrue($latest['ok'], 'getLatest failed');

        $fakeClient = $this->mockClient($this->mockResponse());
        $testingService = new OpenExchangeRatesService($appId, $config, $fakeClient);
        $latest = $testingService->getLatest(array('EUR'), 'USD');
        static::assertTrue($latest['ok'], 'getLatest failed');

        $fakeClient = $this->mockClient($this->mockResponse());
        $testingService = new OpenExchangeRatesService($appId, $config, $fakeClient);
        $latest = $testingService->getLatest(array(), 'USD');
        static::assertTrue($latest['ok'], 'getLatest failed');

        $fakeClient = $this->mockClient($this->mockResponse());
        $testingService = new OpenExchangeRatesService($appId, $config, $fakeClient);
        $currencies = $testingService->getCurrencies();
        static::assertTrue($currencies['ok'], 'getCurrencies failed');

        $fakeClient = $this->mockClient($this->mockResponse());
        $testingService = new OpenExchangeRatesService($appId, $config, $fakeClient);
        $convertCurrency = $testingService->convertCurrency(10, 'EUR', 'USD');
        static::assertTrue($convertCurrency['ok'], 'convertCurrency failed');

        $fakeClient = $this->mockClient($this->mockResponse());
        $testingService = new OpenExchangeRatesService($appId, $config, $fakeClient);
        $getHistorical = $testingService->getHistorical(new \DateTime('2014-01-01'));
        static::assertTrue($getHistorical['ok'], 'getHistorical failed');
    }

    /**
     * @dataProvider getServiceConfig
     * Test that the class can be instantiated
     * @param $appId
     * @param $config
     */
    public function testInstantiation($appId, array $config)
    {
        $service = new OpenExchangeRatesService($appId, $config, $this->mockClient(null));
        static::assertTrue($service instanceof OpenExchangeRatesService, 'Creation failed');
    }

    /**
     * @dataProvider getServiceConfig
     * Test what happens when an error is thrown
     * @param $appId
     * @param $config
     */
    public function testError($appId, array $config)
    {
        //all request will return a fake response
        $fakeResponse = $this->mockResponse();

        //create our fake client
        $fakeClient = $this->mockClient($fakeResponse);

        //make send throw an exception
        $fakeClient->expects(static::any())->method('request')->willThrowException(
            new \Exception('testException')
        );

        $testingService = new OpenExchangeRatesService($appId, $config, $fakeClient);

        static::assertArrayHasKey('error', $testingService->getCurrencies(), 'Error was not properly checked');
    }

    /**
     * @dataProvider getServiceConfig
     * Test general config
     * @param $appId
     * @param $config
     */
    public function testConfig($appId, array $config)
    {
        $fakeClient = $this->mockClient($this->mockRequest(), $this->mockResponse());
        $testingService = new OpenExchangeRatesService($appId, $config, $fakeClient);

        static::assertEquals($config['https'], $testingService->useHttps(), 'https config mismatch');

        static::assertEquals(
            $config['base_currency'],
            $testingService->getBaseCurrency(),
            'base_currency config mismatch'
        );

        $testingService->setHttps(true);
        static::assertTrue($testingService->useHttps(), 'https setter failed');

        static::assertEquals(
            'https://openexchangerates.org/api',
            $testingService->getEndPoint(),
            'Endpoint does not look right'
        );

        $testingService->setHttps(false);
        static::assertEquals(
            'http://openexchangerates.org/api',
            $testingService->getEndPoint(),
            'Endpoint does not look right'
        );

        $testingService->setBaseCurrency('EUR');
        static::assertEquals(
            'EUR',
            $testingService->getBaseCurrency(),
            'base currency setter failed'
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockRequest()
    {
        $fakeRequest = $this
            ->getMockBuilder('Mrzard\OpenExchangeRates\Service\HttpRequestInterface')
            ->setMethods(array('request'))
            ->getMock();
        $fakeRequest
            ->expects(static::any())
            ->method('request')
            ->will(static::returnSelf());

        return $fakeRequest;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Mrzard\OpenExchangeRates\Service\HttpResponseInterface
     */
    private function mockResponse()
    {
        $fakeResponse = $this
            ->getMockBuilder('Mrzard\OpenExchangeRates\Service\HttpResponseInterface')
            ->setMethods(array('getWrappedResponse', 'getBody'))
            ->getMock();
        $responseObject =  new Response(200, ['Content-Type' => 'application/json'], '{"ok":true}');
        $fakeResponse
            ->expects(static::any())
            ->method('getWrappedResponse')
            ->willReturn($responseObject);
        $fakeResponse
            ->expects(static::any())
            ->method('getBody')
            ->willReturn($responseObject->getBody());

        return $fakeResponse;
    }

    /**
     * @param $fakeResponse
     * @return \PHPUnit_Framework_MockObject_MockObject|\Mrzard\OpenExchangeRates\Service\HttpClientInterface
     */
    private function mockClient($fakeResponse)
    {
        $fakeClient = $this
            ->getMockBuilder('Mrzard\OpenExchangeRates\Service\HttpClientInterface')
            ->setMethods(array('request'))
            ->getMock();

        //our client will always return a our request
        $fakeClient
            ->expects(static::any())
            ->method('request')
            ->withAnyParameters()
            ->will(static::returnValue($fakeResponse));

        return $fakeClient;
    }
}