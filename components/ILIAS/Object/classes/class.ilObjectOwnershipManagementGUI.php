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

use ILIAS\UI\Factory as UIFactory;

/**
 * Class ilObjectOwnershipManagementGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilObjectOwnershipManagementGUI:
 */
class ilObjectOwnershipManagementGUI
{
    public const P_OWNID = 'ownid';
    protected ilObjUser $user;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected UIFactory $ui_factory;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilObjectDefinition $obj_definition;
    protected ilTree $tree;
    protected int $user_id;
    protected int $own_id = 0;
    protected bool $read_only;
    private ilObjectRequestRetriever $retriever;

    public function __construct(int $user_id = null, bool $read_only = false)
    {
        global $DIC;

        $this->user = $DIC['ilUser'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->lng = $DIC['lng'];
        $this->obj_definition = $DIC['objDefinition'];
        $this->tree = $DIC['tree'];
        $this->retriever = new ilObjectRequestRetriever($DIC->http()->wrapper(), $DIC['refinery']);

        $this->lng->loadLanguageModule('obj');

        $this->user_id = $this->user->getId();
        if (!is_null($user_id)) {
            $this->user_id = $user_id;
        }
        $this->read_only = $read_only;
        $this->own_id = $this->retriever->getMaybeInt(self::P_OWNID, 0);
    }

    public function executeCommand(): void
    {
        $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (!$cmd) {
            $cmd = 'listObjects';
        }
        $this->$cmd();
    }

    public function listObjects(): void
    {
        $objects = ilObject::getAllOwnedRepositoryObjects($this->user_id);

        $tbl = new ilObjectOwnershipManagementTableGUI($this, 'listObjects', $this->user_id);

        if ($objects === []) {
            $tbl->setTitle($this->lng->txt('user_owns_no_objects'));
            $this->tpl->setContent($tbl->getHTML());
            return;
        }

        $object_types = array_keys($objects);

        $options = [];
        foreach ($object_types as $type) {
            $this->ctrl->setParameterByClass(self::class, 'type', $type);
            $target = $this->ctrl->getLinkTargetByClass(self::class, 'listObjects');
            $label = $this->getLabelForObjectType($type);
            $options[$type] = $this->ui_factory->button()->shy($label, $target);
        }
        asort($options);

        $selected_type = $this->retriever->getMaybeString('type') ?? array_keys($options)[0];
        unset($options[$selected_type]);

        $dropdown = $this->ui_factory->dropdown()->standard($options)->withLabel(
            $this->lng->txt('select_object_type')
        );

        $this->toolbar->addStickyItem($dropdown);

        if (is_array($objects[$selected_type])
            && $objects[$selected_type] !== []) {
            ilObject::fixMissingTitles($selected_type, $objects[$selected_type]);
        }

        $tbl->setTitle($this->getLabelForObjectType($selected_type));
        $tbl->initItems($objects[$selected_type]);
        $this->tpl->setContent($tbl->getHTML());
    }

    private function getLabelForObjectType(string $type): string
    {
        if ($this->obj_definition->isPlugin($type)) {
            return $this->lng->txt($type, 'obj_' . $type);
        }

        return $this->lng->txt('objs_' . $type);
    }

    public function applyFilter(): void
    {
        $tbl = new ilObjectOwnershipManagementTableGUI($this, 'listObjects', $this->user_id);
        $tbl->resetOffset();
        $tbl->writeFilterToSession();
        $this->listObjects();
    }

    public function resetFilter(): void
    {
        $tbl = new ilObjectOwnershipManagementTableGUI($this, 'listObjects', $this->user_id);
        $tbl->resetOffset();
        $tbl->resetFilter();
        $this->listObjects();
    }

    protected function redirectParentCmd(int $ref_id, string $cmd): void
    {
        $parent = $this->tree->getParentId($ref_id);
        $this->ctrl->setParameterByClass('ilRepositoryGUI', 'ref_id', $parent);
        $this->ctrl->setParameterByClass('ilRepositoryGUI', 'item_ref_id', $ref_id);
        $this->ctrl->setParameterByClass('ilRepositoryGUI', 'cmd', $cmd);
        $this->ctrl->redirectByClass('ilRepositoryGUI');
    }

    protected function redirectCmd(int $ref_id, string $class, string $cmd = null): void
    {
        $node = $this->tree->getNodeData($ref_id);
        $gui_class = 'ilObj' . $this->obj_definition->getClassName($node['type']) . 'GUI';
        $path = ['ilRepositoryGUI', $gui_class, $class];

        if ($class == 'ilExportGUI') {
            try {
                $this->ctrl->getLinkTargetByClass($path);
            } catch (Exception $e) {
                switch ($node['type']) {
                    case 'glo':
                        $export_cmd = 'exportList';
                        $path = ['ilRepositoryGUI', 'ilGlossaryEditorGUI', $gui_class];
                        break;

                    default:
                        $export_cmd = 'export';
                        $path = ['ilRepositoryGUI', $gui_class];
                        break;
                }
                $this->ctrl->setParameterByClass($gui_class, 'ref_id', $ref_id);
                $this->ctrl->setParameterByClass($gui_class, 'cmd', $export_cmd);
                $this->ctrl->redirectByClass($path);
            }
        }

        $this->ctrl->setParameterByClass($class, 'ref_id', $ref_id);
        $this->ctrl->setParameterByClass($class, 'cmd', $cmd);
        $this->ctrl->redirectByClass($path);
    }

    public function delete(): void
    {
        $this->checkReadOnly();

        $this->redirectParentCmd(
            $this->own_id,
            'delete'
        );
    }

    public function move(): void
    {
        $this->checkReadOnly();

        $this->redirectParentCmd(
            $this->own_id,
            'cut'
        );
    }

    public function export(): void
    {
        $this->checkReadOnly();

        $this->redirectCmd(
            $this->own_id,
            ilExportGUI::class
        );
    }

    public function changeOwner(): void
    {
        $this->checkReadOnly();

        $this->redirectCmd(
            $this->own_id,
            ilPermissionGUI::class,
            'owner'
        );
    }

    public function isReadOnly(): bool
    {
        return $this->read_only;
    }

    protected function checkReadOnly(): void
    {
        if ($this->read_only) {
            throw new ilObjectException(
                'Cannot perform actions when in read only mode'
            );
        }
    }
}
