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

/**
 * Class Util
 *
 * Util Class
 *
 * @author   Sebastian Krah <exotelis@mailbox.org>
 */
class Util
{
    /**
     * Get authentication choices
     *
     * @return array
     */
    public function getAuthenticationChoices()
    {
        $arrChoices = array();
        $objNotifications = \Database::getInstance()->execute("SELECT id,title FROM tl_ebayapi_auth ORDER BY title");
        while ($objNotifications->next()) {
            $arrChoices[$objNotifications->id] = $objNotifications->title;
        }
        return $arrChoices;
    }

    /**
     * Gets array with global IDs
     * Find the list of global ids here: https://developer.ebay.com/DevZone/finding/CallRef/Enums/GlobalIdList.html
     *
     * return array
     */
    public function getGlobalIds() {
        return array(
            0 => "EBAY-US",
            2 => "EBAY-ENCA",
            3 => "EBAY-GB",
            15 => "EBAY-AU",
            16 => "EBAY-AT",
            23 => "EBAY-FRBE",
            71 => "EBAY-FR",
            77 => "EBAY-DE",
            100 => "EBAY-MOTOR",
            101 => "EBAY-IT",
            123 => "EBAY-NLBE",
            146 => "EBAY-NL",
            186 => "EBAY-ES",
            193 => "EBAY-CH",
            201 => "EBAY-HK",
            203 => "EBAY-IN",
            205 => "EBAY-IE",
            207 => "EBAY-MY",
            210 => "EBAY-FRCA",
            211 => "EBAY-PH",
            212 => "EBAY-PL",
            216 => "EBAY-SG"
        );
    }

    /**
     * Gets array with valid sortOrder
     * Find the list of valid sort orders here: https://developer.ebay.com/devzone/finding/callref/extra/fnditmsadvncd.rqst.srtordr.html
     *
     * return array
     */
    public function getSortOrder() {
        return array(
            "BidCountFewest",
            "BidCountMost",
            "CurrentPriceHighest",
            "EndTimeSoonest",
            "PricePlusShippingHighest",
            "PricePlusShippingLowest",
            "StartTimeNewest"
        );
    }

    /**
     * Inserts a new key/value before a specific the key in the array.
     *
     * @param array  $arrCurrent An array to insert in to.
     * @param String $arrKey     The key to insert before.
     * @param mixed  $arrNew     An value to insert.
     */
    public function arrayInsertBefore(&$arrCurrent, $arrKey, $arrNew) {
        if(!\is_array($arrCurrent)) {
            $arrCurrent = $arrNew;
            return;
        }

        if (!\array_key_exists($arrKey, $arrCurrent)) {
            return;
        }

        $index = 0;
        foreach ($arrCurrent as $key => $value) {
            if ($arrKey === $key) {
                break;
            }
            $index++;
        }

        \array_insert($arrCurrent, $index, $arrNew);
    }
}