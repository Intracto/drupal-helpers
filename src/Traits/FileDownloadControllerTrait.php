<?php

namespace Intracto\DrupalHelpers\Traits;

use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\file\FileInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Trait FileDownloadControllerTrait
 *
 * Provides a basic trait to return a download response for file entities.
 * Returns a response opening pdfs in new tab.
 * Returns a response downloading files behind file entities.
 *
 * @package Intracto\DrupalHelpers\Traits
 */
trait FileDownloadControllerTrait {

  /**
   * The http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $httpClient;

  /**
   * Sets a HTTP client.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   */
  public function setHttpClient(\GuzzleHttp\ClientInterface $httpClient): void {
    $this->httpClient = $httpClient;
  }

  /**
   * Downloads a file added to a file entity.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file entity.
   *
   * @return \Drupal\Core\Cache\CacheableRedirectResponse|\Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  protected function downloadFile(FileInterface $file) {
    $fileType = $file->getMimeType();
    $fileUrl = file_create_url($file->getFileUri());

    if ($fileType === 'application/pdf') {
      return new CacheableRedirectResponse($fileUrl);
    }

    // Redirect IOS users to the file. Safari mobile doesn't handle downloads.
    if ($this->isIosUser()) {
      $response = new Response($fileUrl, '302');
      $response->headers->set('location', $fileUrl);
      return $response;
    }

    try {
      $response = new Response($this->getFileContents($fileUrl));
      $disposition = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        basename($fileUrl)
      );
      $response->headers->set('Content-Disposition', $disposition);
      $response->headers->set('Content-Type', $file->getMimeType());
    }
    catch (\Exception $e) {
      return new Response();
    }

    return $response;
  }

  /**
   * Check if we are dealing with a safari user.
   *
   * @return bool
   *   Whether or not we are dealing with a safari user.
   */
  protected function isIosUser() {
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
      return FALSE;
    }

    $iOsUserAgents = [
      'iPod',
      'iPhone',
      'iPad',
    ];

    $searchString = implode('|', $iOsUserAgents);
    preg_match("/{$searchString}/", $_SERVER['HTTP_USER_AGENT'], $matches);
    $os = current($matches);
    return !empty($os);
  }

  /**
   * Returns the file contents.
   *
   * @param string $url
   *   The url of the file.
   *
   * @return string
   *   The response if found.
   */
  protected function getFileContents(string $url) : ?string {
    try {
      $response = $this->httpClient->get($url, [
        'verify' => FALSE,
      ]);

      return $response->getBody()->getContents();
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

}
