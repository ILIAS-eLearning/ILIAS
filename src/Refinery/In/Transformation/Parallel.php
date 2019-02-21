<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\In\Transformation;


use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;

class Parallel implements Transformation
{
	/**
	 * @var Transformation[]
	 */
	private $transformationStrategies;

	/**
	 * @param array $transformations
	 * @throws \ilException
	 */
	public function __construct(array $transformations)
	{
		foreach ($transformations as $transformation) {
			if (!$transformation instanceof Transformation) {
				throw new \ilException(sprintf('The array MUST contain only "%s" instances', Transformation::class));
			}
		}
		$this->transformationStrategies = $transformations;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		$results = array();
		foreach ($this->transformationStrategies as $strategy) {
			$results[] = $strategy->transform($from);
		}

		return $results;
	}

	/**
	 * @inheritdoc
	 */
	public function applyTo(Result $data): Result
	{
		$results = array();
		foreach ($this->transformationStrategies as $strategy) {
			$results[] = $strategy->applyTo($data);
		}

		if (array() === $results) {
			$results[] = $data;
		}

		return $results;
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
