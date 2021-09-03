<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Nadia Ahmad
 * @version $Id$
 */
class ilMailForm
{
    private ServerRequestInterface $httpRequest;

    /**
     * @param string $quotedTerm
     * @param string $term
     * @param bool $doRecipientSearch
     * @return array
     */
    public function getRecipientAsync(string $quotedTerm, string $term, bool $doRecipientSearch = true) : array
    {
        global $DIC;
        $this->httpRequest = $DIC->http()->request();
        $mode = ilMailAutoCompleteRecipientResult::MODE_STOP_ON_MAX_ENTRIES;
        if (isset($this->httpRequest->getQueryParams()['fetchall']) && $this->httpRequest->getQueryParams()['fetchall']) {
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
