<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

/** 
 * Factory for wrapped ilTemplates.
 */
class ilTemplateWrapperFactory implements TemplateFactory {
	/**
 	 * @inheritdocs
	 */
	public function getTemplate($path, $purge_unfilled_vars, $purge_unused_blocks) {
		$tpl = new \ilTemplate($path, $purge_unfilled_vars, $purge_unused_blocks);
		return new ilTemplateWrapper($tpl);
	}
}
