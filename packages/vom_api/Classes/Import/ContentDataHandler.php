<?php
namespace Vom\Vomapi\Import;

use ReflectionProperty;
use Vom\Vomapi\Message\MessageTrait;
use Vom\Vomapi\Model\Import\Content;
use Vom\Vomapi\Import\DataHandler;
use Vom\Vomapi\Model\Import\ContentInterface;

class ContentDataHandler extends DataHandler implements DataHandlerInterface
{

    protected ContentCollection $contents;

    public function __construct()
    {
        $this->contents = new ContentCollection();
    }

    public function addContent(ContentInterface $content): self
    {
        $this->contents->add($content);
        return $this;
    }

    public function getCollection()
    {
        return $this->contents;
    }

    public function process()
    {
        if($this->contents->count() === 0) {
            throw new \Exception('Not a Content to proccess', 1);
        }

        $data = $subDatas = [];
        /** @var Content $content */
        foreach ($this->getCollection() as $content) {
            $data['tt_content'][$content->getHash()] = $content->forDataHandler();
            if ($content->hasSubDatas()) {
                $subDatas[] = $content->getSubDatas();
            }
        }
        $data['tt_content'] = array_reverse($data['tt_content'], true);

        $this->callDataHandler($data, [], 'Inserted Collection');
        
        $data = [];
        foreach ($subDatas as $datas) {
            foreach ($datas as $tableName => $d) {
                foreach ($d as $hash => $values) {
                    $result = array_map(function($value) {
                        if (substr($value, 0,3) === 'NEW') {
                            return $this->dataHandler->substNEWwithIDs[$value];
                        }
                        return $value;
                    },$values);
                }
            }
            $data[$tableName][$hash] = $result;
        }

        $this->callDataHandler($data, [], 'Inserted subdata');

    }
}
