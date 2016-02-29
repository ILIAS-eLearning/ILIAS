<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Singleselect extends SelectBase {

	/**
	 * Set or get the default choice of options for the multiselect.
	 *
	 * @param	int[]|string[]|null		$default_choice
	 * @return	Multiselect|string[]|int[]
	 */
	public function default_choice(array $default_choice = null) {
		if ($default_choice === null) {
			return $this->default_choice;
		}

		list($ms, $mrts) = $this->getMappings();
		return new Singleselect($this->factory, $this->label(), $this->description(),
						$this->options, $default_choice, $ms, $mrts);
	}

	/**
	 * @inheritdocs
	 */
	protected function clone_with_new_mappings($mappings, $mapping_result_types) {
		return new Singleselect($this->factory, $this->label(), $this->description(),
						$this->options, $this->default_choice, $mappings, $mapping_result_types);
	}
}
