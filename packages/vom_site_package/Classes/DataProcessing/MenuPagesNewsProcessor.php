<?php

namespace OrleansMetropole\VomSitePackage\DataProcessing;

use GeorgRinger\News\Domain\Repository\NewsRepository;
use phpDocumentor\Reflection\Types\Boolean;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class MenuPagesNewsProcessor  implements DataProcessorInterface
{

    /**
     * 
     */
    private $models = [
        'pages' => PageRepository::class, 
        'tx_news_domain_model_news' => NewsRepository::class
    ];

    public function __construct()
    {
        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class)->setCreateAbsoluteUri(true)->reset();
    }

    /**
     * Process data of a record to resolve File objects to the view
     *
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array
    {

        $items = explode(',', $processedData['data']['pages']);

        if (empty($items)) {
            return $processedData;
        }
        
        $datas = [];
        foreach ($items as $item) {
            preg_match('/(pages|tx_news_domain_model_news)_(\d+)/', $item, $out);
            $model = (string) $out[1];
            $uid = (int) $out[2];
            if (!$this->validateModel($model)) {
                throw new \Exception(sprintf('Model %s is not supported', $model), 1);
            }

            $menu = [
                'uid' => null,
                'title' => null,
                'link' => null,
                'image' => null,
                'excerpt' => null,
                'date' => null,
                'type' => null,
                'data' => null
            ];
            switch ($model) {
                case 'pages':
                    $repository = GeneralUtility::makeInstance(PageRepository::class);
                    $obj = $repository->getPage($uid);
                    $menu['data'] = $obj;
                    $menu['uid'] = $uid;
                    $menu['title'] = $obj['nav_title'] ? $obj['nav_title'] : $obj['title'];
                    $menu['link'] = $this->uriBuilder->setTargetPageUid($uid)->build();
                    $menu['type'] = 'page';

                    break;

                case 'tx_news_domain_model_news':
                        $repository = GeneralUtility::makeInstance(NewsRepository::class);
                        /** GeorgRinger\News\Domain\Model\NewsDefault $obj */
                        $obj = $repository->findByUid($uid);
                        $menu['data'] = $obj;
                        $menu['uid'] = $uid;
                        $uri = $this->uriBuilder
                            ->reset()
                            ->setTargetPageUid(123)
                            // tx_news_pi1:{controller:'News', action:'detail', news:'{slide.element.0.uid}'}
                            ->uriFor(
                                'detail', ['news' => $uid], 'News', 'tx_news', 'tx_news_pi1'
                            )
                        ;
                        $menu['title'] = $obj->getTitle();
                        $menu['link'] = $uri;
                        $menu['type'] = 'tx_news_domain_model_news';
                        $menu['date'] = $obj->getDatetime();
                        $menu['excerpt'] = $obj->getTeaser();
                        break;
                
                default:
                    # code...
                    break;
            }
            $datas[] = $menu;
            
        }

        $processedData['menu'] = $datas;

        return $processedData;
    }

    private function validateModel(string $model): bool
    {
        return in_array($model, array_keys($this->models));
    }
}
