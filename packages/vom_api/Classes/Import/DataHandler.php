<?php
namespace Vom\Vomapi\Import;

use ReflectionProperty;
use Vom\Vomapi\Message\MessageTrait;
use TYPO3\CMS\Core\DataHandling\DataHandler AS TypoDataHandler;

class DataHandler 
{
    use MessageTrait;

    public function __construct(
        private readonly TypoDataHandler $dataHandler,
    ) {}

    public function callDataHandler(?array $data, ?array $command, string $message, ?array $flags = []): void
    {
        // Get an instance of the DataHandler and process the data
        /** @var DataHandler $dataHandler */
        // Manage specific flag
        foreach ($flags as $flag => $value) {
            if (property_exists($this->dataHandler, $flag)) {
                $reflectionProperty = new ReflectionProperty(get_class($this->dataHandler), $flag);
                $reflectionProperty->setValue($this->dataHandler, $value);
                $this->setSuccess(sprintf('Set %s specific flag to %s for dataHandler', $flag, $value));
            }
        }
        $this->dataHandler->start($data, $command);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();

        // Error or success reporting   
        if (count($this->dataHandler->errorLog) === 0) {
            // Handle success
            $this->setSuccess($message);
        } else {
            // Handle errors
            if (!empty($this->dataHandler->errorLog)) {
                $this->setError('Error(s) while creating content element');
                foreach ($this->dataHandler->errorLog as $log) {
                    // handle error e.g. in a log
                    $this->setError($log);
                }
            }
        }
        //BackendUtility::setUpdateSignal('updatePageTree');
        $this->dataHandler->clear_cacheCmd('pages');
    }
}
