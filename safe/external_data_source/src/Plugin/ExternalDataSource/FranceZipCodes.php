<?php

namespace Drupal\external_data_source\Plugin\ExternalDataSource;

use Drupal\external_data_source\Plugin\ExternalDataSourceBase;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception as GuzzleException;

/**
 * Provides a 'France Zip codes' ExternalDataSource.
 *
 * @ExternalDataSource(
 *   id = "francezipcodes",
 *   name = @Translation("France Zip Codes"),
 *   description = @Translation("This Plugin will gather France Zip Codes list.")
 * )
 */
class FranceZipCodes extends ExternalDataSourceBase {

  /**
   *
   * @return string
   */
  public function getPluginId() {
    return 'francezipcodes';
  }

  /**
   *
   * @return string
   */
  public function getPluginDefinition() {
    return $this->t('This Plugin will gather France Zip Codes list.');
  }

  /**
   * SetRequest setting sent request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   */
  public function setRequest(Request $request) {
    $this->request = $request;
  }

  /**
   * GetResponse call WS to retrieve data.
   *
   * @return array
   */
  public function getResponse() {
    if ($this->request && !is_null($this->request->get('q'))) {
      $this->q = $this->request->get('q');
    }
    $data = [];
    $client = new Client();
    try {
      // Case it's an auto complete:
      if (!is_null($this->q)) {
        $response = $client->get('https://geo.api.gouv.fr/communes?nom=' . $this->q . '&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre');
      }
      else {
        $response = $client->get('https://geo.api.gouv.fr/communes?fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre');
      }
      $data = json_decode($response->getBody()->getContents());
    }
    catch (GuzzleException $e) {
      watchdog_exception('external_data_source', $e->getMessage());
    }
    return $this->formatResponse($data);
  }

  /**
   * FormatResponse.
   *
   * @param array $data
   *   Formatting data retrieved from ws to match [{"value":"","label":""},
   *   {"value":"", "label":""}] return array $collection retrieved suggestions.
   *
   * @return array $collection
   */
  public function formatResponse(array $data) {
    $collection = [];
    foreach ($data as $entry) {
      $collection[] = [
        'value' => (string) reset($entry->codesPostaux),
        'label' => $this->t($entry->nom),
      ];
    }
    return $collection;
  }

}
