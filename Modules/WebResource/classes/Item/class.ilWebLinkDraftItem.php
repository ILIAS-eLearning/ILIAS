<?php

declare(strict_types=1);

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
 * Draft class for creating and updating a Web Link item
 * @author Tim Schmitz <schmitz@leifos.de>
 */
class ilWebLinkDraftItem extends ilWebLinkBaseItem
{
    protected bool $internal;

    /**
     * @param bool                      $internal
     * @param string                    $title
     * @param string|null               $description
     * @param string                    $target
     * @param bool                      $active
     * @param ilWebLinkBaseParameter[]  $parameters
     */
    public function __construct(
        bool $internal,
        string $title,
        ?string $description,
        string $target,
        bool $active,
        array $parameters
    ) {
        $this->internal = $internal;
        parent::__construct($title, $description, $target, $active, $parameters);
    }

    public function isInternal(): bool
    {
        return $this->internal;
    }

    public function addParameter(ilWebLinkBaseParameter $parameter): void
    {
        $this->parameters[] = $parameter;
    }
}
