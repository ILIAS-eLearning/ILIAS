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

namespace ILIAS\Style\Content\Object;

use ILIAS\Style\Content\InternalDataService;
use ILIAS\Style\Content\InternalDomainService;
use ilObject;

/**
 * External facade for object content styles
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectFacade
{
    protected ObjectManager $object_manager;
    protected int $ref_id;
    protected int $obj_id;
    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDataService $data_service,
        InternalDomainService $domain_service,
        int $ref_id,
        int $obj_id = 0
    ) {
        $this->ref_id = $ref_id;
        $this->obj_id = ($obj_id > 0)
            ? $obj_id
            : ilObject::_lookupObjId($ref_id);
        $this->domain_service = $domain_service;
        $this->object_manager = $domain_service->object($this->ref_id, $obj_id);
    }

    /**
     * This must be called on cloning the parent object, with passing
     * the object id of the clone.
     */
    public function cloneTo(int $obj_id): void
    {
        $this->object_manager->cloneTo($obj_id);
    }

    /**
     * This ID must be used when rendering the object (pages). It respects global
     * settings like fixed style IDs.
     */
    public function getEffectiveStyleId(): int
    {
        return $this->object_manager->getEffectiveStyleId();
    }

    /**
     * Get the style ID currently set (stored) by the object.
     * Note: This may be different from the effective style id, e.g. if a fixed
     * global style overwrites the ID of the current object, or the ID of the
     * current object is invalid, e.g. by referencing a non-shared parent style ID.
     */
    public function getStyleId(): int
    {
        return $this->object_manager->getStyleId();
    }

    /**
     * Calling this should usually be avoided, currently this is
     * necessary on import routines, but otherwise updates should be
     * called internally automatically.
     */
    public function updateStyleId(int $style_id): void
    {
        $this->object_manager->updateStyleId($style_id);
    }

    /**
     * Inherits a non local style from the parent container
     */
    public function inheritFromParent(): void
    {
        $this->object_manager->inheritFromParent();
    }
}
