<?php
declare(strict_types=1);

namespace Vom\Vomapi\Api;

use Nng\Nnrestapi\Api\AbstractApi;
use Nng\Nnrestapi\Annotations as Api;

/**
 * @Api\Endpoint()
 */
class Page extends AbstractApi {

   /**
    * @Api\Access("public")
    * @return array
    * 
    */
   public function getIndexAction($uid)
   {
      return [
         'great'=>'it works!',
         'uid' => $uid
      ];
   }

   
   /**
    * @Api\Access("public")
    * @return array
    * 
    */
    public function putIndexAction()
    {
       return $this->request->getBody();
    }
}