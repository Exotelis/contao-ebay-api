<?php

/**
 * This file is part of exotelis/contao-ebay-api
 *
 * Copyright (c) 2019 Sebastian Krah
 *
 * @package   exotelis/contao-ebay-api
 * @author    Sebastian Krah <exotelis@mailbox.org>
 * @copyright 2019 Sebastian Krah
 * @license   https://github.com/Exotelis/contao-ebay-api/blob/master/LICENSE LGPL-3.0
 */

declare(strict_types=1);

// Backend Module
array_insert($GLOBALS['BE_MOD'], 1, array
(
    'ebayapi' => array
    (
        'auth' => array
        (
            'tables' => array('tl_ebayapi_auth')
        )
    )
));

// Frontend Modules
(new Exotelis\EbayApi\Util())->arrayInsertBefore($GLOBALS['FE_MOD'],'miscellaneous', array
(
    'ebayapi' => array
    (
        'selleritemlist' => 'Exotelis\EbayApi\ModuleSellerItemList'
    )
));

// Stylesheet
if (defined('TL_MODE') && TL_MODE == 'BE')
{

    $GLOBALS['TL_CSS'][] = 'bundles/exotelisebayapi/ebayapi.css|static';
}