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

// Add palettes to tl_module
$GLOBALS['TL_DCA']['tl_module']['palettes']['selleritemlist'] = '{title_legend},name,headline,type;{config_legend},ebayapi_appId,ebayapi_globalId,ebayapi_seller,ebayapi_itemsPerPage,ebayapi_sortOrder;{template_legend:hide},ebayapi_template,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

// Add fields to tl_module
$GLOBALS['TL_DCA']['tl_module']['fields']['ebayapi_appId'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['ebayapi_appId'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('Exotelis\EbayApi\Util', 'getAuthenticationChoices'),
    'eval'                      => array('mandatory'=>true, 'includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['ebayapi_globalId'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['ebayapi_globalId'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('Exotelis\EbayApi\Util', 'getGlobalIds'),
    'eval'                      => array('mandatory'=>true, 'includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "int(10) NOT NULL default '-1'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['ebayapi_seller'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['ebayapi_seller'],
    'exclude'                   => true,
    'inputType'                 => 'text',
    'eval'                      => array('mandatory'=>true, 'maxlength'=>64, 'tl_class'=>'w50'),
    'sql'                       => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['ebayapi_itemsPerPage'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['ebayapi_itemsPerPage'],
    'default'                 => 10,
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('rgxp'=>'natural', 'minval'=>1, 'maxval'=>100, 'tl_class'=>'w50 clr'),
    'sql'                     => "smallint(5) unsigned NOT NULL default '10'"
);

// Find the list of sort orders here: https://developer.ebay.com/DevZone/finding/CallRef/extra/fnditmsadvncd.rqst.srtordr.html
$GLOBALS['TL_DCA']['tl_module']['fields']['ebayapi_sortOrder'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['ebayapi_sortOrder'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('Exotelis\EbayApi\Util', 'getSortOrder'),
    'reference'                 => &$GLOBALS['TL_LANG']['tl_module'],
    'eval'                      => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "varchar(20) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['ebayapi_template'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['ebayapi_template'],
    'default'                 => 'ebayapi_itemlist_default',
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('tl_module_exotelis_ebayapi', 'getItemlistTemplates'),
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(64) NOT NULL default ''"
);

class tl_module_exotelis_ebayapi extends Contao\Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('Contao\BackendUser', 'User');
    }

    /**
     * Gets the wildcard for the itemlist templates
     *
     * @return array
     */
    public function getItemlistTemplates() {
        return $this->getTemplateGroup('ebayapi_selleritemlist_');
    }
}