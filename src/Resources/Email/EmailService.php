<?php



namespace App\Resources\Email {

  use App\Resources\Common\Utilities\ConfigManager;
  use App\Resources\Common\Utilities\Debug;
  use App\Resources\Common\Utilities\Translation;
  use RestClient;



  class EmailService {
    public function __construct(
      protected ConfigManager $config
    ) {}



    public function sendPassword(string $email, string $password): bool {
      $appName = $this->config->get('app.name');
      $replyToEmail = $this->config->get('email.replyTo');

      $message = Translation::translate([
        "cs" => self::lines(
          "Vážený uživateli,",
          "Moc Vám děkujeme za využití našich služeb! Níže naleznete Vaše heslo k účtu, které z bezpečnostních důvodů doporučujeme změnit v nastavení účtu:",
          $password,
          "V případě jakýchkoliv otázek nebo případných potíží neváhejte nás kontaktovat prostřednictvím emailu $replyToEmail.",
          "S přáním hezkého dne",
          "",
          $appName
        ),
        "en" => self::lines(
          "Dear user,",
          "Thank you very much for using our services! Below you will find your account password, which for security reasons we recommend changing in the account settings:",
          $password,
          "In case of any questions or possible problems, do not hesitate to contact us via email $replyToEmail.",
          "Wishing you a nice day",
          "",
          $appName
        ),
        "de" => self::lines(
          "Lieber Nutzer,",
          "Vielen Dank, dass Sie unsere Dienste nutzen! Nachfolgend finden Sie Ihr Kontopasswort, das wir aus Sicherheitsgründen in den Kontoeinstellungen zu ändern empfehlen:",
          $password,
          "Bei Fragen oder möglichen Problemen zögern Sie nicht, uns per E-Mail $replyToEmail zu kontaktieren.",
          "Einen schönen Tag noch",
          "",
          $appName
        )
      ]);

      $subject = Translation::translate([
        "cs" => "Přístupové heslo",
        "en" => "Access password",
        "de" => "Passwort",
      ]);

      return $this->sendEmail($email, $subject, $message);
    }



    // TODO: Abandon
    public function sendNotification(string $email, string $locationName, string $alertMessage): bool {
      $appName = $this->config->get('app.name');
      // TODO: Send content from users is dangerous, rather just tell that alert was triggered
      $message = self::lines(
        $alertMessage,
        "",
        $appName
      );

      return $this->sendEmail($email, $locationName, $message);
    }



    public function sendEmail(string $to, string $rawSubject, string $body): bool {
      $appName = $this->config->get('app.name');
      $senderEmail = $this->config->get('email.sender');
      $replyToEmail = $this->config->get('email.replyTo');
      $api = self::getRestClient();

      $body = [
        "sender" => [
          "name" => $appName,
          "email" => $senderEmail
        ],
        "to" => [
          [
            "email" => $to
          ]
        ],
        "replyTo" => [
          "email" => $replyToEmail
        ],
        "subject" => "$rawSubject, $appName",
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
      $apiKey = $this->config->get('keys.api.brevo');;

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
  }
}
