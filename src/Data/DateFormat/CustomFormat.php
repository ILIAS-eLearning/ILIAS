<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\DateFormat;

/**
 * Defines a user-defined Date Format.
 */
class CustomFormat extends DateFormat
{
	public function __construct(FormatBuilder $builder) {
		$this->builder = $builder;
	}

	/**
	 * Get an instance of FormatBuilder;
	 * Finish the definition with FormatBuilder::apply() to
	 * get an instance of DateFormat.
	 */
	public function withFormat(): FormatBuilder
	{
		return $this->builder->withInstance($this);
	}

	/**
	 * Set the actual format (this is called by the FormatBuilder).
	 */
	public function applyFormat(FormatBuilder $builder): CustomFormat
	{
		$this->format = $builder->get();
		return $this;
	}
}
