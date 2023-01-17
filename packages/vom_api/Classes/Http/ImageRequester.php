<?php
namespace Vom\Vomapi\Http;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\RequestFactory;

final class ImageRequester  
{
    private const API_URL = 'https://www.orleans-metropole.fr/fileadmin';

    // We need the RequestFactory for creating and sending a request,
    // so we inject it into the class using constructor injection.
    public function __construct(
        private readonly RequestFactory $requestFactory,
    ) {
    }

    /**
     * @throws \JsonException
     * @throws \RuntimeException
     */
    public function request(string $identifier): string
    {
        // Additional headers for this specific request
        // See: https://docs.guzzlephp.org/en/stable/request-options.html
        $additionalOptions = [
            'headers' => ['Cache-Control' => 'no-cache'],
            'allow_redirects' => false,
            'sink' => Environment::getVarPath().'/cache/data/temp.jpg'
        ];

        // Get a PSR-7-compliant response object
        $response = $this->requestFactory->request(
            self::API_URL.$identifier,
            'GET',
            $additionalOptions
        );

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                'Returned status code is ' . $response->getStatusCode()
            );
        }
        
        if ($response->getHeaderLine('Content-Type') !== 'image/jpeg') {
            throw new \RuntimeException(
                'The request did not return image/jpeg'
            );
        }
        // Get the content as a string on a successful request
        return $response->getBody()->getContents();
    }
}
