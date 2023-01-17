<?php
declare(strict_types=1);

namespace Vom\Vomapi\Command;

use ReflectionProperty;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use Vom\Vomapi\Model\PageImportModel;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Backend\Tree\Repository\PageTreeRepository;
use TYPO3\CMS\Core\Utility\DebugUtility;
use Vom\Vomapi\Import\PageDataHandler;

class PageImport extends Command
{
    private $csvFile = '/vom_api/Data/%s/page.csv';

    private int $rootParent;

    private string $context;

    private SymfonyStyle $io;

    public function __construct(
        private readonly PageDataHandler $pageDataHandler
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            'Import data page from csv'
        );
        $this->setHelp(
            ' data page from csv'
        );

        $this->addArgument('context', InputArgument::REQUIRED, 'Context ? [OM] / [VO]');
        $this->addArgument('parentId', InputArgument::REQUIRED, 'Parent Id');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        Bootstrap::initializeBackendAuthentication();

        $this->pageDataHandler->setMessenger($this->io);

        $this->context = $input->getArgument('context');
        $this->rootParent = (int) $input->getArgument('parentId');
        
        /**
         * remove all pages under "Root" uid : 983
         */
        $output->writeln('Deleting all pages...');
        /** @var $pageTreeRepository PageTreeRepository */
        $pageTreeRepository = GeneralUtility::makeInstance(PageTreeRepository::class);
        $tree = $pageTreeRepository->getTree($this->rootParent);

        $command = [];
        foreach ($tree['_children'] as $key => $item) {
            $command['pages'][$item['uid']]['delete'] = 1;
        }
        $this->pageDataHandler->callDataHandler([], $command, 'All pages deleted', ['deleteTree' => true]);
        
        /**
         * Get date from csv
         */
        $csv = [];
        $mapping = [
            'fkey', 'key','lvl1', 'lvl2', 'lvl3', 'lvl4', 'publish', 'in_menu', 'typ', 'lvl', 'desc', 'edito', 'uid', 'url'
        ];
        if (($handle = fopen(Environment::getExtensionsPath() . sprintf($this->csvFile, $this->context), 'r')) !== FALSE) { // Check the resource is valid
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Check opening the file is OK!
                $a = [];
                foreach ($data as $key => $value) {
                    $a[$mapping[$key]] = $value;
                }
                $csv[] = $a;
            }
        }
        //dd($csv);
        $datas = [];
        //$csv = array_reverse($csv, true);
        $i = 0;
        foreach ($csv as $data) {
            if ($data['publish'] == 0) {
                continue;
            }

            $page = new PageImportModel($data);
            $page->setRootParent($this->rootParent);
            $this->generateYaml($this->context, $page->getKey(), (int)$data['uid']);
            $this->pageDataHandler->addPage($page);
        }   
        $this->pageDataHandler->process();

        // Replace shortcut
        // Get page uid
        $rows = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages')
            ->select(['uid', 'shortcut'], 'pages', ['doktype' => 4, 'deleted' => 0])
            ->fetchAllAssociative()
        ;

        $datas['pages'] = [];
        foreach ($rows as $s) {
            $uid = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('pages')
                ->select(['uid'], 'pages', ['tx_vomapi_key' => $s['shortcut'], 'deleted' => 0])
                ->fetchAssociative()
            ;
            dd($s);
            dd($uid);
            $datas['pages'][$s['uid']] = [
                'shortcut' => $uid['uid']
            ];
        }
        $this->pageDataHandler->callDataHandler($datas, [], 'Shortcut resolved :)');

        return Command::SUCCESS;
    }

    private function generateYaml(string $context, string $key, ?int $uid): bool
    {
        $file = sprintf(
            '%s/vom_api/Data/%s/content/%s.yaml',
            Environment::getExtensionsPath(),
            $context,
            $key
        );

        // If exist so exit ! #TODO : do not override ?
        if (file_exists($file)) {
            $this->io->comment(sprintf('File exist with key %s', $key));
            return false;
        }

        $a = [];

        // Come from ?
        if ($uid) {
            $qb = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tt_content_from')
            ;
            $rows = $qb
                ->select('uid')
                ->from('tt_content_from')
                ->where(
                    $qb->expr()->eq('pid', $uid), 
                    $qb->expr()->eq('deleted', 0)
                )
                ->orderBy('colPos')
                ->addOrderBy('sorting')
                ->executeQuery()
                ->fetchAllAssociative()
            ;
            $index = 0;
            foreach ($rows as $row ) {
                $index = $index + 10;
                $a[$index] = [
                    'mode' => 'from',
                    'uid' => $row['uid']
                ];
            }
        }
        $this->io->comment(sprintf('Generate yaml for key %s', $key));
        $yaml = Yaml::dump($a);

        file_put_contents($file, $yaml);

        return true;
    }

}