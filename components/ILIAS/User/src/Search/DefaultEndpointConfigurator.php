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

readonly class DefaultEndpointConfigurator implements EndpointConfigurator
{
    /**
     * @param list<string> $parent_class_path like in {@see EndpointConfigurator::getParentClassPath()}
     */
    public function __construct(
        private array $parent_class_path
    ) {
    }
    public function getParentClassPath(): array
    {
        return $this->parent_class_path;
    }

    public function getAdditionalAnswerElements(
        \ilObjUser $current_user,
        AutocompleteQuery $autocomplete_query
    ): array {
        return [];
    }
}
