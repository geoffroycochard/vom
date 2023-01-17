<?php

defined("TYPO3_MODE") or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    "Vom.Vomapi",
    "ExamplePlugin",
    "Example Plugin",
    "EXT:vomapi/ext_icon.svg"
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    "vomapi",
    "Configuration/TypoScript",
    "Vomapi"
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages',
    [
        'tx_vomapi_key' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:vomapi/Resources/Private/Language/locallang_db.xlf:pages.tx_vomapi_key',
            'config' => [
               'type' => 'input',
               'eval' => 'int',
               'items' => [
                  ['',0,],
                  ['LLL:EXT:examples/Resources/Private/Language/locallang_db.xlf:pages.tx_vomapi_key.I.0',1,],
                  ['LLL:EXT:examples/Resources/Private/Language/locallang_db.xlf:pages.tx_vomapi_key.I.1',2,],
                  ['LLL:EXT:examples/Resources/Private/Language/locallang_db.xlf:pages.tx_vomapi_key.I.2','--div--',],
                  ['LLL:EXT:examples/Resources/Private/Language/locallang_db.xlf:pages.tx_vomapi_key.I.3',3,],
               ],
               'size' => 1,
               'maxitems' => 1,
            ],
        ]
    ]
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'tx_vomapi_key'
 );