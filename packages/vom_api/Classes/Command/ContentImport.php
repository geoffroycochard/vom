<?php
namespace Vom\Vomapi\Command;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\Bootstrap;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Core\Environment;
use Vom\Vomapi\Model\Import\Content;
use Vom\Vomapi\Command\CallDataHandler;
use Vom\Vomapi\Import\ContentDataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Vom\Vomapi\Model\Import\ContentFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ContentImport extends Command
{
    /**
     * @var SymfonyStyle $io
     */
    private SymfonyStyle $io;

    private string $context;

    public function __construct(
        private readonly ContentDataHandler $contentDataHandler
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription(
            'Import content page from yaml'
        );
        $this->setHelp(
            'content page from yaml'
        );
        $this->addArgument('context', InputArgument::REQUIRED, 'Context ? [OM] / [VO]');
        $this->addArgument('key', InputArgument::OPTIONAL, 'pid / key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        Bootstrap::initializeBackendAuthentication();
        
        // Arguments
        $this->context = $input->getArgument('context');
        $key = $input->getArgument('key');
        
        $this->contentDataHandler->setMessenger($this->io);

        /**
         * Single case when key
         */
        if ($key) {
            $file = sprintf(
                '%s/vom_api/Data/%s/content/%s.yaml',
                Environment::getExtensionsPath(),
                $this->context,
                $key
            );

            if (!file_exists($file)) {
                $this->io->error(sprintf('No file with key %s', $key));
                return Command::FAILURE;
            }
            $content = Yaml::parse(file_get_contents($file));
            $this->getContents($content, $key);
            $this->contentDataHandler->process();

            return Command::SUCCESS;
        }


        /**
         * @var mixed $dir
         * Find all yaml to import
         */
        $dir = sprintf(
            '%s/vom_api/Data/%s/content/',
            Environment::getExtensionsPath(),
            $this->context
        );
        $finder = new Finder();
        $finder->files()->in($dir);

        if (!$finder->hasResults()) {
            return Command::SUCCESS;
        }

        $command = [];
        foreach ($finder as $file) {
            $content = Yaml::parse(file_get_contents($file->getRealPath()));
            if(!empty($content)) {
                $key = $file->getFilenameWithoutExtension();
                $content = $this->getContents($content, $key);
            }
        }
        $this->contentDataHandler->process();
        return Command::SUCCESS;

    }

    private function getContents(array $content, int $key): void
    {
        // Get page uid
        $row = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages')
            ->select(['uid'], 'pages', ['tx_vomapi_key' => $key, 'deleted' => 0])
            ->fetchOne()
        ;

        if (!is_int($row)) {
            throw new \Exception('No uid page found', 1);
        }
        $pid = $row;

        // Delete prev
        $rows = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content')
            ->delete('tt_content', ['pid' => $pid])
        ;

        $command = [];  
        foreach ($content as $k => $c) {
            $hash = substr('NEW'.hash('md5', $key.$k), 0, 10);
            
            // From mode
            if ($c['mode'] === 'from') {
                $row = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable('tt_content_from')
                    ->select(['*'], 'tt_content_from', ['uid' => $c['uid']])
                    ->fetchAssociative()
                ;
                $stucture = [
                    'pid' => $pid,
                    'CType' => $row['CType'],
                    'data' => $row
                ];
            } elseif ($c['mode'] == 'new') {
                $stucture = [
                    'pid' => $pid,
                    'CType' => $c['structure']['CType'],
                    'data' => $c['structure']
                ];
            } else {
                throw new \Exception('No mode', 1);
            }

            $stucture['hash'] = $hash;

            /**
             * @var Content $content
             */
            $content = (new ContentFactory($stucture['CType']))
                            ->getInstance($stucture);
            $content->setHash($hash);
            $this->contentDataHandler->addContent($content);

        }

    }


}
