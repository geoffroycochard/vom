<?php

namespace Vom\Vomapi\Command;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class YamlGenerator extends Command
{


    private $csvFile = '/vom_api/Data/om/page.csv';

    protected function configure()
    {
        $this->setDescription(
            'Generate yaml data page from csv'
        );
        $this->setHelp(
            'Generate yaml data page from csv'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $csv = [];
        $mapping = [
            'key','lvl1', 'lvl2', 'lvl3', 'type', 'lvl', 'desc', 'uid', 'url'
        ];
        if (($handle = fopen(Environment::getExtensionsPath() . $this->csvFile, 'r')) !== FALSE) { // Check the resource is valid
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Check opening the file is OK!
                $a = [];
                foreach ($data as $key => $value) {
                    $a[$mapping[$key]] = $value;
                }
                $csv[] = $a;
            }
        }

        $key =  180;
        $pid = 155;
        $file = sprintf(
            '%s/vom_api/Data/om/content/%s.yaml',
            Environment::getExtensionsPath(),
            $key
        );

        $qb = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content_from')
        ;
        $rows = $qb
            ->select('uid')
            ->from('tt_content_from')
            ->where(
                $qb->expr()->eq('pid', $pid), 
                $qb->expr()->eq('dele   ted', 0)
            )
            ->orderBy('colPos')->addOrderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative()
        ;

        $a = [];
        $index = 0;
        foreach ($rows as $row ) {
            $index = $index + 10;
            $a[$index] = [
                'mode' => 'from',
                'uid' => $row['uid']
            ];
        }

        DebugUtility::debug(($file));


        $yaml = Yaml::dump($a);
        file_put_contents($file, $yaml);

        DebugUtility::debug($yaml);

        return 1;
    }
}
