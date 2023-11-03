<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\BookingProcess;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectSelectionListGUI
{
    protected \ILIAS\BookingManager\Objects\ObjectsManager $object_manager;
    protected ObjectSelectionManager $object_selection;
    protected \ilLanguage $lng;
    protected \ILIAS\DI\UIServices $ui;
    protected string $form_action;

    public function __construct(
        int $pool_id,
        string $form_action
    ) {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->object_manager = $DIC->bookingManager()->internal()->domain()
            ->objects($pool_id);
        $this->form_action = $form_action;
        $this->object_selection = $DIC->bookingManager()->internal()->domain()
            ->objectSelection($pool_id);
    }

    public function render() : string
    {
        $tpl = new \ilTemplate("tpl.obj_selection.html", true, true, "Modules/BookingManager/BookingProcess");

        $selected = $this->object_selection->getSelectedObjects();
        foreach ($this->object_manager->getObjectTitles() as $id => $title) {
            $tpl->setCurrentBlock("item");
            if (in_array($id, $selected)) {
                $tpl->setVariable("CHECKED", "checked='checked'");
            }
            $tpl->setVariable("ID", $id);
            $tpl->setVariable("COLOR_NR", $this->object_manager->getColorNrForObject($id));
            $tpl->setVariable("TITLE", $title);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("FORM_ACTION", $this->form_action);
        $submit_button = $this->ui->factory()->button()->standard(
            $this->lng->txt("book_refresh"),
            "#"
        )->withAdditionalOnLoadCode(function ($id) {
            return <<<EOT
            const book_submit_btn = document.getElementById('$id');
            book_submit_btn.addEventListener("click", (event) => {
                book_submit_btn.closest('form').submit(); return false;
            });
EOT;
        });
        $tpl->setVariable("BUTTON", $this->ui->renderer()->render($submit_button));

        $p = $this->ui->factory()->panel()->secondary()->legacy(
            $this->lng->txt("book_object_selection"),
            $this->ui->factory()->legacy($tpl->get())
        );
        return $this->ui->renderer()->render($p);
    }
}
