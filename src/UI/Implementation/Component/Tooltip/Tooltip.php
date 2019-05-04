<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Tooltip;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 *
 */
abstract class Tooltip implements Component\Tooltip\Tooltip
{
	use ComponentHelper;
	use JavaScriptBindable;

	/** @var Signal */
	protected $showSignal;

	/** @var SignalGeneratorInterface */
	protected $signalGenerator;

	/** @var string */
	private $position = self::POSITION_AUTO;

	/**
	 * @param SignalGeneratorInterface $signal_generator
	 */
	public function __construct(SignalGeneratorInterface $signal_generator)
	{
		$this->signalGenerator = $signal_generator;
		$this->initSignals();
	}


	/**
	 * @inheritdoc
	 */
	public function withResetSignals()
	{
		$clone = clone $this;
		$clone->initSignals();

		return $clone;
	}


	/**
	 * @inheritdoc
	 */
	public function getShowSignal(): Signal
	{
		return $this->showSignal;
	}

	/**
	 * Init any signals of this component
	 */
	protected function initSignals()
	{
		$this->showSignal = $this->signalGenerator->create();
	}

	/**
	 * @inheritdoc
	 */
	public function withTopPosition(): Component\Tooltip\Tooltip
	{
		$clone = clone $this;
		$clone->position = self::POSITION_TOP;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withRightPosition(): Component\Tooltip\Tooltip
	{
		$clone = clone $this;
		$clone->position = self::POSITION_RIGHT;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withLeftPosition(): Component\Tooltip\Tooltip
	{
		$clone = clone $this;
		$clone->position = self::POSITION_LEFT;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withBottomPosition(): Component\Tooltip\Tooltip
	{
		$clone = clone $this;
		$clone->position = self::POSITION_BOTTOM;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withAutomaticPosition(): Component\Tooltip\Tooltip
	{
		$clone = clone $this;
		$clone->position = self::POSITION_AUTO;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getPosition(): string
	{
		return $this->position;
	}
}