<?php



namespace App\Utilities {

    use App\External\BrevoApi;



  class Email {
    private static function lines(string ...$lines): string {
      return join("\n", $lines);
    }



    public static function send_password(string $email, string $password): bool {
      $language = Translation::get_preferred_language();
      $contact_email = "roman.dvorovoy123@gmail.com";

      $messages = [

        "cs" => self::lines(
          "Vážený uživateli,",
          "Moc Vám děkujeme za využití našich služeb! Níže naleznete Vaše heslo k účtu, které z bezpečnostních důvodů doporučujeme změnit v nastavení účtu:",
          $password,
          "V případě jakýchkoliv otázek nebo případných potíží neváhejte nás kontaktovat prostřednictvím emailu $contact_email.",
          "S přáním hezkého dne",
          "MojeKlima"
        ),

        "en" => self::lines(
          "Dear user,",
          "Thank you very much for using our services! Below you will find your account password, which for security reasons we recommend changing in the account settings:",
          $password,
          "In case of any questions or possible problems, do not hesitate to contact us via email $contact_email.",
          "Wishing you a nice day",
          "MojeKlima"
        ),

        "de" => self::lines(
          "Lieber Nutzer,",
          "Vielen Dank, dass Sie unsere Dienste nutzen! Nachfolgend finden Sie Ihr Kontopasswort, das wir aus Sicherheitsgründen in den Kontoeinstellungen zu ändern empfehlen:",
          $password,
          "Bei Fragen oder möglichen Problemen zögern Sie nicht, uns per E-Mail $contact_email zu kontaktieren.",
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

      return BrevoApi::send_email($email, $translatedSubject, $translatedMessage);
    }



    public static function send_notification(string $email, string $locationName, string $alertMessage): bool {
      $message = self::lines(
        $alertMessage,
        "",
        "MojeKlima"
      );

      return BrevoApi::send_email($email, "$locationName, MojeKlima", $message);
    }
  }
}
