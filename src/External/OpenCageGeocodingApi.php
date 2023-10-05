<?php



namespace App\External {

  use App\Core\Entities\Suggestion;
  use RestClient;
  use Exception;



  class OpenCageGeocodingApi {
    /** @return Suggestion[] */
    public static function get_suggestions(string $language, string $query): array {
      $api = self::get_rest_client();
      $apiKey = self::get_api_key();

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
        $formatted = $result["formatted"];
        $countryCode = $result["components"]['ISO_3166-1_alpha-3']
          ?: null;
        $region = $result["components"]["region"]
          ?: $result["components"]["municipality"]
          ?: $result["components"]["county"]
          ?: $result["components"]["state"]
          ?: null;

        $suggestion = new Suggestion(
          $latitude,
          $longitude,
          $formatted,
          $countryCode,
          $region
        );

        $suggestions[] = $suggestion;
      }

      return $suggestions;
    }



    private static function get_rest_client(): RestClient {
      $api = new RestClient([
        'base_url' => "https://api.opencagedata.com/geocode/v1/"
      ]);

      $api->register_decoder('json', function($data) {
        return json_decode($data, TRUE);
      });

      return $api;
    }


    
    private static function get_api_key() {
      return $_ENV["OPEN_CAGE_GEOCODING_API_KEY"];
    }
  }
}
