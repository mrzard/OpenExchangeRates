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
     * @var HttpClientInterface
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
        if (!method_exists($client, 'createRequest')) {
            throw new \ErrorException('Supplied client don\'t have method `createRequest(method, url, options)`');
        }
        if (!method_exists($client, 'send')) {
            throw new \ErrorException('Supplied client don\'t have method `send(request)`');
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

        $request = $this->client->createRequest(
            'GET',
            $this->getEndPoint().'/convert/'.$value.'/'.$symbolFrom.'/'.$symbolTo,
            array('query' => $query)
        );

        return $this->runRequest($request);
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

        $request = $this->client->createRequest(
            'GET',
            $this->getEndPoint().'/latest.json',
            array('query' => $this->prepareSymbols($query, $symbols))
        );

        return $this->runRequest($request);
    }


    /**
     * Gets a list of all available currencies
     */
    public function getCurrencies()
    {
        $request = $this->client->createRequest(
            'GET',
            $this->getEndPoint().'/currencies.json',
            array('query' => array('app_id' => $this->getAppId()))
        );

        return $this->runRequest($request);
    }


    /**
     * Run guzzle request
     *
     * @param HttpRequestInterface $request
     *
     * @return array
     */
    private function runRequest($request)
    {
        try {
            $response = $this->client->send($request);
            //send the req and return the json
            return $response->json();
        } catch (Exception $e) {
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
        $request = $this->client->createRequest(
            'GET',
            $this->getEndPoint().'/historical/'.$date->format('Y-m-d').'.json',
            array(
                'query' => array(
                    'app_id' => $this->getAppId(),
                    'base' => $this->getBaseCurrency()
                )
            )
        );

        return $this->runRequest($request);
    }
}
