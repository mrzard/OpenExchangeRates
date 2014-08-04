<?php
namespace Mrzard\OpenExchangeRatesBundle\Tests;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Mrzard\OpenExchangeRates\Service\OpenExchangeRatesService;

class OpenExchangeRatesServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OpenExchangeRatesService
     */
    protected $mockedService;

    /**
     * Get service configuration
     *
     * @return array
     */
    protected function getServiceConfig()
    {
        return array(
            'base_currency' => 'USD',
            'https' => false
        );
    }

    /**
     * Set up test
     */
    public function setUp()
    {
        $fakeRequest = $this
            ->getMockBuilder('Guzzle\Http\Message\Request')
            ->setConstructorArgs([
                'GET',
                'localhost',
                []
            ])
            ->setMethods(['send', 'getResponse'])
            ->getMock();

        $fakeRequest->expects($this->any())->method('send')->willReturn(true);

        //all request will return a fake response
        $fakeRequest
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn(new Response(200, null, json_encode(['ok' => true])));

        //create our fake client
        $fakeClient = $this
            ->getMockBuilder('Guzzle\Http\Client')
            ->setMethods(['createRequest'])
            ->getMock();

        //our client will always return a our request
        $fakeClient
            ->expects($this->any())
            ->method('createRequest')
            ->withAnyParameters()
            ->will($this->returnValue($fakeRequest));

        $this->mockedService = $this
            ->getMockBuilder(
                'Mrzard\OpenExchangeRates\Service\OpenExchangeRatesService'
            )
            ->setConstructorArgs(array('f4k3', $this->getServiceConfig(), $fakeClient))
            ->setMethods(null)
            ->getMock();

    }

    /**
     * Test that the functions can run
     */
    public function testService()
    {
        $this->assertTrue(
            $this->mockedService->getLatest([], null)['ok'], 'getLatest failed'
        );
        $this->assertTrue(
            $this->mockedService->getLatest(['EUR'], null)['ok'], 'getLatest failed'
        );
        $this->assertTrue(
            $this->mockedService->getLatest(['EUR'], 'USD')['ok'], 'getLatest failed'
        );
        $this->assertTrue(
            $this->mockedService->getLatest([], 'USD')['ok'], 'getLatest failed'
        );
        $this->assertTrue(
            $this->mockedService->getCurrencies()['ok'], 'getCurrencies failed'
        );
        $this->assertTrue(
            $this->mockedService->convertCurrency(10, 'EUR', 'USD')['ok'],
            'convertCurrency failed'
        );
        $this->assertTrue(
            $this->mockedService->getHistorical(new \DateTime('2014-01-01'))['ok'],
            'getHistorical failed'
        );
    }

    /**
     * Test that the class can be instantiated
     */
    public function testInstantiation()
    {
        $service = new OpenExchangeRatesService(
            'f4k31d',
            $this->getServiceConfig(),
            new Client()
        );
        $this->assertTrue($service instanceof OpenExchangeRatesService, 'Creation failed');
    }

    /**
     * Test what happens when an error is thrown
     */
    public function testError()
    {
        $appId = 'f4k31d';
        $fakeRequest = $this
            ->getMockBuilder('Guzzle\Http\Message\Request')
            ->setConstructorArgs([
                'GET',
                'localhost',
                []
            ])
            ->setMethods(['send', 'getResponse'])
            ->getMock();

        //make send throw an exception
        $fakeRequest->expects($this->any())->method('send')->willThrowException(
            new \Exception('testException')
        );

        //all request will return a fake response
        $fakeRequest
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn(new Response(200, null, json_encode(['ok' => true])));

        //create our fake client
        $fakeClient = $this
            ->getMockBuilder('Guzzle\Http\Client')
            ->setMethods(['createRequest'])
            ->getMock();

        //our client will always return a our request
        $fakeClient
            ->expects($this->any())
            ->method('createRequest')
            ->withAnyParameters()
            ->will($this->returnValue($fakeRequest));


        $this->mockedService = $this
            ->getMockBuilder(
                'Mrzard\OpenExchangeRates\Service\OpenExchangeRatesService'
            )
            ->setConstructorArgs([$appId, $this->getServiceConfig(), $fakeClient])
            ->setMethods(null)
            ->getMock();

        $this->assertArrayHasKey(
            'error',
            $this->mockedService->getCurrencies(),
            'Error was not properly checked'
        );
    }

    /**
     * Test general config
     */
    public function testConfig()
    {
        $config = $this->getServiceConfig();

        $this->assertEquals(
            $config['https'],
            $this->mockedService->useHttps(),
            'https config mismatch'
        );

        $this->assertEquals(
            $config['base_currency'],
            $this->mockedService->getBaseCurrency(),
            'base_currency config mismatch'
        );

        $this->mockedService->setHttps(true);
        $this->assertEquals(
            true,
            $this->mockedService->useHttps(),
            'https setter failed'
        );
        $this->assertEquals(
            'https://openexchangerates.org/api',
            $this->mockedService->getEndPoint(),
            'Endpoint does not look right'
        );

        $this->mockedService->setHttps(false);
        $this->assertEquals(
            'http://openexchangerates.org/api',
            $this->mockedService->getEndPoint(),
            'Endpoint does not look right'
        );

        $this->mockedService->setBaseCurrency('EUR');
        $this->assertEquals(
            'EUR',
            $this->mockedService->getBaseCurrency(),
            'base currency setter failed'
        );
    }
}