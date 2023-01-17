<?php
namespace Vom\Vomapi\Model\Import;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class VomSitePackageMenuPagesNews extends Content 
{
    protected string $cType = 'vom_site_package_menu_pages_news';

    public function postProcessData()
    {
        $pages = explode(',', $this->data['pages']);
        $uidPages = [];
        foreach ($pages as $page) {
            $uid = $page;
            $p = explode('_', $page);
            if ($p[0] === 'pages') {
                $uid = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable('pages')
                    ->select(['uid'], 'pages', ['tx_vomapi_key' => $p[1]])
                    ->fetchOne()
                ;
                $uid = 'pages_'.$uid;
            }
            $uidPages[] = $uid;
        }

        $this->data['pages'] = implode(',', $uidPages);
    } 
    
}
