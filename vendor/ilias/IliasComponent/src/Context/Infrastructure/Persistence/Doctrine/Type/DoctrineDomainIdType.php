<?php

namespace srag\IliasComponent\Context\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use LogicException;


/**
 * Class DoctrineDomainIdType
 *
 * @package srag\IliasComponent\Infrastructure\Persistence\Doctrine\Type
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class DoctrineDomainIdType extends Type {

	const NAME = "ilias_domain_id";


	/**
	 * @return string
	 */
	public function getName(): string {
		return static::NAME;
	}


	/**
	 * @var array[]
	 */
	protected static $mapping = [];


	/**
	 * @param string $class
	 */
	public final static function setClass(string $class): void {
		if (!is_subclass_of($class, DomainId::class)) {
			throw new LogicException("Domain ID class must be a sub class of " . DomainId::class . ", got " . $class . ".");
		}
		self::$mapping[static::class]["class"] = $class;
	}


	/**
	 * @return string
	 */
	public final static function getClass(): string {
		if (!isset(self::$mapping[static::class]["class"])) {
			throw new LogicException("No class set for type " . static::class . ".");
		}

		return self::$mapping[static::class]["class"];
	}


	/**
	 * @param string $type
	 */
	public final static function setDataType(string $type): void {
		self::$mapping[static::class]["data_type"] = $type;
	}


	/**
	 * @return string
	 */
	public final static function getDataType(): string {
		return self::$mapping[static::class]["data_type"] ?? Type::INTEGER;
	}


	/**
	 *
	 */
	public final static function resetMapping(): void {
		self::$mapping = [];
	}


	/**
	 * @param $value
	 *
	 * @return string|null
	 */
	public final static function resolveName($value): ?string {
		if ($value instanceof DomainId) {
			$class = get_class($value);
			/**
			 * @var string $type
			 */
			foreach (self::$mapping as $type => $mapping) {
				if ($class === $mapping["class"]) {
					return $type::NAME;
				}
			}

			return self::NAME;
		}

		return null;
	}


	/**
	 * @param mixed            $value
	 * @param AbstractPlatform $platform
	 *
	 * @return mixed
	 */
	public final static function resolveValue($value, AbstractPlatform $platform) {
		if ($value instanceof DomainId) {
			$class = get_class($value);
			$type = Type::INTEGER;
			foreach (self::$mapping as $mapping) {
				if ($class === $mapping["class"]) {
					$type = $mapping["data_type"] ?? $type;
					break;
				}
			}

			return self::getType($type)->convertToPHPValue($value->isEmpty() ? null : $value->toString(), $platform);
		}

		return $value;
	}


	/**
	 * @param array            $fieldDeclaration
	 * @param AbstractPlatform $platform
	 *
	 * @return string
	 */
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string {
		return static::getInnerType()->getSQLDeclaration($fieldDeclaration, $platform);
	}


	/**
	 * @param mixed            $value
	 * @param AbstractPlatform $platform
	 *
	 * @return mixed
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform) {
		if ($value instanceof DomainId) {
			$value = $value->isEmpty() ? null : $value->toString();
		}
		try {
			return static::getInnerType()->convertToDatabaseValue($value, $platform);
		} catch (ConversionException $e) {
			throw ConversionException::conversionFailed($value, $this->getName());
		}
	}


	/**
	 * @param mixed            $value
	 * @param AbstractPlatform $platform
	 *
	 * @return DomainId|null
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform): ?DomainId {
		try {
			$value = static::getInnerType()->convertToPHPValue($value, $platform);
		} catch (ConversionException $e) {
			throw ConversionException::conversionFailed($value, $this->getName());
		}

		return $value === null ? null : static::getClass()::fromValue($value);
	}


	/**
	 * @return Type
	 */
	protected final static function getInnerType(): Type {
		return self::getType(static::getDataType());
	}
}
