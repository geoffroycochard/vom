<?php
namespace Vom\Vomapi\Model\Import;
use GuzzleHttp\Psr7\HttpFactory;
use Vom\Vomapi\Http\ImageRequester;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;

class Image extends Content 
{

    use ImageTrait;

    protected string $cType = 'image';
    
    public function postProcessData()
    {
        // Find files [sys_file_reference_from]
        $rows = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_file_reference_from')
            ->select(['*'], 'sys_file_reference_from', ['tablenames' => 'tt_content', 'uid_foreign' => $this->getUidOriginal()])
            ->fetchAllAssociative()
        ;

        $files = [];
        foreach ($rows as $l) {
            /** @var FileObject $newFile */
            $newFile = $this->handleImage($l);
            $files[] = $newFile;
            $this->setSubDatas('sys_file_reference', [
                'table_local' => 'sys_file',
                'uid_local' => $newFile->getUid(),
                'tablenames' => 'tt_content',
                'uid_foreign' => $this->originalData['hash'],
                'fieldname' => 'image',
                'pid' => $this->data['pid']
            ]);
        }
        //$this->data['image'] = count($files);
    }


}
