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

/**
 * Container page GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilContainerPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilContainerPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilContainerPageGUI: ilPropertyFormGUI, ilInternalLinkGUI, ilPageMultiLangGUI
 */
class ilContainerPageGUI extends ilPageObjectGUI
{
    protected ilObjectDefinition $obj_definition;
    protected int $requested_ref_id = 0;

    public function __construct(
        int $a_id = 0,
        int $a_old_nr = 0,
        string $a_lang = ""
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->obj_definition = $DIC["objDefinition"];
        $request = $DIC->container()->internal()->gui()->standardRequest();
        $this->requested_ref_id = $request->getRefId();

        parent::__construct("cont", $a_id, $a_old_nr, false, $a_lang);
    }

    public function getProfileBackUrl() : string
    {
        $link = ilLink::_getLink($this->requested_ref_id);
        // make it relative, since profile only accepts relative links as back links
        $link = substr($link, strpos($link, "//") + 2);
        $link = substr($link, strpos($link, "/"));
        return $link;
    }

    public function finishEditing() : void
    {
        $this->ctrl->returnToParent($this);
    }

    public function getAdditionalPageActions() : array
    {
        $ctrl = $this->ctrl;
        $ui = $this->ui;
        $lng = $this->lng;

        $type = ilObject::_lookupType(
            ilObject::_lookupObjectId($this->requested_ref_id)
        );

        $class = $this->obj_definition->getClassName($type);

        $items = [];
        if ($class !== "") {
            $items[] = $ui->factory()->link()->standard(
                $lng->txt("obj_sty"),
                $ctrl->getLinkTargetByClass([
                    "ilRepositoryGUI",
                    "ilObj" . $class . "GUI"
                ], "editStyleProperties")
            );
        }
        return $items;
    }
}
