<?php



namespace App\Utilities {

  class Email {
    private static function lines(string ...$lines): string {
      return join("\n", $lines);
    }



    public static function send(string $to, string $subject, string $message) {
      $headers = [
        "Content-Type" => "text/plain; charset=UTF-8",
        "From" => "MojeKlima <dvorro2@atlas.spsostrov.cz>",
        "Reply-To" => "MojeKlima <dvorro2@ms.spsostrov.cz>"
      ];

      mail($to, $subject, $message, $headers);
    }



    public static function sendPassword(string $email, string $password) {
      $language = Translation::getPreferredLanguage();

      $messages = [

        "cs" => self::lines(
          "Vážený uživateli,",
          "Moc Vám děkujeme za využití našich služeb! Níže naleznete Vaše heslo k účtu, které z bezpečnostních důvodů doporučujeme změnit v nastavení účtu:",
          $password,
          "V případě jakýchkoliv otázek nebo případných potíží neváhejte nás kontaktovat prostřednictvím emailu dvorro2@ms.spsostrov.cz.",
          "S přáním hezkého dne",
          "MojeKlima"
        ),

        "en" => self::lines(
          "Dear user,",
          "Thank you very much for using our services! Below you will find your account password, which for security reasons we recommend changing in the account settings:",
          $password,
          "In case of any questions or possible problems, do not hesitate to contact us via email dvorro2@ms.spsostrov.cz.",
          "Wishing you a nice day",
          "MojeKlima"
        ),

        "de" => self::lines(
          "Lieber Nutzer,",
          "Vielen Dank, dass Sie unsere Dienste nutzen! Nachfolgend finden Sie Ihr Kontopasswort, das wir aus Sicherheitsgründen in den Kontoeinstellungen zu ändern empfehlen:",
          $password,
          "Bei Fragen oder möglichen Problemen zögern Sie nicht, uns per E-Mail dvorro2@ms.spsostrov.cz zu kontaktieren.",
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

      self::send($email, $translatedSubject, $translatedMessage);
    }



    public static function sendNotification(string $email, string $locationName, string $alertMessage) {
      $message = self::lines(
        $alertMessage,
        "",
        "MojeKlima"
      );

      self::send($email, "$locationName, MojeKlima", $message);
    }
  }
}
