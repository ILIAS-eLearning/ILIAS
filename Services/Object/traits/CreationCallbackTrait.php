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

namespace ILIAS\Object;

trait CreationCallbackTrait
{
    public function callCreationCallback(
        \ilObject $obj,
        \ilObjectDefinition $obj_definition,
        int $requested_crtcb
    ) : void {
        if ($requested_crtcb === 0) {
            return;
        }

        $callback_type = \ilObject::_lookupType((int) $requested_crtcb, true);
        $class_name = 'ilObj' . $obj_definition->getClassName($callback_type) . 'GUI';

        if (strtolower($class_name) === 'ilobjitemgroupgui') {
            $callback_obj = new $class_name((int) $this->requested_crtcb);
        } else {
            $callback_obj = new $class_name(null, $this->requested_crtcb, true, false);
        }
        $callback_obj->afterSaveCallback($obj);
    }
}
