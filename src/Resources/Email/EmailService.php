<?php



namespace App\Resources\Email {

  use App\Resources\Common\Utilities\Debug;
  use App\Resources\Common\Utilities\Translation;
  use RestClient;



  class EmailService {
    public function sendEmail(string $to, string $subject, string $body): bool {
      $api = self::getRestClient();

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
        Debug::set("BrevoApi::sendEmail error", $result->error);

        return false;
      }

      return true;
    }



    private function getRestClient(): RestClient {
      $apiKey = $_ENV["BREVO_API_KEY"];

      $api = new RestClient([
        'base_url' => "https://api.brevo.com/v3/",
        "headers" => [
          "accept" => "application/json",
          "api-key" => $apiKey
        ]
      ]);

      $api->register_decoder('json', function($data) {
        return json_decode($data, true);
      });

      return $api;
    }



    private function lines(string ...$lines): string {
      return join("\n", $lines);
    }

    public function sendPassword(string $email, string $password): bool {
      $language = Translation::getPreferredLanguage();
      $contactEmail = "roman.dvorovoy123@gmail.com";

      $messages = [

        "cs" => self::lines(
          "Vážený uživateli,",
          "Moc Vám děkujeme za využití našich služeb! Níže naleznete Vaše heslo k účtu, které z bezpečnostních důvodů doporučujeme změnit v nastavení účtu:",
          $password,
          "V případě jakýchkoliv otázek nebo případných potíží neváhejte nás kontaktovat prostřednictvím emailu $contactEmail.",
          "S přáním hezkého dne",
          "MojeKlima"
        ),

        "en" => self::lines(
          "Dear user,",
          "Thank you very much for using our services! Below you will find your account password, which for security reasons we recommend changing in the account settings:",
          $password,
          "In case of any questions or possible problems, do not hesitate to contact us via email $contactEmail.",
          "Wishing you a nice day",
          "MojeKlima"
        ),

        "de" => self::lines(
          "Lieber Nutzer,",
          "Vielen Dank, dass Sie unsere Dienste nutzen! Nachfolgend finden Sie Ihr Kontopasswort, das wir aus Sicherheitsgründen in den Kontoeinstellungen zu ändern empfehlen:",
          $password,
          "Bei Fragen oder möglichen Problemen zögern Sie nicht, uns per E-Mail $contactEmail zu kontaktieren.",
          "Einen schönen Tag noch",
          "MojeKlima"
        ),

      ];
      $translatedMessage = Translation::translate($messages, $language);

      $subjects = [
        "cs" => "Přístupové heslo, MojeKlima",
        "en" => "Access password, MojeKlima",
        "de" => "Passwort, MojeKlima",
      ];
      $translatedSubject = Translation::translate($subjects, $language);

      return $this->sendEmail($email, $translatedSubject, $translatedMessage);
    }

    public function sendNotification(string $email, string $locationName, string $alertMessage): bool {
      $message = self::lines(
        $alertMessage,
        "",
        "MojeKlima"
      );

      return $this->sendEmail($email, "$locationName, MojeKlima", $message);
    }
  }
}
