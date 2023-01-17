<?php
namespace Vom\Vomapi\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use TYPO3\CMS\Core\Core\Environment;

class SQLImportFromDatabase extends Command 
{
    protected function configure()
    {
        $this->setDescription(
            'Import sql data from from database'
        );
        $this->setHelp(
            'Import sql data from from database'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $tables = [
            'sys_category', 'sys_category_record_mm', 'sys_collection', 'sys_collection_entries', 
            'sys_file', 'sys_filemounts', 'sys_file_collection', 'sys_file_metadata', 
            'sys_file_processedfile', 'sys_file_reference', 'sys_file_storage', 'tt_address', 'tt_content'
        ];

        // ------
        $io->info('Drop tables');
        $query = [];
        foreach ($tables as $table) {
            $query[] = sprintf('DROP TABLE %s_from', $table);
        }
        $this->execFromShellCommandline('mysql -u php81 --password=php81 typo3from -e "'.implode(';', $query).'"');

        // ------
        $io->info('Import tables');
        $file = sprintf(
            '%s/vom_api/Data/sql/from.sql',
            Environment::getExtensionsPath()
        );
        $this->execFromShellCommandline('mysql -u php81 --password=php81 typo3from < /var/www/typo3mig/local/public/typo3conf/ext/vom_api/Data/sql/from.sql');
        $io->success('imported');

        // ------
        $io->info('Query to rename tables');
        $query = [];
        foreach ($tables as $table) {
            $query[] = sprintf('ALTER TABLE %s RENAME TO %s_from', $table, $table);
        }
        $this->execFromShellCommandline('mysql -u php81 --password=php81 typo3from -e "'.implode(';', $query).'"');

        return Command::SUCCESS;
    }

    private function execFromShellCommandline(string $command): void
    {
        $process = Process::fromShellCommandline($command);
        $process->start();
        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                echo "\nDebug :".$data;
            } else { // $process::ERR === $type
                echo "\nErreur : ".$data;
            }
        }
    }
}
