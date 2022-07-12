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

/**
 * Badge type gui interface
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
interface ilBadgeTypeGUI
{
    /**
     * Add custom fields to form
     */
    public function initConfigForm(ilPropertyFormGUI $a_form, int $a_parent_ref_id) : void;

    /**
     * Set form values
     */
    public function importConfigToForm(ilPropertyFormGUI $a_form, array $a_config) : void;
    
    /**
     * Export values to DB
     */
    public function getConfigFromForm(ilPropertyFormGUI $a_form) : array;

    /**
     * Custom form validation
     */
    public function validateForm(ilPropertyFormGUI $a_form) : bool;
}
