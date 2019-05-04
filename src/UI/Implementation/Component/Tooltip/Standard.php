<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Tooltip;

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component;

/**
 *
 */
class Standard extends Tooltip implements Component\Tooltip\Standard
{
	/**
	 * @var Component\Component[]
	 */
	protected $contents = [];

	/**
	 * @param Component\Component[] $contents
	 * @param SignalGeneratorInterface $signal_generator
	 */
	public function __construct(array $contents, SignalGeneratorInterface $signal_generator)
	{
		parent::__construct($signal_generator);
		$c = $this->toArray($contents);
		$types = [Component\Component::class];
		$this->checkArgListElements('contents', $c, $types);
		$this->contents = $contents;
	}

	/**
	 * @inheritdoc
	 */
	public function contents(): array
	{
		return $this->contents;
	}
}