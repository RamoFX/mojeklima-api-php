<?php



namespace App\Resources\Common\Utilities {



  class Translation {
    protected const DEFAULT_LANGUAGE = 'en';
    protected const SUPPORTED_LANGUAGES = [ 'en', 'cs', 'de' ];



    public static function getPreferredLanguage(): string {
      $preferredLanguage = $_SERVER['HTTP_PREFERRED_LANGUAGE'] ?? null;
      $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;

      if ($preferredLanguage !== null && in_array($preferredLanguage, self::SUPPORTED_LANGUAGES))
        return $preferredLanguage;

      if ($acceptLanguage === null)
        return self::DEFAULT_LANGUAGE;

      $acceptLanguages = explode(',', $acceptLanguage);

      foreach ($acceptLanguages as $language) {
        $languageTag = substr($language, 0, 2);

        if (in_array($languageTag, self::SUPPORTED_LANGUAGES))
          return $languageTag;
      }

      return self::DEFAULT_LANGUAGE;
    }



    public static function translate(array $translatedMessages): string {
      $language = self::getPreferredLanguage();

      if (array_key_exists($language, $translatedMessages))
        return $translatedMessages[$language];

      $availableTranslations = join(', ', array_keys($translatedMessages));
      $defaultTranslation = $translatedMessages[self::DEFAULT_LANGUAGE] ?? reset($translatedMessages);
      $note = "Please note, that current message wasn't translated (requested: $language, supported: $availableTranslations";

      return "$defaultTranslation $note";
    }
  }
}
