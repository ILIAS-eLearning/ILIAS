<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * Mails are based on templates with placeholders.
 * Those placeholders will be susbstituted by actual values.
 * It's the Context's job to deliver those values.
 * Naturally, there will be several implementations over this,
 * since some values are taken from e.g. the user, while others
 * originate from the course.
 *
 */
interface MailContext {

	/**
	 * Get a value for a placeholder.
	 * Some resolvements require more than one context - all contexts of
	 * content-builder are relayed to valueFor.
	 *
	 * @param string 	$placeholder_id
	 * @param MailContext[] 	$contexts
	 * @return string|null
	 */
	public function valueFor($placeholder_id, $contexts = array());

	/**
	 * Returns the list of placeholder_ids this context provides.
	 *
	 * @return string[]
	 */
	public function placeholderIds();

}
