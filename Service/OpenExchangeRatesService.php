<?php

/**
 * OpenExchangeRates Service
 *
 * @author Gonzalo Míguez (mrzard@gmail.com)
 * @since 2014
 */

namespace Mrzard\OpenExchangeRates\Service;

use DateTime;
use Exception;
use GuzzleHttp\ClientInterface;

/**
 * Class OpenExchangeRatesService
 *
 * This class exposes the OpenExchangeRates API
 *
 * @package Mrzard\OpenExchangeRatesBundle\Service
 */
class OpenExchangeRatesService
{
    /**
     * @var string
     * 
     * the app id
     */
    protected $appId;

    /**
     * @var string
     * 
     * the api endpoint
     */
    protected $endPoint = '://openexchangerates.org/api';

    /**
     * @var string
     * 
     * base currency
     */
    protected $baseCurrency = '';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var bool
     * 
     * https is used
     */
    protected $https;

    /**
     * Service constructor
     *
     * @param string $openExchangeRatesAppId the app_id for OpenExchangeRates
     * @param array  $apiOptions             Options for the OpenExchangeRatesApi
     * @param object $client                 Http client for requests
     * @throws \ErrorException
     */
    public function __construct($openExchangeRatesAppId, $apiOptions, $client)
    {
        $this->appId = $openExchangeRatesAppId;
        $this->https = (bool) $apiOptions['https'];
        $this->baseCurrency = (string) $apiOptions['base_currency'];
        $this->client = $this->wrapClient($client);
    }

    /**
     * @param $client
     * @return HttpClientInterface
     * @throws \ErrorException
     */
    private function wrapClient($client)
    {
        if ($client instanceof HttpClientInterface) {
            return $client;
        }
        if (!method_exists($client, 'request')) {
            throw new \ErrorException('Supplied client doesn\'t have method `request`');
        }
        // TODO: check methods parameters
        return new HttpClientWrapper($client);
    }

    /**
     * @return string
     */
    public function getEndPoint()
    {
        $protocol = 'http';
        if ($this->useHttps()) {
            $protocol .= 's';
        }

        return $protocol.$this->endPoint;
    }

    /**
     * Get the appId
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Get if https is enabled
     *
     * @return boolean
     */
    public function useHttps()
    {
        return $this->https;
    }

    /**
     * Sets whether to use https
     *
     * @param boolean $https
     */
    public function setHttps($https)
    {
        $this->https = (bool) $https;
    }

    /**
     * Get the base currency
     *
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->baseCurrency;
    }

    /**
     * Set the base currency
     *
     * @param string $baseCurrency
     */
    public function setBaseCurrency($baseCurrency)
    {
        $this->baseCurrency = $baseCurrency;
    }


    /**
     * Converts $value from currency $symbolFrom to currency $symbolTo
     *
     * @param float  $value      value to convert
     * @param string $symbolFrom symbol to convert from
     * @param string $symbolTo   symbol to convert to
     *
     * @return float
     */
    public function convertCurrency($value, $symbolFrom, $symbolTo)
    {
        $query = array('app_id' => $this->getAppId());

        return $this->getResponse(
            'GET',
            $this->getEndPoint().'/convert/'.$value.'/'.$symbolFrom.'/'.$symbolTo,
            array('query' => $query)
        );
    }

    /**
     * If base currency is overridden, return $baseCurrency, otherwise
     * return the object's base currency
     *
     * @param $baseCurrency
     *
     * @return string
     */
    protected function prepareBaseCurrency($baseCurrency)
    {
        return null === $baseCurrency ? $this->getBaseCurrency() : $baseCurrency;
    }

    /**
     * Given a query and symbols, puts them in the query or not
     *
     * @param $query
     * @param $symbols
     *
     * @return mixed
     */
    protected function prepareSymbols($query, $symbols)
    {
        if (count($symbols)) {
            $query['symbols'] = implode(',', $symbols);
        }

        return $query;
    }

    /**
     * Get the latest exchange rates
     *
     * @param array  $symbols array of currency codes to get the rates for.
     *                        Default empty (all currencies)
     * @param string $base    Base currency, default NULL (gets it from config)
     *
     * @return array
     */
    public function getLatest(array $symbols = array(), $base = null)
    {
        $query = array(
            'app_id' => $this->getAppId(),
            'base' => $this->prepareBaseCurrency($base)
        );

        return $this->getResponse(
            'GET',
            $this->getEndPoint().'/latest.json',
            array('query' => $this->prepareSymbols($query, $symbols))
        );
    }


    /**
     * Gets a list of all available currencies
     */
    public function getCurrencies()
    {
        return $this->getResponse(
            'GET',
            $this->getEndPoint().'/currencies.json',
            array('query' => array('app_id' => $this->getAppId()))
        );
    }


    /**
     * Format response
     *
     * @param $method  String
     * @param $uri     String
     * @param $options array
     *
     * @return array
     * @internal param HttpResponseInterface $response
     *
     */
    private function getResponse($method, $uri, $options)
    {
        try {
            $response = $this->client->request($method, $uri, $options);
            return json_decode($response->getResponse()->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return array('error' => '-1 Could not run request');
        }
    }


    /**
     * Get historical data
     *
     * @param \DateTime $date
     *
     * @return array
     */
    public function getHistorical(DateTime $date)
    {
        return $this->getResponse(
            'GET',
            $this->getEndPoint().'/historical/'.$date->format('Y-m-d').'.json',
            array(
                'query' => array(
                    'app_id' => $this->getAppId(),
                    'base' => $this->getBaseCurrency()
                )
            )
        );
    }
}
