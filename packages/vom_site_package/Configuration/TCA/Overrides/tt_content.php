<?php

use TYPO3\CMS\Core\Utility\DebugUtility;

defined('TYPO3') or die('Access denied.');

call_user_func(function()
{

    // Adds the content element to the "Type" dropdown
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        [
            // title
            'Pages and news',
            // plugin signature: extkey_identifier
            'vom_site_package_menu_pages_news',
            // icon identifier
            'content-text',
        ],
        'menu_pages',
        'after'
    );

    //
    $TCAMenuPages = $GLOBALS['TCA']['tt_content']['types']['menu_pages'];

    $TCAMenuPages['columnsOverrides'] = [
        'pages' => [
            'config' => [
                'allowed' => 'pages, tx_news_domain_model_news'
            ]
        ]
    ];
    $GLOBALS['TCA']['tt_content']['types']['vom_site_package_menu_pages_news'] = $TCAMenuPages;

});
