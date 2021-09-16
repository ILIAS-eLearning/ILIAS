<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

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
    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;
    protected int $requested_ref_id = 0;

    /**
    * Constructor
    */
    public function __construct($a_id = 0, $a_old_nr = 0, $a_lang = "")
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $tpl = $DIC["tpl"];
        $this->obj_definition = $DIC["objDefinition"];
        $params = $DIC->http()->request()->getQueryParams();
        $this->requested_ref_id = (int) ($params["ref_id"] ?? 0);

        parent::__construct("cont", $a_id, $a_old_nr, false, $a_lang);
    }

    /**
     * Get profile back url
     */
    public function getProfileBackUrl()
    {
        $link = ilLink::_getLink((int) $_GET["ref_id"]);
        // make it relative, since profile only accepts relative links as back links
        $link = substr($link, strpos($link, "//") + 2);
        $link = substr($link, strpos($link, "/"));
        return $link;
    }

    public function finishEditing()
    {
        $this->ctrl->returnToParent($this);
    }

    /**
     * Get additional page actions
     * @return array
     */
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
        if ($class != "") {
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
