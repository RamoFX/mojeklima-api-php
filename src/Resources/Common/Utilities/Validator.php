<?php



namespace App\Resources\Common\Utilities {

  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class Validator {
    public static function multiple(...$validator_results) {
      $last_key = array_key_last($validator_results);

      return $validator_results[$last_key];
    }



    /**
     * @throws GraphQLException
     */
    public static function maxLength(string $fieldName, string $fieldValue, int $limit): string {
      $fieldLength = strlen($fieldValue);

      if ($fieldLength > $limit) {
        $language = Translation::getPreferredLanguage();
        $messages = [
          "cs" => "Pole \"$fieldName\" nesmí být delší než $limit znaků, ale je dlouhé $fieldLength znaků",
          "en" => "The \"$fieldName\" field cannot be longer than $limit characters, but is $fieldLength characters long",
          "de" => "Das Feld \"$fieldName\" darf nicht länger als $limit Zeichen sein, ist aber 999 $fieldLength lang",
        ];
        $translatedMessage = Translation::translate($messages, $language);

        throw new GraphQLException($translatedMessage, 400);
      }

      return $fieldValue;
    }



    /**
     * @throws GraphQLException
     */
    public static function format(string $fieldName, string $fieldValue, int $formatId): string {
      if (filter_var($fieldValue, $formatId) === false) {
        $language = Translation::getPreferredLanguage();
        $messages = [
          "cs" => "Pole \"$fieldName\" má neplatný formát",
          "en" => "Field \"$fieldName\" has an invalid format",
          "de" => "Das Feld \"$fieldName\" hat ein ungültiges Format",
        ];
        $translatedMessage = Translation::translate($messages, $language);

        throw new GraphQLException($translatedMessage, 400);
      }

      return $fieldValue;
    }



    /**
     * @throws GraphQLException
     */
    public static function less(string $fieldName, float $fieldValue, float $limit): float {
      if ($fieldValue >= $limit) {
        $language = Translation::getPreferredLanguage();
        $messages = [
          "cs" => "Pole \"$fieldName\" by mělo být menší než $limit",
          "en" => "The \"$fieldName\" field should be less than the $limit",
          "de" => "Das Feld \"$fieldName\" sollte kleiner als $limit sein",
        ];
        $translatedMessage = Translation::translate($messages, $language);

        throw new GraphQLException($translatedMessage, 400);
      }

      return $fieldValue;
    }



    /**
     * @throws GraphQLException
     */
    public static function lessOrEqual(string $fieldName, float $fieldValue, float $limit): float {
      if ($fieldValue > $limit) {
        $language = Translation::getPreferredLanguage();
        $messages = [
          "cs" => "Pole \"$fieldName\" by mělo být menší než nebo rovno $limit",
          "en" => "The \"$fieldName\" field should be less than or equal to $limit",
          "de" => "Das Feld \"$fieldName\" sollte kleiner oder gleich $limit sein",
        ];
        $translatedMessage = Translation::translate($messages, $language);

        throw new GraphQLException($translatedMessage, 400);
      }

      return $fieldValue;
    }



    /**
     * @throws GraphQLException
     */
    public static function greaterOrEqual(string $fieldName, float $fieldValue, float $limit): float {
      if ($fieldValue < $limit) {
        $language = Translation::getPreferredLanguage();
        $messages = [
          "cs" => "Pole \"$fieldName\" by mělo být větší nebo rovno $limit",
          "en" => "The \"$fieldName\" field should be greater than or equal to $limit",
          "de" => "Das Feld \"$fieldName\" sollte größer oder gleich $limit sein",
        ];
        $translatedMessage = Translation::translate($messages, $language);

        throw new GraphQLException($translatedMessage, 400);
      }

      return $fieldValue;
    }



    /**
     * @throws GraphQLException
     */
    public static function greater(string $fieldName, float $fieldValue, float $limit): float {
      if ($fieldValue <= $limit) {
        $language = Translation::getPreferredLanguage();
        $messages = [
          "cs" => "Pole \"$fieldName\" by mělo být větší než $limit",
          "en" => "The \"$fieldName\" field should be greater than $limit",
          "de" => "Das Feld \"$fieldName\" sollte größer als $limit sein",
        ];
        $translatedMessage = Translation::translate($messages, $language);

        throw new GraphQLException($translatedMessage, 400);
      }

      return $fieldValue;
    }



    /**
     * @throws GraphQLException
     */
    public static function oneOf(string $fieldName, string $fieldValue, array $possibilities): string {
      if (!in_array($fieldValue, $possibilities)) {
        $language = Translation::getPreferredLanguage();
        $messages = [
          "cs" => "Pole \"$fieldName\" musí být jedno z: " . join(', ', $possibilities),
          "en" => "The \"$fieldName\" field must be one of: " . join(', ', $possibilities),
          "de" => "Das Feld \"$fieldName\" muss eines der folgenden sein: " . join(', ', $possibilities),
        ];
        $translatedMessage = Translation::translate($messages, $language);

        throw new GraphQLException($translatedMessage, 400);
      }

      return $fieldValue;
    }
  }
}
