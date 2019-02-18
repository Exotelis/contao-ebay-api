<?php

/**
 * This file is part of exotelis/contao-ebay-api
 *
 * Copyright (c) 2019 Sebastian Krah
 *
 * @package   exotelis/contao-ebay-api
 * @author    Sebatian Krah <exotelis@mailbox.org>
 * @copyright 2019 Sebastian Krah
 */

declare(strict_types=1);

namespace Exotelis\EbayApi;

/**
 * Class EbayApi
 *
 * Wrapper that calls the EbayApi
 *
 * @author   Sebastian Krah <exotelis@mailbox.org>
 */
class EbayApi
{
    /**
     * API Url
     *
     * @var String
     */
    protected $apiEndpoint;

    /**
     * Request header information
     *
     * @var array
     */
    protected $requestHeader;

    /**
     * Request body
     *
     * @var array
     */
    protected $requestBody;

    /**
     * Result from API call
     *
     * @var array
     */
    protected $result;

    /**
     * Status of the last api call
     *
     * @var boolean
     */
    protected $error;

    /**
     * @param String $apiEndpoint     API-URL you want to call
     * @param array  $requestHeader   Header of the request
     * @param array  $requestBody     Body of the request
     *
     * @throws \Exception
     */
    public function __construct($apiEndpoint, $requestHeader = null, $requestBody = null) {
        if($requestHeader != null && !\is_array($requestHeader)) {
            throw new \Exception("requestHeader " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['mustBeArr'] . " " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['reportIssue']);
        }

        if($requestBody != null && !\is_array($requestBody)) {
            throw new \Exception("requestBody " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['mustBeArr'] . " " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['reportIssue']);
        }

        $this->apiEndpoint = $apiEndpoint;
    }

    /**
     * Sets the request header
     *
     * @param array $requestHeader
     *
     * @throws \Exception
     * @return \Exotelis\EbayApi\EbayApi
     */
    public function setRequestHeader($requestHeader) {
        if(!\is_array($requestHeader)) {
            throw new \Exception("requestHeader " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['mustBeArr'] . " " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['reportIssue']);
        }

        $this->requestHeader = $requestHeader;

        return $this;
    }

    /**
     * Sets the request body
     *
     * @param array $requestBody   The request as an array which will be converted to xml
     *
     * @throws \Exception
     * @return \Exotelis\EbayApi\EbayApi
     */
    public function setRequestBody($requestBody) {
        if(!\is_array($requestBody)) {
            throw new \Exception("requestBody " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['mustBeArr'] . " " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['reportIssue']);
        }

        // TODO setRequestHeader must be called first and convert the body depending on the defined header (xml,json,nv)
        $xml = $this->array2xml($requestBody);

        if(\is_null($xml)) {
            throw new \Exception($GLOBALS['TL_LANG']['ERR']['ebayApi']['convert2xml'] . " " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['reportIssue']);
        }
        $this->requestBody = $xml->asXML();

        return $this;
    }

    /**
     * Calls the API
     *
     * @throws \Exception
     * @return \Exotelis\EbayApi\EbayApi
     */
    public function callApi() {
        if(\is_null($this->requestHeader) || \is_null($this->requestBody)) {
            throw new \Exception($GLOBALS['TL_LANG']['ERR']['ebayApi']['headerBodyNotSet'] . " " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['reportIssue']);
        }

        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
        \curl_setopt($ch, CURLOPT_HTTPHEADER, $this->requestHeader);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = \curl_exec($ch);
        \curl_close($ch);

        // Convert xml 2 Array
        $this->result = $this->xml2array($result);

        // Set errory
        if($this->result["ack"] === "Success") {
            $this->error = false;
        } else {
            $this->error = true;
        }

        return $this;
    }

    /**
     * Displays if the last api call had an error
     *
     * @return bool
     * @throws \Exception
     */
    public function hasError() {
        if(\is_null($this->error)) {
            throw new \Exception($GLOBALS['TL_LANG']['ERR']['ebayApi']['hasErr']  . " " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['reportIssue']);
        }
        return $this->error;
    }

    /**
     * Gets the unfiltered result
     *
     * @throws \Exception
     * @return mixed
     */
    public function getResult() {
        if(\is_null($this->result)) {
            throw new \Exception($GLOBALS['TL_LANG']['ERR']['ebayApi']['noResults']  . " " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['reportIssue']);
        }

        return $this->result;
    }

    /**
     * Converts array to xml
     *
     * @param array                  $requestBody
     * @param null|\SimpleXMLElement $xmlParser
     *
     * @throws \Exception
     * @return null|\SimpleXMLElement
     */
    protected function array2xml($requestBody, $xmlParser=null) {
        if(\count($requestBody) != 1 && \is_null($xmlParser)) {
            throw new \Exception($GLOBALS['TL_LANG']['ERR']['ebayApi']['more1root']  . " " . $GLOBALS['TL_LANG']['ERR']['ebayApi']['reportIssue']);
        }

        $root = false;
        if(\is_null($xmlParser)) {
            $root = true;
            $xmlParser = new \SimpleXMLElement("<" . \key($requestBody) . "/>");
        }

        foreach($requestBody as $k => $v) {
            if($k === "@attributes") {
                continue;
            }

            if(\is_array($v) && \key_exists("@attributes", $v)) {
                foreach($v["@attributes"] as $ak => $av) {
                    $xmlParser->addAttribute($ak, $av);
                }
            }

            $k = \str_replace("@", "", $k);

            if(\is_array($v)) {
                if($root) {
                    $this->array2xml($v, $xmlParser);
                } else {
                    $this->array2xml($v, $xmlParser->addChild($k));
                }
            } else {
                $xmlParser->addChild($k, (string)$v);
            }
        }

        return $xmlParser;
    }

    /**
     * Converts xml to array
     *
     * @param String $xml
     *
     * @return array mixed
     */
    protected function xml2array($xml) {
        return \json_decode(\json_encode(simplexml_load_string($xml)), true);
    }
}