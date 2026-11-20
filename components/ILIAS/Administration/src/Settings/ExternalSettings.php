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

namespace ILIAS\Administration\Settings;

/**
 * Settings of a component which are shown on settings forms of other components
 * in the administration, contributed via $contribute[ExternalSettings::class].
 * The settings are provided by addToExternalSettingsForm(int $form_id) of the
 * GUI, which is also the target of the edit link.
 */
interface ExternalSettings
{
    /**
     * Object type of the administration node, used as default caption (obj_<type>).
     */
    public function getObjectType(): string;

    /**
     * @return SettingsForm[]
     */
    public function getForms(): array;

    /**
     * Position on the given form, lower first.
     */
    public function getPosition(SettingsForm $form): int;

    public function getGUI(): \ilObjectGUI;
}
