<?php

/**
 * This file is part of exotelis/contao-ebay-api
 *
 * Copyright (c) 2019 Sebastian Krah
 *
 * @package   exotelis/contao-ebay-api
 * @author    Sebatian Krah <exotelis@mailbox.org>
 * @copyright 2019 Sebastian Krah
 * @license   https://github.com/Exotelis/contao-ebay-api/blob/master/LICENSE LGPL-3.0
 */

declare(strict_types=1);

namespace Exotelis\EbayApi;

use Contao;
use Patchwork\Utf8;

/**
 * Class ModuleSellerItemList
 *
 * Front end module "seller item list"
 *
 * @property String     $ebayapi_appId
 * @property integer    $ebayapi_globalId
 * @property String     $ebayapi_seller
 * @property integer    $ebayapi_itemsPerPage
 * @property String     $ebayapi_sortOrder
 * @property string     $ebayapi_template
 * @author   Sebastian Krah <exotelis@mailbox.org>
 */
class ModuleSellerItemList extends Contao\Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_selleritemlist';

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new Contao\BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['selleritemlist'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $apiEndpoint = "https://svcs.ebay.com/services/search/FindingService/v1";
        $operationName = "findItemsAdvanced";

        // Get AppId
        $apiAppId = $this->Database->prepare("SELECT token FROM tl_ebayapi_auth WHERE id=?")
            ->execute($this->ebayapi_appId);

        // Reqeust header
        $requestHeader = array(
            "X-EBAY-SOA-SECURITY-APPNAME:" . $apiAppId->token,
            "X-EBAY-SOA-OPERATION-NAME:" . $operationName,
            "X-EBAY-SOA-REQUEST-DATA-FORMAT:xml",
            "X-EBAY-SOA-RESPONSE-DATA-FORMAT:xml",
            "X-EBAY-SOA-GLOBAL-ID:" . (new Util())->getGlobalIds()[$this->ebayapi_globalId],
            "X-EBAY-SOA-SERVICE-VERSION:1.10.0"
        );

        // Request body
        $requestBody = array(
            "findItemsAdvancedReqeust" => array (
                "@attributes" => array (
                    "xmlns" => "http://www.ebay.com/marketplace/search/v1/services"
                ),
                "itemFilter" => array(
                    "name" => "Seller",
                    "value" => $this->ebayapi_seller
                ),
                "outputSelector" => "GalleryInfo",
                "@outputSelector@" => "PictureURLLarge",
                "@@outputSelector@@" => "PictureURLSuperSize",
                "paginationInput" => array(
                    "entriesPerPage" => $this->ebayapi_itemsPerPage,
                    "pageNumber" => Contao\Input::get("page") ?? 1
                ),
                "sortOrder" => $this->ebayapi_sortOrder
            )
        );

        try {
            $ebayApi = new EbayApi($apiEndpoint);
            $ebayApi
                ->setRequestHeader($requestHeader)
                ->setRequestBody($requestBody)
                ->callApi();

            $result = $ebayApi->getResult();

            // Check for errors
            if($ebayApi->hasError()) {
                $error = \str_replace(".", "", $result["errorMessage"]["error"]["message"]);
                $this->Template->error =  $GLOBALS['TL_LANG']['ERR']['ebayApi'][$error] ? : $error;
                return;
            }
        } catch (\Exception $e) {
            $this->Template->error = $e->getMessage();
            return;
        }

        $count = (int)$result["searchResult"]["@attributes"]["count"];
        // If result is empty
        if($count === 0) {
            $this->Template->empty = $GLOBALS['TL_LANG']['MSC']['ebayApi']['emptyList'];
            return;
        }

        // If just a single result
        if($count === 1) {
            $temp = $result["searchResult"]["item"];
            $result["searchResult"]["item"] = array($temp);
        }

        $this->Template->items = $this->parseItems($result["searchResult"]["item"]);

        // Add the pagination menu
        $totalEntries = $result["paginationOutput"]["totalEntries"];
        $objPagination = new Contao\Pagination($totalEntries, $this->ebayapi_itemsPerPage);
        $this->Template->pagination = $objPagination->generate("\n  ");
    }

    protected function parseItems($items) {
        $arrItems = array();

        foreach($items as $item) {
            $arrItems[] = $this->parseItem($item);
        }

        return $arrItems;
    }

    protected function parseItem($item) {
        $template = new Contao\FrontendTemplate($this->ebayapi_template);

        $template->itemId = $item["itemId"];
        $template->title = trim($item["title"]);
        $template->viewItemURL = $item["viewItemURL"];
        $template->thumbnail = $item["galleryURL"];
        $template->largeImage = $item["pictureURLLarge"];
        $template->xLargeImage = $item["pictureURLSuperSize"];
        $template->condition = $item["condition"]["conditionDisplayName"];
        $template->placeholder = "bundles/exotelisebayapi/images/placeholder.png";

        // Shipping Info
        $template->shippingCosts = Contao\System::getFormattedNumber($item["shippingInfo"]["shippingServiceCost"]);
        $template->shippingType = $GLOBALS['TL_LANG']['MSC']['ebayApi']['shippingType'][$item["shippingInfo"]["shippingType"]] ? : $item["shippingInfo"]["shippingType"];
        $template->shipToLocations = $GLOBALS['TL_LANG']['MSC']['ebayApi']['shipToLocations'][$item["shippingInfo"]["shipToLocations"]] ? : $item["shippingInfo"]["shipToLocations"];

        // Selling Status
        $template->currentPrice = Contao\System::getFormattedNumber($item["sellingStatus"]["currentPrice"]);
        $template->convertedCurrentPrice = Contao\System::getFormattedNumber($item["sellingStatus"]["convertedCurrentPrice"]);

        // Listing Infos
        $template->startTime = Contao\Date::parse(Contao\Config::get('datimFormat'), \strtotime($item["listingInfo"]["startTime"]));
        $template->endTime = Contao\Date::parse(Contao\Config::get('datimFormat'), \strtotime($item["listingInfo"]["endTime"]));
        $template->listingType = $GLOBALS['TL_LANG']['MSC']['ebayApi']['listingType'][$item["listingInfo"]["listingType"]] ? : $item["listingInfo"]["listingType"];

        return $template->parse();
    }
}