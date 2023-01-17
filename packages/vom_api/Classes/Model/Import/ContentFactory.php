<?php
namespace Vom\Vomapi\Model\Import;

use function Symfony\Component\String\u;

final class ContentFactory 
{
    /**
     * Summary of cType
     * @var string
     */
    private string $cType;
    
    /**
     * Summary of type
     * @var string
     */
    private string $type;

    /**
     * Summary of __construct
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = u($type)->camel()->title()->toString();;
    }

    /**
     * Summary of getInstance
     * @param array $data
     * @return object
     */
    public function getInstance(array $data): Object
    {
        $className = 'Vom\\Vomapi\\Model\\Import\\' . $this->type;
        if (!class_exists($className)) {
            $className = 'Vom\\Vomapi\\Model\\Import\\Text';
        }
        return (new $className())->setData($data);
    }
    

}