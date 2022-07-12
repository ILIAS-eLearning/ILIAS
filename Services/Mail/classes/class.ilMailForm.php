<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
     * @return array{hasMoreResults: bool, items: array}
     */
    public function getRecipientAsync(string $quotedTerm, string $term, bool $doRecipientSearch = true) : array
    {
        global $DIC;

        $http = $DIC->http();
        $refinery = $DIC->refinery();

        $mode = ilMailAutoCompleteRecipientResult::MODE_STOP_ON_MAX_ENTRIES;
        if (
            $http->wrapper()->query()->has('fetchall') &&
            $http->wrapper()->query()->retrieve('fetchall', $refinery->kindlyTo()->bool())
        ) {
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
