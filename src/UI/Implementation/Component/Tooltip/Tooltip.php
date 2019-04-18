<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Tooltip;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class Tooltip
 * @package ILIAS\UI\Implementation\Component\Tooltip
 * @author Niels Theen <ntheen@databay.de>
 * @author Coling Kiegel <kiegel@qualitus.de>
 * @author Michael Jansen <mjansen@databay.de>
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
	private $placement = self::PLACEMENT_TOP;

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
	public function withPlacementTop(): Component\Tooltip\Tooltip
	{
		$clone = clone $this;
		$clone->placement = self::PLACEMENT_TOP;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withPlacementRight(): Component\Tooltip\Tooltip
	{
		$clone = clone $this;
		$clone->placement = self::PLACEMENT_RIGHT;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withPlacementLeft(): Component\Tooltip\Tooltip
	{
		$clone = clone $this;
		$clone->placement = self::PLACEMENT_LEFT;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withPlacemenBottom(): Component\Tooltip\Tooltip
	{
		$clone = clone $this;
		$clone->placement = self::PLACEMENT_BOTTOM;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getPlacement(): string
	{
		return $this->placement;
	}
}