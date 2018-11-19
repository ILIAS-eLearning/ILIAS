<?php

declare(strict_types=1);

/**
 * A PostCondition does restrict the progression of a user through the learning sequence.
 * Thus, instead of saying "You may only _visit_ this object if you did this",
 * a PostCondition says "you may only _leave_ this object if you did this".
 *
 * LSPostConditions are being applied by the LearningSequenceConditionController.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSPostCondition
{
	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * @var int
	 */
	protected $type;

	/**
	 * @var mixed
	 */
	protected $value;

	public function __construct(
		int $ref_id,
		int $type,
		$value = null
	){
		$this->ref_id = $ref_id;
		$this->type = $type;
		$this->value = $value;
	}

	public function getRefId(): int
	{
		return $this->ref_id;
	}

	public function getConditionType(): int
	{
		return $this->type;
	}

	public function withConditionType(int $type): ilLSPostCondition
	{
		$clone = clone $this;
		$clone->type = $type;
		return $clone;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function withValue($value): ilLSPostCondition
	{
		$clone = clone $this;
		$clone->value = $value;
		return $clone;
	}
}
