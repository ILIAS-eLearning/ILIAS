<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Nadia Ahmad
 * @version $Id$
 */
class ilMailForm
{
    /**
     * @param string $quotedTerm
     * @param string $term
     * @param bool $doRecipientSearch
     * @return array
     */
    public function getRecipientAsync(string $quotedTerm, string $term, bool $doRecipientSearch = true) : array
    {
        $mode = ilMailAutoCompleteRecipientResult::MODE_STOP_ON_MAX_ENTRIES;
        if (isset($_GET['fetchall']) && $_GET['fetchall']) {
            $mode = ilMailAutoCompleteRecipientResult::MODE_FETCH_ALL;
        }

        $result = new ilMailAutoCompleteRecipientResult($mode);

        $search = new ilMailAutoCompleteSearch($result);
        if ($doRecipientSearch) {
            $search->addProvider(new ilMailAutoCompleteSentMailsRecipientsProvider($quotedTerm, $term));
        }
        $search->addProvider(new ilMailAutoCompleteBuddyRecipientsProvider($quotedTerm, $term));
        if (ilSearchSettings::getInstance()->isLuceneUserSearchEnabled()) {
            $search->addProvider(new ilMailAutoCompleteUserProvider($quotedTerm, $term));
        }
        $search->search();

        return $result->getItems();
    }
}
