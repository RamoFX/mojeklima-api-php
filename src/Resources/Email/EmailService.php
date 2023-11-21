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



    public function sendPasswordResetVerification(string $email, string $token): bool {
      $appName = $this->config->get('app.name');
      $replyToEmail = $this->config->get('email.replyTo');
      $origin = $this->config->get('app.origin');
      $verificationLink = "$origin/reset-password?token=$token";
      $message = Translation::translate([
        'cs' => <<<END
          Vážený uživateli,
          
          Obdrželi jsme žádost o obnovení hesla pro váš účet na webu $appName. Pro pokračování v obnově hesla, klikněte prosím na následující odkaz:
          
          $verificationLink
          
          Pokud jste nepožádali o obnovení hesla, prosím, ignorujte tento email.
          
          Pokud máte jakékoli potíže, kontaktujte prosím naši podporu na emailové adrese: $replyToEmail.
          
          Děkujeme,
          Tým $appName
          END,
        'en' => <<<END
          Dear user,
          
          We have received a request to reset the password for your account on the $appName website. To proceed with password recovery, please click on the following link:
          
          $verificationLink
          
          If you have not requested a password reset, please ignore this email.
          
          If you have any concerns or questions, feel free to contact us at $replyToEmail.
          
          Thank you,
          $appName Team
          END,
        'de' => <<<END
          Sehr geehrter Benutzer,
          
          Wir haben eine Anfrage zum Zurücksetzen des Passworts für Ihr Konto auf der $appName-Website erhalten. Um mit der Wiederherstellung des Passworts fortzufahren, klicken Sie bitte auf den folgenden Link:
          
          $verificationLink
          
          Wenn Sie kein Zurücksetzen des Passworts angefordert haben, ignorieren Sie bitte diese E-Mail.
          
          Wenn Sie Bedenken oder Fragen haben, können Sie uns gerne unter $replyToEmail kontaktieren.
          
          Vielen Dank,
          $appName-Team
          END
      ]);

      $subject = Translation::translate([
        'cs' => 'Obnova hesla',
        'en' => 'Password reset',
        'de' => 'Wiederherstellung von Passwörtern'
      ]);

      return $this->sendEmail($email, $subject, $message);
    }



    public function sendEmailVerification(string $email, string $token): bool {
      $appName = $this->config->get('app.name');
      $replyToEmail = $this->config->get('email.replyTo');
      $origin = $this->config->get('app.origin');
      $verificationLink = "$origin/verify-email?token=$token";
      $message = Translation::translate([
        'cs' => <<<END
          Vážený uživateli,
          
          Děkujeme za vytvoření účtu na webu $appName. Pro dokončení registračního procesu, klikněte prosím na následující odkaz pro ověření vaší emailové adresy:
          
          $verificationLink
          
          Pokud jste se na webu $appName neregistrovali, tento email můžete ignorovat.
          
          Pokud máte jakékoli potíže, kontaktujte prosím naši podporu na emailové adrese: $replyToEmail.
          
          Děkujeme,
          Tým $appName
          END,
        'en' => <<<END
          Dear User,
          
          Thank you for creating an account with $appName. To complete the registration process, please click on the link below to verify your email address:
          
          $verificationLink
          
          If you did not sign up for an account on $appName, please ignore this email.
          
          If you have any concerns or questions, feel free to contact us at $replyToEmail.
          
          Thank you,
          $appName Team
          END,
        'de' => <<<END
          Sehr geehrter Benutzer,
          
          Vielen Dank, dass Sie ein Konto bei $appName erstellt haben. Um den Registrierungsprozess abzuschließen, klicken Sie bitte auf den folgenden Link, um Ihre E-Mail-Adresse zu bestätigen:
          
          $verificationLink
          
          Wenn Sie sich nicht auf $appName registriert haben, ignorieren Sie bitte diese E-Mail.
          
          Wenn Sie Bedenken oder Fragen haben, können Sie uns gerne unter $replyToEmail kontaktieren.
          
          Vielen Dank,
          $appName Team
          END
      ]);

      $subject = Translation::translate([
        'cs' => 'Ověření e-mailu',
        'en' => 'Email verification',
        'de' => 'E-Mail-Verifizierung'
      ]);

      return $this->sendEmail($email, $subject, $message);
    }



    public function sendAccountMarkedAsRemovedNotification(string $email): bool {
      $appName = $this->config->get('app.name');
      $replyToEmail = $this->config->get('email.replyTo');
      // TODO: Finish messages
      $message = Translation::translate([
        'cs' => <<<END
          Vážený uživateli,
          
          ...
          
          Pokud máte jakékoli potíže, kontaktujte prosím naši podporu na emailové adrese: $replyToEmail.
          
          Děkujeme,
          Tým $appName
          END,
        'en' => <<<END
          Dear User,
          
          ...
          
          If you continue to have issues, please contact our support team at: $replyToEmail.
          
          Thank you,
          $appName Team
          END,
        'de' => <<<END
          Sehr geehrter Benutzer,
          
          ...
          
          Wenn Sie weiterhin Probleme haben, kontaktieren Sie bitte unser Support-Team unter: $replyToEmail.
          
          Vielen Dank,
          $appName Team
          END
      ]);

      $subject = Translation::translate([
        "cs" => 'Odstranění účtu',
        "en" => 'Account removal',
        "de" => 'Entfernen des Kontos'
      ]);

      return $this->sendEmail($email, $subject, $message);
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
      $apiKey = $this->config->get('keys.api.brevo');
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
  }
}
