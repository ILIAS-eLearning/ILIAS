<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * This encapsulates basic functionality for mailing.
 */
interface Actions {

	const BOOKED_ON_COURSE = "B01";
	const BOOKED_ON_WAITINGLIST = "B02";
	const CANCELED_FROM_COURSE = "C01";
	const CANCELED_FROM_WAITINGLIST = "C02";

	/**
	 * Get a template's data by its ident, e.g. "B01".
	 *
	 * @param string 	$ident
	 * @return array<string, string> | null
	 */
	public function getTemplateDataByIdent($ident);

	/**
	 * Get an instance of content-builder.
	 * @return ContentBuilder
	 */
	public function getContentBuilder();

	/**
	 * Get an instance of the logging db.
	 * @return LoggingDB
	 */
	public function getMailLogDB();

	/**
	 * Get the sender of ilias-mails
	 * @return Recipient
	 */
	public function getStandardSender();

	/**
	 * Get the TMS Clerk
	 *
	 * @return TMSMailClerk
	 */
	public function getClerk();

}
