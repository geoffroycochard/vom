<?php

namespace Vom\Vomapi\Model\Import;

use phpDocumentor\Reflection\Types\Boolean;
use ReflectionMethod;

abstract class Content implements ContentInterface
{
    /**
     * 
     */
    protected array $data;

    protected array $subDatas = [];

    protected array $originalData;

    protected string $cType;

    protected string $hash;

    private bool $new;

    private array $keys = [
        'CType', 'header',
        'bodytext', 'pages', 'layout', 
        'image', 'imagecols', 'imageorient'
    ];

    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data 
     * @return self
     */
    public function setData(array $data): self
    {
        $this->data = $this->originalData = $data;
        return $this;
    }

    public function hasSubDatas(): bool 
    {
        return !empty($this->getSubDatas());
    }

    public function getSubDatas(): array
    {
        return $this->subDatas;
    }

    public function setSubDatas($tableName, $data): self
    {
        # TODO : manage more clever !! HASH tableName + data
        $hash = 'NEW' . rand(1000, 9999);
        $this->subDatas[$tableName][$hash] = $data;
        return $this;
    }

    public function getPidOriginal()
    {
        return $this->originalData['data']['pid'];
    }
    public function getUidOriginal()
    {
        return $this->originalData['data']['uid'];
    }

    public function forDataHandler()
    {
        $this->processData();
        return $this->data;
    }

    protected function processData(): void
    {
        $r = [
            'pid' => $this->data['pid'],
            'CType' => $this->data['CType'],
        ];
        foreach ($this->keys as $key) {
            if (array_key_exists($key, $this->data['data'])) {
                $r[$key] = $this->data['data'][$key];
            }
        }

        // Force layout 
        if (array_key_exists('layout', $r)) {
            $r['layout'] = (int) $r['layout'];
        }

        $this->data = $r;

        if (method_exists($this, 'postProcessData')) {
            $method = new ReflectionMethod($this, 'postProcessData');
            $method->invoke($this);
        }
    }
}
