<?php



namespace App\Utilities {

  use App\GraphQL\DevelopmentOutputBuffer;



  class Translation {
    public static function get_preferred_language(): string {
      $supported_languages = ["cs", "en", "de"];
      $preferred_language = $_SERVER["HTTP_PREFERRED_LANGUAGE"];
      $accept_language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];

      if (isset($preferred_language) && in_array($preferred_language, $supported_languages)) {
        DevelopmentOutputBuffer::set('final-language-from', 'preferred-language');
        return $preferred_language;
      } else if (isset($accept_language)) {
        $accept_languages = explode(',', $accept_language);

        foreach ($accept_languages as $l) {
          $sl = substr($l, 0, 2);

          if (in_array($sl, $supported_languages)) {
            DevelopmentOutputBuffer::set('final-language-from', 'accept-language');
            return $sl;
          }
        }
      }

      DevelopmentOutputBuffer::set('final-language-from', 'fallback');
      return "cs";
    }



    public static function translate(array $translated_messages, string $language): string {
      if (array_key_exists($language, $translated_messages)) {
        return $translated_messages[$language];
      } else {
        return "$translated_messages[0] (no $language translation exist for this message, message provided has " . join(', ', array_keys($translated_messages)) . " languages)";
      }
    }
  }
}
