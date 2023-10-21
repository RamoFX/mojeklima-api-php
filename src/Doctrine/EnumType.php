<?php



namespace App\Doctrine;

use BackedEnum;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;



// https://github.com/doctrine/orm/issues/9021#issuecomment-991799674
class EnumType extends Type {
  private string $class;

  /**
   * @throws Exception
   */
  public static function addEnumType(string $class): void {
    self::addType($class, self::class);
    self::getType($class)->class = $class;
  }

  public function getName(): string {
    return $this->class;
  }

  public function getSQLDeclaration(array $column, AbstractPlatform $platform): string {
    $class = $this->class;
    $values = array_map(static fn(BackedEnum $enum): string => $enum->value, $class::cases());
    $column['length'] = max(0, ...array_map('mb_strlen', $values));

    return $platform->getStringTypeDeclarationSQL($column);
  }

  public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string {
    return $value?->value;
  }

  public function convertToPHPValue($value, AbstractPlatform $platform): ?BackedEnum {
    $class = $this->class;

    return null === $value ? null : $class::from($value);
  }
}
