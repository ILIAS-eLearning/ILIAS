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

namespace ILIAS\UI\Implementation\Component\Listing\Entity;

use ILIAS\UI\Component\Listing\Entity as I;
use ILIAS\UI\Component\Entity as IEntity;
use ILIAS\UI\Implementation\Component\ComponentHelper;

abstract class EntityListing implements I\EntityListing
{
    use ComponentHelper;

    public function __construct(
        protected EntityFactory $entity_factory
    ) {
    }

    public function withData(mixed $data): self
    {
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }

    public function getEntities(\ILIAS\UI\Factory $ui_factory): \Generator
    {
        return $this->entity_factory->get($ui_factory, $this->data);
    }
}
