<?php

namespace ILIAS\RuleEngine\Specification;

/**
 * Factory for Specification instances.
 *
 *
 * $specification = SpecificationFactory::andX(
 *     SpecificationFactory::is('institution', 'ACME'),
 *     SpecificationFactory::containes('email', '@acme.com')
 * );
 *
 * This is equivalent to `institution = "ACME" and strpos(email, '@acme.com') !== false
 *
 * @author Martin Studer ms@studer-raimann.ch
 */

abstract class SpecificationFactory implements Specification {

	/**
	 * @param $specifications[]
	 *
	 * @return AndXSpecification
	 */
	public static function andX($specifications)
    {
        return new AndXSpecification($specifications);
    }


	/**
	 * @param Specification $specifications
	 *
	 * @return orXSpecification
	 */
	public static function orX(Specification  $specifications): orXSpecification
	{
		return new orXSpecification($specifications);
	}


	/**
	 * @param Specification $specification
	 *
	 * @return NotSpecification
	 */
	public static function not(Specification $specification): NotSpecification
	{
		return new notSpecification($specification);
	}


	/**
	 * Check that a value equals another value.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return Specification
	 */
	public static function equals($key, $value) {
		return self::createSpec($key,$value,"=");
	}


	/**
	 * Check that a value is not equal to another value.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return Specification
	 */
	public static function notEquals($key, $value) {
		//TODO
	}


	/**
	 * Check that a value strictly equals another value.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return Specification
	 */
	public static function is($key, $value) {
		//TODO
	}


	/**
	 * Check that a value is NOT strictly equal to another value.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return Specification
	 */
	public static function isNot($key, $value) {
		//TODO
	}


	/**
	 * Check that a value is inferior to another value.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return Specification
	 */
	public static function lessThan($key, $value) {
		//TODO
	}


	/**
	 * Check that a value is inferior or equal to another value.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return Specification
	 */
	public static function lessThanEqual($key, $value) {
		//TODO
	}


	/**
	 * Check that a value is superior to another value.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return Specification
	 */
	public static function moreThan($key, $value) {
		//TODO
	}


	/**
	 * Check that a value is superior or equal to another value.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return Specification
	 */
	public static function moreThanEqual($key, $value) {
		//TODO
	}


	/**
	 * Check that a value is in a given list.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return Specification
	 */
	public static function in($key, $value) {
		//TODO
	}


	/**
	 * Check that a value is NOT in a given list.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return Specification
	 */
	public static function notIn($key, $value) {
		//TODO
	}

	/**
	 * @param $key
	 * @param $value
	 * @param $operator
	 *
	 * @return Specification
	 */
	private static function createSpec($key, $value, $operator)
	{
		return new GenericSpecification(sprintf('%s %s %s', $key, $operator,$value));
	}
}