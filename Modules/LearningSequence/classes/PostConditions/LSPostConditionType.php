<?php

declare(strict_types=1);

/**
 * A type-definition for a PostCondition.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class LSPostConditionType
{
	/**
	 * @var int
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var bool
	 */
	protected $configurable;

	/**
	 * @var string[]
	 */
	protected $applicable_for;


	public function __construct(
		int $type,
		string $label,
		bool $configurable = false,
		array $applicable_for = []
	) {
		$this->type = $type;
		$this->label = $label;
		$this->configurable = $configurable;
		$this->applicable_for = $applicable_for;
	}

	public function getType(): int
	{
		return $this->type;
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	public function isConfigurable(): bool
	{
		return $this->configurable;
	}

	public function isApplicableFor(string $obj_type): array
	{
		return true;
	}
}
