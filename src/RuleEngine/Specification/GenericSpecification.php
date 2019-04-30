<?php
namespace ILIAS\RuleEngine\Specification;

/**
 * Class GenericSpecification
 *
 * @package ILIAS\RuleEngine\Specification
 */
class GenericSpecification implements Specification
{
	private $rule = '';
	private $parameters = [];
	public function __construct($rule, array $parameters = [])
	{
		$this->rule       = $rule;
		$this->parameters = $parameters;
	}
	/**
	 * @inheritDoc
	 */
	public function getRule() : string
	{
		return $this->rule;
	}
	/**
	 * @inheritDoc
	 */
	public function getParameters() : array
	{
		return $this->parameters;
	}
}