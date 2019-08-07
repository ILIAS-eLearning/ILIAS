<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */

namespace ILIAS\Refinery;


use ILIAS\Data\Result;

trait DeriveInvokeFromTransform
{
	/**
	 * @param mixed $from
	 * @return mixed
	 * @throws \Exception
	 */
	abstract public function transform($from);

	/**
	 * @throws \InvalidArgumentException  if the argument could not be transformed
	 * @param  mixed  $from
	 * @return mixed
	 */
	public function __invoke($from) {
		return $this->transform($from);
	}
}
