<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Nadia Ahmad
 * @version $Id$
 */
class ilMailForm
{
    private GlobalHttpState $http;
    private Refinery $refinery;

    /**
     * @param string $quotedTerm
     * @param string $term
     * @param bool $doRecipientSearch
     * @return array{hasMoreResults: bool, items: array}
     */
    public function getRecipientAsync(string $quotedTerm, string $term, bool $doRecipientSearch = true) : array
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $mode = ilMailAutoCompleteRecipientResult::MODE_STOP_ON_MAX_ENTRIES;
        if (
            $this->http->wrapper()->query()->has('fetchall') &&
            $this->http->wrapper()->query()->retrieve('fetchall', $this->refinery->kindlyTo()->bool())
        ) {
            $mode = ilMailAutoCompleteRecipientResult::MODE_FETCH_ALL;
        }

        $result = new ilMailAutoCompleteRecipientResult($mode);

        $search = new ilMailAutoCompleteSearch($result);
        if ($doRecipientSearch) {
            $search->addProvider(new ilMailAutoCompleteSentMailsRecipientsProvider($quotedTerm, $term));
        }
        $search->addProvider(new ilMailAutoCompleteBuddyRecipientsProvider($quotedTerm, $term));
        $search->addProvider(new ilMailAutoCompleteUserProvider($quotedTerm, $term));
        $search->search();

        return $result->getItems();
    }
}
