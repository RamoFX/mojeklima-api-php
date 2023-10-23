<?php



namespace App\Resources\Suggestion {

  use App\Resources\Common\Utilities\Translation;
  use Exception;
  use RestClient;
  use RestClientException;



  class SuggestionService {
    /**
     * @return SuggestionEntity[]
     * @throws RestClientException
     * @throws Exception
     */
    public function suggestions(string $query): array {
      $language = Translation::getPreferredLanguage();

      // rest api
      $api = $this->getRestClient();
      $apiKey = $_ENV["OPEN_CAGE_GEOCODING_API_KEY"];

      // "https://api.opencagedata.com/geocode/v1/json?key=2e20a10b90b64b8ab26b10257f49ef5f&no_annotations=1&language=$language&limit=5&q=$query";
      $result = $api->get("json", [
        'key' => $apiKey,
        'no_annotations' => 1,
        'limit' => 5,
        'language' => $language,
        'q' => $query
      ]);

      if ($result->error)
        throw new Exception($result->error);

      $data = $result->decode_response();

      $suggestions = [];

      if (!isset($data['total_results']) || $data['total_results'] === 0)
        return $suggestions;

      foreach ($data['results'] as $result) {
        $latitude = $result["geometry"]["lat"];
        $longitude = $result["geometry"]["lng"];
        $cityName = $result["components"]["city"];
        $countryName = $result["components"]["country"];

        $suggestion = new SuggestionEntity(
          $latitude,
          $longitude,
          $cityName,
          $countryName
        );

        $suggestions[] = $suggestion;
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
