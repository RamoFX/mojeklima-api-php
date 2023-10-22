<?php



namespace App\Core {

  abstract class JsonDeserializable {
    /**
     * @param string $json
     * @return $this
     */
    public static function jsonDeserialize(string $json): static {
      $className = get_called_class();
      $classInstance = new $className();
      $json = json_decode($json);

      foreach ($json as $key => $value) {
        $keySetter = "set" . ucfirst($key);

        // check setter as preferred, only then directly assign if possible
        // setters are there for a reason and this should be respected
        if (method_exists($classInstance, $keySetter)) {
          $classInstance->{$keySetter}($value);
        } else if (property_exists($classInstance, $key)) {
          $classInstance->{$key} = $value;
        }
      }

      return $classInstance;
    }

    /**
     * @param string $json
     * @return $this[]
     */
    public static function jsonDeserializeArray(string $json): array {
      $json = json_decode($json);
      $items = [];
      foreach ($json as $item)
        $items[] = self::jsonDeserialize($item);

      return $items;
    }
  }
}
