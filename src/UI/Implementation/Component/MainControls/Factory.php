<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\MainControls as IMainControls;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements IMainControls\Factory
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
	public function metabar(Image $logo): IMainControls\Metabar
	{
		return new Metabar($this->signal_generator, $logo);
	}

	/**
	 * @inheritdoc
	 */
	public function mainbar(): IMainControls\Mainbar
	{
		return new Mainbar($this->signal_generator);
	}

	/**
	 * @inheritdoc
	 */
	public function slate(): IMainControls\Slate\Factory
	{
		return new Slate\Factory($this->signal_generator);
	}

}
