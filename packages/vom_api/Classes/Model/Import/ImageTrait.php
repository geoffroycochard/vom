<?php
namespace Vom\Vomapi\Model\Import;

use Vom\Vomapi\Http\ImageRequester;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;

trait ImageTrait {

    public function handleImage(array $params)
    {
        // find sys_file
        $sff = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_file_from')
            ->select(['*'], 'sys_file_from', ['uid' => $params['uid_local']])
            ->fetchAssociative()
        ;

        // Storage instanciation
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        /** @var \TYPO3\CMS\Core\Resource\ResourceStorage $storage */
        $storage = $storageRepository->getDefaultStorage();

        // File exist ?
        if($storage->hasFile($sff['identifier'])) {
            return $storage->getFile($sff['identifier']);
            
        }

        // Retrieve file
        $client = GeneralUtility::makeInstance(ImageRequester::class, GeneralUtility::makeInstance(RequestFactory::class));
        $content = $client->request($sff['identifier']);

        // Create folder 
        $a = explode('/', $sff['identifier']);
        unset($a[0]);
        unset($a[count($a)]);
        $path = '/'.implode('/', $a);
        $rootFolder = $storage->getRootLevelFolder();
        if ($rootFolder->hasFolder($path)) {
            $pf = $storage->getFolder($path);
        } else {
            $pf = $storage->createFolder($path);
        }

        // Upload file
        return $storage->addFile(
            Environment::getVarPath().'/cache/data/temp.jpg',
            $pf,
            $sff['name']
        );
    }

}