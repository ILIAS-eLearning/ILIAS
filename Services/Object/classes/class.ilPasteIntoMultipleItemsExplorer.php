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

use ILIAS\Repository\Clipboard\ClipboardManager;

/**
 * ilPasteIntoMultipleItemsExplorer Explorer
 *
 * @author Michael Jansen <mjansen@databay.de>
 *
 */
class ilPasteIntoMultipleItemsExplorer extends ilRepositoryExplorer
{
    public const SEL_TYPE_CHECK = 1;
    public const SEL_TYPE_RADIO = 2;

    protected int $type = 0;
    protected ClipboardManager $clipboard;

    protected array $checked_items = [];
    protected string $post_var = '';
    protected array $form_items = [];
    protected string $form_item_permission = 'read';

    public function __construct(int $type, string $target, string $session_variable)
    {
        global $DIC;

        $this->setId("cont_paste_explorer");
        $this->type = $type;

        parent::__construct($target);

        $this->root_id = $this->tree->readRootId();
        $this->order_column = 'title';
        $this->setSessionExpandVariable($session_variable);

        $this->addFilter('root');
        $this->addFilter('crs');
        $this->addFilter('grp');
        $this->addFilter('cat');
        $this->addFilter('fold');
        $this->addFilter('lso');

        $this->addFormItemForType('root');
        $this->addFormItemForType('crs');
        $this->addFormItemForType('grp');
        $this->addFormItemForType('cat');
        $this->addFormItemForType('fold');
        $this->addFormItemForType('lso');

        $this->setFiltered(true);
        $this->setFilterMode(IL_FM_POSITIVE);
        $this->clipboard = $DIC
            ->repository()
            ->internal()
            ->domain()
            ->clipboard();
    }

    public function isClickable(
        string $type,
        int $ref_id = 0
    ): bool {
        return false;
    }

    public function addFormItemForType(string $type): void
    {
        $this->form_items[$type] = true;
    }

    public function removeFormItemForType(string $type): void
    {
        $this->form_items[$type] = false;
    }

    public function setCheckedItems(array $checked_items = []): void
    {
        $this->checked_items = $checked_items;
    }

    public function isItemChecked(int $id): bool
    {
        return in_array($id, $this->checked_items);
    }

    public function setPostVar(string $post_var): void
    {
        $this->post_var = $post_var;
    }

    public function getPostVar(): string
    {
        return $this->post_var;
    }

    public function setRequiredFormItemPermission(string $form_item_permission): void
    {
        $this->form_item_permission = $form_item_permission;
    }

    public function getRequiredFormItemPermission(): string
    {
        return $this->form_item_permission;
    }

    public function buildFormItem(int $node_id, string $type): string
    {
        if (!$this->access->checkAccess($this->getRequiredFormItemPermission(), '', $node_id)) {
            return "";
        }

        if (
            !array_key_exists($type, $this->form_items) ||
            !$this->form_items[$type]
        ) {
            return "";
        }

        $disabled = false;
        if ($this->clipboard->hasEntries()) {
            $disabled = in_array($node_id, $this->clipboard->getRefIds());
        } elseif ($this->clipboard->getCmd() == 'copy' && $node_id == $this->clipboard->getParent()) {
            $disabled = true;
        }

        switch ($this->type) {
            case self::SEL_TYPE_CHECK:
                return ilLegacyFormElementsUtil::formCheckbox(
                    $this->isItemChecked($node_id),
                    $this->post_var,
                    (string) $node_id,
                    $disabled
                );
            case self::SEL_TYPE_RADIO:
                return ilLegacyFormElementsUtil::formRadioButton(
                    $this->isItemChecked($node_id),
                    $this->post_var,
                    (string) $node_id,
                    '',
                    $disabled
                );
        }
        return "";
    }

    public function formatObject(ilTemplate $tpl, $node_id, array $option, $obj_id = 0): void
    {
        if (!isset($node_id) or !is_array($option)) {
            $this->error->raiseError(
                get_class($this) .
                "::formatObject(): Missing parameter or wrong datatype! " .
                "node_id: " .
                $node_id .
                " options:" .
                var_dump($option),
                $this->error->WARNING
            );
        }

        $pic = false;
        foreach ($option["tab"] as $picture) {
            if ($picture == 'plus') {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $this->lng->txt("expand"));
                $target = $this->createTarget('+', $node_id);
                $tpl->setVariable("LINK_NAME", $node_id);
                $tpl->setVariable("LINK_TARGET_EXPANDER", $target);
                $tpl->setVariable("IMGPATH", $this->getImage("browser/plus.png"));
                $tpl->parseCurrentBlock();
                $pic = true;
            }

            if ($picture == 'minus' && $this->show_minus) {
                $tpl->setCurrentBlock("expander");
                $tpl->setVariable("EXP_DESC", $this->lng->txt("collapse"));
                $target = $this->createTarget('-', $node_id);
                $tpl->setVariable("LINK_NAME", $node_id);
                $tpl->setVariable("LINK_TARGET_EXPANDER", $target);
                $tpl->setVariable("IMGPATH", $this->getImage("browser/minus.png"));
                $tpl->parseCurrentBlock();
                $pic = true;
            }
        }

