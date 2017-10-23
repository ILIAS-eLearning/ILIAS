<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * needs recipient, template(id) and contexts
 */
interface Mail {

	/**
	 *
	 *
	 * @return Recipient
	 */
	public function getRecipient();

	/**
	 * Get the mail template's identifier (!not the actual DB-id) to be used.
	 *
	 * @return string
	 */
	public function getTemplateIdentifier();

	/**
	 * Get (additional) contexts needed for placeholder-replacements.
	 *
	 * @return MailContext[]
	 */
	public function getContexts();


}

