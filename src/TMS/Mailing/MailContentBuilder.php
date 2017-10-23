<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * This builds content for mails in TMS, as e.g. used for
 * automatic notifications in courses.
 *
 */
interface MailContentBuilder {

	/**
	 * get instance of this with template-identifier and contexts
	 *
	 * @param string 	$ident
	 * @param MailContext[] $contexts
	 * @return MailContentBuilder
	 */
	public function withData($ident, $contexts);

	/**
	 * Get the template's id of this Mail.
	 *
	 * @return int
	 */
	public function getTemplateId();

	/**
	 * Get the template's identifier of this Mail.
	 *
	 * @return int
	 */
	public function getTemplateIdentifier();

	/**
	 * Get the subject of Mail with placeholders applied
	 *
	 * @return string
	 */
	public function getSubject();

	/**
	 * Gets the (HTML-)message of Mail with filled placeholders,
	 * i.e.: apply all from placeholder values to template's message'.
	 *
	 * @return string
	 */
	public function getMessage();

	/**
	 * Gets the message of Mail with filled placeholders,
	 * i.e.: apply all from placeholder values to template's message'.
	 * All tags are stripped.
	 *
	 * @return string
	 */
	public function getPlainMessage();

	/**
	 * Returns pathes toimages that should be embedded.
	 *
	 * @return array[]	array( array('/path/to/img/img.jpg', 'img.jpg'), ...)
	 */
	public function getEmbeddedImages();

	//TODO: atachments
}