        if (!$pic) {
            $tpl->setCurrentBlock("blank");
            $tpl->setVariable("BLANK_PATH", $this->getImage("browser/blank.png"));
            $tpl->parseCurrentBlock();
        }

        if ($this->output_icons) {
            $tpl->setCurrentBlock("icon");

            $path = ilObject::_getIcon($obj_id, "tiny", $option["type"]);
            $tpl->setVariable("ICON_IMAGE", $path);

            $tpl->setVariable("TARGET_ID", "iconid_" . $node_id);
            $this->iconList[] = "iconid_" . $node_id;
            $tpl->setVariable("TXT_ALT_IMG", $this->lng->txt($option["desc"]));
            $tpl->parseCurrentBlock();
        }

        if (strlen($formItem = $this->buildFormItem($node_id, $option['type']))) {
            $tpl->setCurrentBlock('check');
            $tpl->setVariable('OBJ_CHECK', $formItem);
            $tpl->parseCurrentBlock();
        }

        if ($this->isClickable($option["type"], $node_id)) {
            $tpl->setCurrentBlock("link");
            $tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($node_id, $option["type"]));

            $style_class = $this->getNodeStyleClass($node_id, $option["type"]);

            if ($style_class != "") {
                $tpl->setVariable("A_CLASS", ' class="' . $style_class . '" ');
            }

            if (($onclick = $this->buildOnClick($node_id, $option["type"], $option["title"])) != "") {
                $tpl->setVariable("ONCLICK", "onClick=\"$onclick\"");
            }

            $tpl->setVariable("LINK_NAME", $node_id);
            $tpl->setVariable("TITLE", $this->buildTitle($option["title"], $node_id, $option["type"]));
            $tpl->setVariable(
                "DESC",
                ilStr::shortenTextExtended(
                    $this->buildDescription($option["description"], $node_id, $option["type"]),
                    $this->textwidth,
                    true
                )
            );
            $frame_target = $this->buildFrameTarget($option["type"], $node_id, $option["obj_id"]);
            if ($frame_target != "") {
                $tpl->setVariable("TARGET", " target=\"" . $frame_target . "\"");
            }
        } else {
            $obj_title = $this->buildTitle($option["title"], $node_id, $option["type"]);

            if ($node_id == $this->highlighted) {
                $obj_title = "<span class=\"ilHighlighted\">" . $obj_title . "</span>";
            }

            $tpl->setCurrentBlock("text");
            $tpl->setVariable("OBJ_TITLE", $obj_title);
            $tpl->setVariable(
                "OBJ_DESC",
                ilStr::shortenTextExtended(
                    $this->buildDescription($option["desc"], $node_id, $option["type"]),
                    $this->textwidth,
                    true
                )
            );
        }
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("list_item");
        $tpl->parseCurrentBlock();
        $tpl->touchBlock("element");
    }

    /*
     * @param int $obj_id
     */
    public function formatHeader(ilTemplate $tpl, $obj_id, array $option): void
    {
        $path = ilObject::_getIcon((int) $obj_id, "tiny", "root");

        $tpl->setCurrentBlock("icon");
        $nd = $this->tree->getNodeData(ROOT_FOLDER_ID);
        $title = $nd["title"];
        if ($title == "ILIAS") {
            $title = $this->lng->txt("repository");
        }

        $tpl->setVariable("ICON_IMAGE", $path);
        $tpl->setVariable("TXT_ALT_IMG", $title);
        $tpl->parseCurrentBlock();

        if (strlen($formItem = $this->buildFormItem($obj_id, $option['type']))) {
            $tpl->setCurrentBlock('check');
            $tpl->setVariable('OBJ_CHECK', $formItem);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('OBJ_TITLE', $title);
    }

    public function showChilds($parent_id, $obj_id = 0): bool
    {
        if ($parent_id == 0) {
            return true;
        }
        if ($this->access->checkAccess("read", "", $parent_id)) {
            return true;
        }
        return false;
    }

    public function isVisible($ref_id, string $type): bool
    {
        if (!$this->access->checkAccess('visible', '', $ref_id)) {
            return false;
        }
        return true;
    }
}
