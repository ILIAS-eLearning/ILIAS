<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

abstract class ilMailData
{
	abstract function getRecipientMailAddress();
	abstract function getRecipientFullName();
	abstract function hasCarbonCopyRecipients();
	abstract function getCarbonCopyRecipients();
	abstract function hasBlindCarbonCopyRecipients();
	abstract function getBlindCarbonCopyRecipients();
	abstract function getPlaceholderLocalized($a_placeholder_code, $a_lng, $a_markup = false);

	// Phase 2: Attachments via Maildata
	abstract function hasAttachments();
	abstract function getAttachments($a_lng); // Please note this is a plural. The method differs from the original concept!

	abstract function getRecipientUserId();
	
	// gev-patch start
	function deliversStandardPlaceholders() {
		return false;
	}
	// gev-patch end
}
