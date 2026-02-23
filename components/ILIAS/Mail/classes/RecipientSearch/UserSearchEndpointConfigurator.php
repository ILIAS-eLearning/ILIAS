<?php

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

declare(strict_types=1);

namespace ILIAS\Mail\RecipientSearch;

use ILIAS\User\Search\EndpointConfigurator;
use ILIAS\User\Search\AutocompleteQuery;

class UserSearchEndpointConfigurator implements EndpointConfigurator
{
    public function getParentClassPath(): array
    {
        return [
            \ilMailGUI::class,
            \ilMailFormGUI::class
        ];
    }

    public function getAdditionalAnswerElements(
        \ilObjUser $current_user,
        AutocompleteQuery $autocomplete_query
    ): array {
        // #14768
        $term = \ilUtil::stripSlashes($autocomplete_query->getUnprocessedSearchTerm());
        $quoted_term = '%' . str_replace(
            ['%', '_'],
            ['\%', '\_'],
            $term
        ) . '%';

        $result = new UserSearchAutocompleteItemResult($autocomplete_query);

        $search = new Search($result);
        $search->addProvider(
            new SentMailsBasedProvider($quoted_term, $term)
        );
        $search->addProvider(
            new \ILIAS\Contact\BuddySystem\MailRecipientSearch\MailRecipientSearchProvider($quoted_term, $term)
        );
        $search->search();

        return $result->getItems();
    }
}
