<?php

/**
 * Extension Manager/Repository config file for ext "vom_site_package_om".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'VoM Site Package Om',
    'description' => '',
    'category' => 'templates',
    'constraints' => [
        'depends' => [
            'bootstrap_package' => '12.0.0-12.9.99',
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'OrleansMetropole\\VomSitePackageOm\\' => 'Classes',
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Geoffroy Cochard',
    'author_email' => 'geoffroy.cochard@orleans-metropole.fr',
    'author_company' => 'Orléans Métropole',
    'version' => '1.0.0',
];
