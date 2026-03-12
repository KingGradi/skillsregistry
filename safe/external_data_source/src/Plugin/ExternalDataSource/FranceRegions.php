<?php

namespace Drupal\external_data_source\Plugin\ExternalDataSource;

use Drupal\external_data_source\Plugin\ExternalDataSourceBase;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception as GuzzleException;

/**
 * Provides a 'France Regions' ExternalDataSource.
 *
 * @ExternalDataSource(
 *   id = "franceregions",
 *   name = @Translation("France Regions"),
 *   description = @Translation("This Plugin will gather France regions list.")
 * )
 */
class FranceRegions extends ExternalDataSourceBase {

  /**
   * @inheritdoc
   */
  public function getPluginId() {
    return 'franceregions';
  }

  /**
   * @inheritdoc
   */
  public function getPluginDefinition() {
    return $this->t('This Plugin will gather France regions list.');
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
        $response = $client->get('https://geo.api.gouv.fr/regions?nom=' . $this->q . '&fields=nom,code');
      }
      else {
        $response = $client->get('https://geo.api.gouv.fr/regions?fields=nom,code');
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
        'value' => (string) $entry->nom,
        'label' => $this->t($entry->nom),
      ];
    }
    return $collection;
  }

}
