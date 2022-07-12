<?php declare(strict_types=1);

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

namespace ILIAS\Notes;

use ILIAS\Export;
use ilPropertyFormGUI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PrintViewProvider extends Export\AbstractPrintViewProvider
{
    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;

    public function __construct(
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
    }

    public function getTemplateInjectors() : array
    {
        return [
            static function (\ilGlobalTemplate $tpl) : void {
                //$tpl add js/css
            }
        ];
    }

    public function getSelectionForm() : ?ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new \ilPropertyFormGUI();

        $form->addCommandButton("printView", $lng->txt("print_view"));

        //$form->setTitle($lng->txt("svy_print_selection"));
        $form->setFormAction("#");

        return $form;
    }

    public function getOnSubmitCode() : string
    {
        return "event.preventDefault(); " .
            "window.setTimeout(() => { window.print();}, 500);";
    }
}
