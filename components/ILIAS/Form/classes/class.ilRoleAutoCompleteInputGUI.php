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
 * This class represents a role + autocomplete feature form input
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilRoleAutoCompleteInputGUI extends ilTextInputGUI
{
    /**
     * @param string|object $a_class
     */
    public function __construct(
        string $a_title,
        string $a_postvar,
        $a_class,
        string $a_autocomplete_cmd
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $ilCtrl = $DIC->ctrl();

        if (is_object($a_class)) {
            $a_class = get_class($a_class);
        }
        $a_class = strtolower($a_class);

        parent::__construct($a_title, $a_postvar);
        $this->setInputType("raci");
        $this->setMaxLength(70);
        $this->setSize(30);
        $this->setDataSource($ilCtrl->getLinkTargetByClass($a_class, $a_autocomplete_cmd, "", true));
    }

    /**
     * Static asynchronous default auto complete function.
     */
    public static function echoAutoCompleteList(): void
    {
        global $DIC;

        $t = $DIC->refinery()->kindlyTo()->string();
        $w = $DIC->http()->wrapper();
        $q = "";
        if ($w->query()->has("term")) {
            $q = $w->query()->retrieve("term", $t);
        }
        if ($w->post()->has("term")) {
            $q = $w->post()->retrieve("term", $t);
        }
        $list = ilRoleAutoComplete::getList($q);
        echo $list;
        exit;
    }
}
