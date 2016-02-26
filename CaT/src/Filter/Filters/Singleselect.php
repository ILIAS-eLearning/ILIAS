<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Singleselect extends Multiselect {
	/**
	 * @inheritdocs
	 */
	public function original_content_type() {
		return $this->factory->type_factory()->string();
	}
}
