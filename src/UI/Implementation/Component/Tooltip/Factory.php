<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Tooltip;

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 *
 */
class Factory implements \ILIAS\UI\Component\Tooltip\Factory
{
	/** @var SignalGeneratorInterface */
	protected $signalGenerator;

	/**
	 * @param SignalGeneratorInterface $signalGenerator
	 */
	public function __construct(SignalGeneratorInterface $signalGenerator)
	{
		$this->signalGenerator = $signalGenerator;
	}

	/**
	 * @inheritdoc
	 */
	public function standard(array $contents): \ILIAS\UI\Component\Tooltip\Standard
	{
		return new Standard($contents, $this->signalGenerator);
	}
}