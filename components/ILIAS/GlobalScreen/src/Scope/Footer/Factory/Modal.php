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

namespace ILIAS\GlobalScreen\Scope\Footer\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Modal extends AbstractBaseItem implements canHaveParent
{
    use hasTitleTrait;
    use canHaveParentTrait;

    public function __construct(
        IdentificationInterface $provider_identification,
        string $title,
        protected \ILIAS\UI\Component\Modal\Modal $modal
    ) {
        parent::__construct($provider_identification);
        $this->title = $title;
    }

    public function getModal(): \ILIAS\UI\Component\Modal\Modal
    {
        return $this->modal;
    }

    public function withModal(\ILIAS\UI\Component\Modal\Modal $modal): self
    {
        $clone = clone $this;
        $clone->modal = $modal;
        return $clone;
    }

    public function isTop(): bool
    {
        return !$this->hasParent();
    }

}
