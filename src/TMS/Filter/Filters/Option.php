<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Filters;

class Option extends Filter
{


	/**
	 * @var bool
	 */
	protected $checked;

	public function __construct(
		\ILIAS\TMS\Filter\FilterFactory $factory,
		$label,
		$description,
		array $mappings = array(),
		array $mapping_result_types = array(),
		$checked = false
	) {
		assert('is_string($label)');
		assert('is_string($description)');
		assert('is_bool($checked)');

		$this->setFactory($factory);
		$this->setLabel($label);
		$this->setDescription($description);
		$this->setMappings($mappings, $mapping_result_types);
		$this->setChecked($checked);
	}

	/**
	 * @inheritdocs
	 */
	public function original_content_type()
	{
		return $this->factory->type_factory()->bool();
	}

	/**
	 * @inheritdocs
	 */
	public function input_type()
	{
		return $this->original_content_type();
	}

	/**
	 * @inheritdocs
	 */
	protected function raw_content($input)
	{
		return $input;
	}

	/**
	 * @inheritdocs
	 */
	protected function clone_with_new_mappings($mappings, $mapping_result_types)
	{
		return new Option(
			$this->factory,
			$this->label(),
			$this->description(),
			$mappings,
			$mapping_result_types,
			$this->getChecked()
		);
	}

	public function clone_with_checked($checked)
	{
		$mappings = $this->getMappings();
		return new Option(
			$this->factory,
			$this->label(),
			$this->description(),
			$mappings[0],
			$mappings[1],
			$checked
		);
	}

	/**
	 * Set this filter checked by default.
	 *
	 * @param	bool	$bool
	 */
	protected function setChecked($checked)
	{
		$this->checked = $checked;
	}


	/**
	 * Get to know whether the checkbox is to be checked by default.
	 *
	 * @return	bool
	 */
	public function getChecked()
	{
		return $this->checked;
	}
}
