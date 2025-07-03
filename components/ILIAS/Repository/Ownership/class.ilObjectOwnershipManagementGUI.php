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

use ILIAS\Repository\InternalDomainService;
use ILIAS\Repository\InternalGUIService;

class ilObjectOwnershipManagementGUI
{
    public const P_OWNID = 'ownid';
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected int $user_id;
    protected int $own_id = 0;
    protected bool $read_only;
    private ilObjectRequestRetriever $retriever;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        ?int $user_id = null,
        bool $read_only = false
    ) {
        $this->ctrl = $this->gui->ctrl();
        $this->lng = $this->domain->lng();
        $this->retriever = new ilObjectRequestRetriever(
            $this->gui->http()->wrapper(),
            $this->domain->refinery()
        );

        $this->lng->loadLanguageModule('obj');

        $this->user_id = $domain->user()->getId();
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
        $mt = $this->gui->mainTemplate();
        $toolbar = $this->gui->toolbar();
        $f = $this->gui->ui()->factory();

        if ($objects === []) {
            $table_builder = $this->gui->ownership()->ownershipManagementTableBuilder(
                $this->user_id,
                $this->lng->txt('user_owns_no_objects'),
                [],
                '',
                $this,
                'listObjects'
            );
            $mt->setContent($table_builder->getTable()->render());
            return;
        }

        $object_types = array_keys($objects);

        $options = [];
        foreach ($object_types as $type) {
            $this->ctrl->setParameterByClass(self::class, 'type', $type);
            $target = $this->ctrl->getLinkTargetByClass(self::class, 'listObjects');
            $label = $this->getLabelForObjectType($type);
            $options[$type] = $f->button()->shy($label, $target);
        }
        asort($options);

        $selected_type = $this->retriever->getMaybeString('type') ?? array_keys($options)[0];
        unset($options[$selected_type]);

        $dropdown = $f->dropdown()->standard($options)->withLabel(
            $this->lng->txt('select_object_type')
        );

        $toolbar->addStickyItem($dropdown);

        if (is_array($objects[$selected_type])
            && $objects[$selected_type] !== []) {
            ilObject::fixMissingTitles($selected_type, $objects[$selected_type]);
        }

        $table_builder = $this->gui->ownership()->ownershipManagementTableBuilder(
            $this->user_id,
            $this->getLabelForObjectType($selected_type),
            $objects,
            $selected_type,
            $this,
            'listObjects'
        );

        $this->ctrl->setParameterByClass(self::class, 'type', $selected_type);

        if ($table_builder->getTable()->handleCommand()) {
            return;
        }

        $mt->setContent($table_builder->getTable()->render());
    }

    private function getLabelForObjectType(string $type): string
    {
        $obj_definition = $this->domain->objectDefinition();
        if ($obj_definition->isPlugin($type)) {
            return ilObjectPlugin::getPluginObjectByType($type)
                ->txt('obj_' . $type);
        }

        return $this->lng->txt('objs_' . $type);
    }

    protected function redirectParentCmd(int $ref_id, string $cmd): void
    {
        $tree = $this->domain->repositoryTree();
        $parent = $tree->getParentId($ref_id);
        $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $parent);
        $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'item_ref_id', $ref_id);
        $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'cmd', $cmd);
        $this->ctrl->redirectByClass(ilRepositoryGUI::class);
    }

    protected function redirectCmd(int $ref_id, string $class, ?string $cmd = null): void
    {
        $tree = $this->domain->repositoryTree();
        $obj_definition = $this->domain->objectDefinition();

        $node = $tree->getNodeData($ref_id);
        $gui_class = 'ilObj' . $obj_definition->getClassName($node['type']) . 'GUI';
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

    public function delete(int $id): void
    {
        $this->checkReadOnly();

        $this->redirectParentCmd(
            $id,
            'delete'
        );
    }

    public function show(int $id): void
    {
        $link = \ilLink::_getLink($id);
        $this->ctrl->redirectToURL($link);
    }

    public function move(int $id): void
    {
        $this->checkReadOnly();

        $this->redirectParentCmd(
            $id,
            'cut'
        );
    }

    public function export(int $id): void
    {
        $this->checkReadOnly();

        $this->redirectCmd(
            $id,
            ilExportGUI::class
        );
    }

    public function changeOwner(int $id): void
    {
        $this->checkReadOnly();

        $this->redirectCmd(
            $id,
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
