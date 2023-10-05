<?php



namespace App\External {

  use App\GraphQL\DevelopmentOutputBuffer;
  use RestClient;



  class BrevoApi {
    public static function send_email(string $to, string $subject, string $body): bool {
      $api = self::get_rest_client();

      $body = [
        "sender" => [
          "name" => "MojeKlima",
          "email" => "mojeklima@ramofx.dev"
        ],
        "to" => [
          [
            "email" => $to
          ]
        ],
        "subject" => $subject,
        "textContent" => $body
      ];

      $result = $api->post("smtp/email", json_encode($body));

      if ($result->error) {
        DevelopmentOutputBuffer::set("BrevoApi::sendEmail error", $result->error);
        return false;
      }

      return true;
    }



    private static function get_rest_client(): RestClient {
      $api_key = self::get_api_key();

      $api = new RestClient([
        'base_url' => "https://api.brevo.com/v3/",
        "headers" => [
          "accept" => "application/json",
          "api-key" => $api_key
        ]
      ]);

      $api->register_decoder('json', function($data) {
        return json_decode($data, TRUE);
      });

      return $api;
    }



    private static function get_api_key() {
      return $_ENV["BREVO_API_KEY"];
    }
  }
}
