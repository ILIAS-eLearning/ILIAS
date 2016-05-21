<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

/**
 * Interface for a factory that provides templates.
 */
interface TemplateFactory {
	/**
 	 * Get template instance.
	 *
	 * @param	string	$file_name
	 * @param	bool	$purge_unfilled_vars
	 * @param	bool	$purge_unused_blocks
	 * @throws	\InvalidArgumentException	if there is no such template
	 * @return	Template
	 */
	public function getTemplate($file_name, $purge_unfilled_vars, $purge_unused_blocks);
}
