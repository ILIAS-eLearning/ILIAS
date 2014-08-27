<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailAutoCompleteRecipientResult.php';
include_once './Services/Mail/classes/class.ilMailAutoCompleteUserProvider.php';
include_once './Services/Mail/classes/class.ilMailAutoCompleteSearch.php';

include_once './Services/Mail/classes/class.ilMailAutoCompleteSentMailsRecipientsProvider.php';
include_once './Services/Mail/classes/class.ilMailAutoCompleteAddressbookLoginProvider.php';
include_once './Services/Mail/classes/class.ilMailAutoCompleteAddressbookEmailProvider.php';
include_once './Services/Mail/classes/class.ilMailAutoCompleteProviderEmailFilter.php';

/**
* @author Nadia Ahmad
* @version $Id$
*
*/
class ilMailAddressbook
{
	public function getUsersAsync($quoted_term, $term)
	{
		$result = new ilMailAutocompleteRecipientResult(
			isset($_GET['fetchall']) && (int)$_GET['fetchall'] ?
				ilMailAutocompleteRecipientResult::MODE_FETCH_ALL :
				ilMailAutocompleteRecipientResult::MODE_STOP_ON_MAX_ENTRIES
		);

		$result_fetcher = new ilMailAutoCompleteSearch($result);
		$result_fetcher->addProvider(new ilMailAutoCompleteUserProvider($quoted_term, $term));
		$result_fetcher->search();

		return $result->getItems();	
	}

	public function getAddressbookAsync($quoted_term, $term)
	{
		$address_book_login            = new ilMailAutoCompleteAddressbookLoginProvider($quoted_term, $term);
		$address_book_email            = new ilMailAutoCompleteAddressbookEmailProvider($quoted_term, $term);

		$result                        = new ilMailAutocompleteRecipientResult(
			isset($_GET['fetchall']) && (int)$_GET['fetchall'] ?
				ilMailAutocompleteRecipientResult::MODE_FETCH_ALL :
				ilMailAutocompleteRecipientResult::MODE_STOP_ON_MAX_ENTRIES
		);

		$result_fetcher = new ilMailAutoCompleteSearch($result);
		$result_fetcher->addProvider($address_book_login);
		$result_fetcher->addProvider($address_book_email);
		$result_fetcher->search();

		return $result->getItems();
	}
	
	public function getEmailsAsync($quoted_term, $term)
	{
		$result = new ilMailAutocompleteRecipientResult(
			isset($_GET['fetchall']) && (int)$_GET['fetchall'] ?
				ilMailAutocompleteRecipientResult::MODE_FETCH_ALL :
				ilMailAutocompleteRecipientResult::MODE_STOP_ON_MAX_ENTRIES
		);

		$result_fetcher = new ilMailAutoCompleteSearch($result);
		$result_fetcher->addProvider(new ilMailAutoCompleteProviderEmailFilter(new ilMailAutoCompleteSentMailsRecipientsProvider($quoted_term, $term)));
		$result_fetcher->search();

		return $result->getItems();
	}
}