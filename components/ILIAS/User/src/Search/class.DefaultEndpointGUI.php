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

use ILIAS\UI\URLBuilder;

class DefaultEndpointGUI extends Endpoint implements \ilCtrlBaseClassInterface
{
    private const array NAMESPACE = ['u', 's'];
    private const string SEARCH_TERM_TOKEN = 't';

    public function acquireBuilderAndToken(): array
    {
        return (new URLBuilder(
            $this->data_factory->uri(
                ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(self::class)
            )
        ))->acquireParameter(self::NAMESPACE, self::SEARCH_TERM_TOKEN);
    }

    public function getAdditionalAnswerElements(
        \ilObjUser $current_user,
        AutocompleteQuery $autocomplete_query
    ): array {
        return [];
    }
}
