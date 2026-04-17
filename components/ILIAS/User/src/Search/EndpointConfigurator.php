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

namespace ILIAS\User\Search;

interface EndpointConfigurator
{
    /**
     * @return list<string> MUST return an array containing all class names in
     * the path to be prepended to the EndpointGUI to build the URL
     * with {@see \ilCtrlInterface}
     */
    public function getParentClassPath(): array;

    /**
     * @return list<AutocompleteItem> Items that should be
     * added to the list the user can select from.
     */
    public function getAdditionalAnswerElements(
        \ilObjUser $current_user,
        AutocompleteQuery $autocomplete_query
    ): array;
}
