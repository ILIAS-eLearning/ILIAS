<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\UI\Component\Tree\Node as INode;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements INode\Factory
{
	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;

	/**
	 * @param SignalGeneratorInterface $signal_generator
	 */
	public function __construct(SignalGeneratorInterface $signal_generator)
	{
		$this->signal_generator = $signal_generator;
	}

	/**
	 * @inheritdoc
	 */
	public function simple(string $label): INode\Simple
	{
		return new Simple($this->signal_generator, $label);
	}

}
