<?php
namespace Vom\Vomapi\Import;

use ReflectionProperty;
use Vom\Vomapi\Message\MessageTrait;
use Vom\Vomapi\Model\Import\Content;
use Vom\Vomapi\Import\DataHandler;
use Vom\Vomapi\Model\Import\ContentInterface;
use TYPO3\CMS\Core\DataHandling\DataHandler AS TypoDataHandler;
use Vom\Vomapi\Model\PageImportModel;

class PageDataHandler extends DataHandler implements DataHandlerInterface
{

    protected PageCollection $pages;

    public function __construct(
        private readonly TypoDataHandler $dataHandler,
    )
    {
        $this->pages = new PageCollection();
        parent::__construct($dataHandler);
    }

    public function addPage(PageImportModel $content): self
    {
        $this->pages->add($content);
        return $this;
    }

    public function getCollection()
    {
        return $this->pages;
    }

    public function process()
    {
        if($this->pages->count() === 0) {
            throw new \Exception('No pages to proccess', 1);
        }

        $data = $subDatas = [];
        foreach ($this->getCollection() as $page) {
            $data['pages'][$page->getHash()] = $page->forDataHandler();
        }
        //$data['pages'] = array_reverse($data['pages'], true);
        //dd($data);
        $this->callDataHandler($data, [], 'Inserted page');


    }
}
