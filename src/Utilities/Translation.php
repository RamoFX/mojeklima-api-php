<?php



namespace App\Utilities {

  use App\GraphQL\DevelopmentOutputBuffer;



  class Translation {
    public static function getPreferredLanguage(): string {
      $supportedLanguages = ["cs", "en", "de"];
      $preferredLanguage = $_SERVER["HTTP_PREFERRED_LANGUAGE"];
      $acceptLanguage = $_SERVER["HTTP_ACCEPT_LANGUAGE"];

      if (isset($preferredLanguage) && in_array($preferredLanguage, $supportedLanguages)) {
        DevelopmentOutputBuffer::set('final-language-from', 'preferred-language');
        return $preferredLanguage;
      } else if (isset($acceptLanguage)) {
        $acceptLanguages = explode(',', $acceptLanguage);

        foreach ($acceptLanguages as $l) {
          $sl = substr($l, 0, 2);

          if (in_array($sl, $supportedLanguages)) {
            DevelopmentOutputBuffer::set('final-language-from', 'accept-language');
            return $sl;
          }
        }
      }

      DevelopmentOutputBuffer::set('final-language-from', 'fallback');
      return "cs";
    }



    public static function translate(array $translatedMessages, string $language): string {
      if (array_key_exists($language, $translatedMessages)) {
        return $translatedMessages[$language];
      } else {
        return "$translatedMessages[0] (no $language translation exist for this message, message provided has " . join(', ', array_keys($translatedMessages)) . " languages)";
      }
    }
  }
}
