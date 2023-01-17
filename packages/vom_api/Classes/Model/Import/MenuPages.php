<?php
namespace Vom\Vomapi\Model\Import;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class MenuPages extends Content 
{
    protected string $cType = 'menu_pages';

    public function postProcessData()
    {
        $pages = explode(',', $this->data['pages']);
        $uidPages = [];
        foreach ($pages as $page) {
            $uid = $page;
            $uid = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('pages')
                ->select(['uid'], 'pages', ['tx_vomapi_key' => $page])
                ->fetchOne()
            ;
            $uidPages[] = $uid;
        }

        $this->data['pages'] = implode(',', $uidPages);
    } 
}