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

namespace ILIAS\Repository\Form;

use ILIAS\Object\ilObjectDIC;
use ILIAS\DI\Container;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectPropertyTileImage;

trait StdObjProperties
{
    protected \ilObjectPropertiesAgregator $object_prop;
    protected function initStdObjProperties(Container $DIC)
    {
        $this->object_prop = ilObjectDIC::dic()['object_properties_agregator'];
    }

    public function addStdTitleAndDescription(
        int $obj_id,
        string $type
    ): FormAdapterGUI {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $inputs = $obj_prop
            ->getPropertyTitleAndDescription()
            ->toForm($this->lng, $this->ui->factory()->input()->field(), $this->refinery)->getInputs();
        $this->addField("title", $inputs[0]);
        $this->addField("description", $inputs[1]);
        return $this;
    }

    public function saveStdTitleAndDescription(
        int $obj_id,
        string $type
    ): void {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $obj_prop->storePropertyTitleAndDescription(
            new \ilObjectPropertyTitleAndDescription(
                $this->getData("title"),
                $this->getData("description")
            )
        );
    }

    public function addStdTile(
        int $obj_id,
        string $type
    ): FormAdapterGUI {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $input = $obj_prop->getPropertyTileImage()
            ->toForm($this->lng, $this->ui->factory()->input()->field(), $this->refinery);
        $this->addField("tile", $input, true);
        return $this;
    }

    public function saveStdTile(
        int $obj_id,
        string $type
    ): void {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $obj_prop->storePropertyTileImage($this->getData("tile"));
    }

    public function addOnline(
        int $obj_id,
        string $type
    ): FormAdapterGUI {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $input = $obj_prop->getPropertyIsOnline()
                          ->toForm($this->lng, $this->ui->factory()->input()->field(), $this->refinery);
        $this->addField("is_online", $input, true);
        return $this;
    }

    public function saveOnline(
        int $obj_id,
        string $type
    ): void {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $obj_prop->storePropertyIsOnline($this->getData("is_online"));
    }

}
