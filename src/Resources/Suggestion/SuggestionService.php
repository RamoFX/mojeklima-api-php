<?php



namespace App\Resources\Suggestion {

  use App\Resources\Common\Utilities\ConfigManager;
  use App\Resources\Common\Utilities\Debug;
  use App\Resources\Common\Utilities\Translation;
  use App\Resources\Suggestion\DTO\SuggestionInput;
  use Exception;
  use RestClient;
  use RestClientException;



  class SuggestionService {
    public function __construct(
      protected ConfigManager $config
    ) {}



    /**
     * @return SuggestionEntity[]
     * @throws RestClientException
     * @throws Exception
     */
    public function suggestions(SuggestionInput $suggestion): array {
      $language = Translation::getPreferredLanguage();

      // rest api
      $api = $this->getRestClient();
      $apiKey = $this->config->get('keys.api.openCageGeocoding');

      // "https://api.opencagedata.com/geocode/v1/json?key=2e20a10b90b64b8ab26b10257f49ef5f&no_annotations=1&language=$language&limit=5&q=$query";
      $responseJson = $api->get("json", [
        'key' => $apiKey,
        'no_annotations' => 1,
        'limit' => 5,
        'language' => $language,
        'q' => $suggestion->query
      ]);

      if ($responseJson->error)
        throw new Exception($responseJson->error);

      $response = $responseJson->decode_response();

      Debug::set('response', $response);

      $suggestions = [];

      if (!isset($response['total_results']) || $response['total_results'] === 0)
        return $suggestions;

      foreach ($response['results'] as $responseJson) {
        $suggestions[] = new SuggestionEntity(
          $responseJson['geometry']['lat'],
          $responseJson['geometry']['lng'],
          $responseJson['components']['city'],
          $responseJson['components']['country']
        );
      }

      return $suggestions;
    }



    private function getRestClient(): RestClient {
      $api = new RestClient([
        'base_url' => "https://api.opencagedata.com/geocode/v1/"
      ]);

      $api->register_decoder('json', function($data) {
        return json_decode($data, true);
      });

      return $api;
    }
  }
}
