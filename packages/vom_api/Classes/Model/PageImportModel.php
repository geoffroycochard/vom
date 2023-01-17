<?php
declare(strict_types=1);

namespace Vom\Vomapi\Model;

use phpDocumentor\Reflection\Types\Integer;
use TYPO3\CMS\Core\Utility\DebugUtility;

class PageImportModel
{

    private $data;

    private $level;

    private $title;

    private $hash;

    private $parentHash;

    private int $rootParent;

    public function __construct(array $data)
    {

        $this->data = $data;
        $this->setLevel();
        
    }

    public function getKey(): string
    {
        return $this->data['key'];
    }

    private function setLevel()
    {
        for ($i=1; $i < 4; $i++) { 
            if (empty($this->data['lvl'.$i])) {
                $this->level = ($i - 1);
                return;
            }
        }
        $this->level = 3;
    }

    public function getLevel(): int
    {
        return $this->level;
    }


    public function getParentHash(): string|int
    {
        if ($this->getLevel() == 1) {
            return $this->getRootParent();
        }

        $key = '';
        for ($i=1; $i < $this->getLevel(); $i++) { 
            if (!empty($this->data['lvl'.$i])) {
                $key .= $this->data['lvl'.$i];
            }
        }
        
        return substr('NEW'.hash('md5', $key), 0, 10);
    }
    
    public function getHash(): string
    {
        return substr('NEW'.hash('md5', $this->data['lvl1'].$this->data['lvl2'].$this->data['lvl3']), 0, 10);
    }

    public function getTitle()
    {
        return $this->data['lvl'.$this->getLevel()];
    }

    /**
     * DOKTYPE :
     * 1 : page
     * 2 :
     * 3 : externallink avec col[url] = url
     * 4 : shortcut avec col[shortcut] = page_id
     * 
     */
    public function getDoktype()
    {
        if (substr($this->data['url'], 0, 2) == '//') {
            return 3;
        }
        if (substr($this->data['url'], 0, 4) == 'page') {
            $a = explode('_', $this->data['url']);
            $this->data['url'] = $a[1];
            return 4;
        }
        return 1;
    }

    public function getUrl()
    {
        return $this->data['url'];
    } 

    public function inMenu()
    {
        return $this->data['in_menu'] == 0 ? 1 : 0;
    }

    public function setRootParent(int $id)
    {
        $this->rootParent = $id;
    }

    public function getRootParent()
    {
        return $this->rootParent;
    }

    public function forDataHandler()
    {
        return [
            'title' => $this->getTitle(),
            'doktype' => $this->getDoktype(),
            'hidden' => 0,
            'url' => $this->getUrl(),
            'shortcut' => $this->getUrl(),
            'nav_hide' => $this->inMenu(),
            'tx_vomapi_key' => $this->getKey(),
            'pid' => $this->getParentHash($this->getRootParent())
        ];
    }

}
