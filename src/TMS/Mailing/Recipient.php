<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * A Recipient must provide an eMail-address;
 * it is used in the Clerk to get mail and user name from.
 */
interface Recipient {

	/**
	 *
	 * @return string | null
	 */
	public function getMailAddress();

	/**
	 *
	 * @return string | null
	 */
	public function getUserId();

	/**
	 *
	 * @return string | null
	 */
	public function getUserLogin();

	/**
	 * Check, if user is active (or not an ilUser)
	 *
	 * @return bool
	 */
	public function isInactiveUser();

	/**
	 *
	 * @return string | null
	 */
	public function getUserName();

	/**
	 * @param string 	$name
	 * @throws Exception if Recipient was constructed with an id
	 * @return Recipient
	 */
	public function withName($name);

	/**
	 * @param string 	$mail
	 * @throws Exception if Recipient was constructed with an id
	 * @return Recipient
	 */
	public function withMail($mail);




}

