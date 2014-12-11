<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Search/classes/class.ilSearchSettings.php';
include_once './Services/Mail/classes/class.ilMailAutoCompleteRecipientResult.php';
include_once './Services/Mail/classes/class.ilMailAutoCompleteSentMailsRecipientsProvider.php';
include_once './Services/Mail/classes/class.ilMailAutoCompleteAddressbookLoginProvider.php';
include_once './Services/Mail/classes/class.ilMailAutoCompleteAddressbookEmailProvider.php';
include_once './Services/Mail/classes/class.ilMailAutoCompleteUserProvider.php';
include_once './Services/Mail/classes/class.ilMailAutoCompleteSearch.php';

/**
 * @author Nadia Ahmad
 * @version $Id$
 */
class ilMailForm
{
	/**
	 *
	 * Called by class ilMailFormGUI
	 *
	 * @param	string		search string surrounded with wildcards
	 * @param	string		search string
	 * @return	array	    search result as an object of type stdClass
	 * @access	public
	 *
	 */
	public function getRecipientAsync($quoted_term, $term, $search_recipients = true)
	{
		if($search_recipients)
		{
			$sent_mails_recipient_provider = new ilMailAutoCompleteSentMailsRecipientsProvider($quoted_term, $term);
		}
		$address_book_login            = new ilMailAutoCompleteAddressbookLoginProvider($quoted_term, $term);
		$address_book_email            = new ilMailAutoCompleteAddressbookEmailProvider($quoted_term, $term);
		$user                          = new ilMailAutoCompleteUserProvider($quoted_term, $term);

		$result                        = new ilMailAutocompleteRecipientResult(
			isset($_GET['fetchall']) && (int)$_GET['fetchall'] ?
			ilMailAutocompleteRecipientResult::MODE_FETCH_ALL :
			ilMailAutocompleteRecipientResult::MODE_STOP_ON_MAX_ENTRIES
		);

		$result_fetcher = new ilMailAutoCompleteSearch($result);
		if($search_recipients)
		{
			$result_fetcher->addProvider($sent_mails_recipient_provider);
		}
		$result_fetcher->addProvider($address_book_login);
		$result_fetcher->addProvider($address_book_email);
		$result_fetcher->addProvider($user);
		$result_fetcher->search();

		return $result->getItems();
	}
}
