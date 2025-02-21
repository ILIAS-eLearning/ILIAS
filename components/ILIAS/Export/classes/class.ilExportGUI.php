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

use ILIAS\Data\ObjectId;
use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\DI\UIServices as ilUIServices;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\CollectionInterface as ilExportHandlerConsumerExportOptionCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\HandlerInterface as ilExportHandlerConsumerExportOptionInterface;
use ILIAS\Export\ExportHandler\Factory as ilExportHandler;
use ILIAS\HTTP\Services as ilHTTPServices;
use ILIAS\Refinery\Factory as ilRefineryFactory;

/**
 * Export User Interface Class
 * @author       Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilExportGUI:
 */
class ilExportGUI
{
    public const CMD_LIST_EXPORT_FILES = "listExportFiles";
    public const CMD_EXPORT_XML = "createXmlExportFile";
    protected const CMD_SAVE_ITEM_SELECTION = "saveItemSelection";
    protected const CMD_EXPORT_OPTION_PREFIX = "exportOption";

    protected ilExportHandlerConsumerExportOptionCollectionInterface $export_options;
    protected ilUIServices $ui_services;
    protected ilHTTPServices $http;
    protected ilRefineryFactory $refinery;
    protected ilObjUser $il_user;
    protected ilLanguage $lng;
    protected ilObject $obj;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilAccessHandler $access;
    protected ilErrorHandling $error;

    protected ilToolbarGUI $toolbar;
    protected ilObjectDefinition $obj_definition;
    protected ilTree $tree;
    protected ilExportHandler $export_handler;
    protected ilExportHandlerConsumerContextInterface $context;
    protected ilDataFactory $data_factory;
    protected object $parent_gui;

    public function __construct(object $a_parent_gui, ?ilObject $a_main_obj = null)
    {
        global $DIC;
        $this->ui_services = $DIC->ui();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->il_user = $DIC->user();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("exp");
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->error = $DIC['ilErr'];
        $this->toolbar = $DIC->toolbar();
        $this->parent_gui = $a_parent_gui;
        $this->obj_definition = $DIC['objDefinition'];
        $this->tree = $DIC->repositoryTree();
        $this->obj = $a_main_obj ?? $a_parent_gui->getObject();
        $this->export_handler = new ilExportHandler();
        $this->context = $this->export_handler->consumer()->context()->handler($this, $this->obj);
        $this->export_options = $this->export_handler->consumer()->exportOption()->collection();
        $this->data_factory = new ilDataFactory();
        $this->initExportOptions();
        $this->enableStandardXMLExport();
    }

    public function executeCommand(): void
    {
        // this should work (at least) for repository objects
        if (method_exists($this->obj, 'getRefId') and $this->obj->getRefId()) {
            if (!$this->access->checkAccess('write', '', $this->obj->getRefId())) {
                $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->WARNING);
            }
            // check export activation of container
            $exp_limit = new ilExportLimitation();
            if ($this->obj_definition->isContainer(ilObject::_lookupType($this->obj->getRefId(), true)) &&
                $exp_limit->getLimitationMode() == ilExportLimitation::SET_EXPORT_DISABLED) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exp_error_disabled"));
                return;
            }
        }
        $cmd = $this->ctrl->getCmd(self::CMD_LIST_EXPORT_FILES);
        if (str_starts_with($cmd, self::CMD_EXPORT_OPTION_PREFIX)) {
            foreach ($this->export_options as $export_option) {
                if ($cmd === $this->builtExportOptionCommand($export_option)) {
                    $export_option->onExportOptionSelected($this->context);
                }
            }
        }
        switch ($cmd) {
            case self::CMD_EXPORT_XML:
                $this->createXMLExportFile();
                break;
            case self::CMD_SAVE_ITEM_SELECTION:
                $this->saveItemSelection();
                break;
            case self::CMD_LIST_EXPORT_FILES:
            default:
                $this->displayExportFiles();
                break;
        }
    }

    /**
     * @depricated
     */
    public function addFormat(): void
    {

    }

    public function listExportFiles(): void
    {
        $this->displayExportFiles();
    }

    /**
     * @depricated
     */
    public function getFormats(): array
    {
        return [];
    }

    final protected function initExportOptionsFromPost(): array
    {
        $options = [];
        if ($this->http->wrapper()->post()->has('cp_options')) {
            $custom_transformer = $this->refinery->custom()->transformation(
                function ($array) {
                    return $array;
                }
            );
            $options = $this->http->wrapper()->post()->retrieve(
                'cp_options',
                $custom_transformer
            );
        }
        return $options;
    }

    final protected function builtExportOptionCommand(ilExportHandlerConsumerExportOptionInterface $export_option): string
    {
        return self::CMD_EXPORT_OPTION_PREFIX . $export_option->getExportOptionId();
    }

    final protected function enableStandardXMLExport(): void
    {
        $this->export_options = $this->export_options->withElement(new ilExportExportOptionXML());
    }

    final protected function initExportOptions(): void
    {
        $export_options = $this->export_handler->consumer()->exportOption()->allExportOptions();
        foreach ($export_options as $export_option) {
            if (
                in_array($this->obj->getType(), $export_option->getSupportedRepositoryObjectTypes()) and
                $export_option->isObjectSupported($this->data_factory->objId($this->obj->getId()))
            ) {
                $this->export_options = $this->export_options->withElement($export_option);
            }
        }
    }

    final protected function displayExportFiles(): void
    {
        if ($this->export_options->count() === 0) {
            return;
        }
        $table = $this->export_handler->table()->handler()
            ->withExportOptions($this->export_options)
            ->withContext($this->context);
        $table->handleCommands();
        $infos = [];
        foreach ($this->export_options as $export_option) {
            $infos[$export_option->getLabel()] = $this->ctrl->getLinkTarget($this, $this->builtExportOptionCommand($export_option));
        }
        if (count($infos) === 1) {
            $this->toolbar->addComponent($this->ui_services->factory()->button()->standard(
                sprintf($this->lng->txt("exp_export_single_option"), array_keys($infos)[0]),
                array_values($infos)[0]
            ));
        }
        if (count($infos) > 1) {
            $links = [];
            foreach ($infos as $label => $link) {
                $links[] = $this->ui_services->factory()->link()->standard($label, $link);
            }
            $this->toolbar->addComponent(
                $this->ui_services->factory()->dropdown()->standard($links)
                    ->withLabel($this->lng->txt("exp_export_dropdown"))
            );
        }
        $this->tpl->setContent($table->getHTML());
    }

    final protected function createXMLExportFile(): void
    {
        if ($this->parent_gui instanceof ilContainerGUI) {
            $this->showItemSelection();
            return;
        }
        $this->createXMLExport();
        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt("exp_file_created"),
            true
        );
        $this->ctrl->redirect($this, self::CMD_LIST_EXPORT_FILES);
    }

    /**
     * Show container item selection table
     */
    final protected function showItemSelection(): void
    {
        $this->tpl->addJavaScript('assets/js/ilContainer.js');
        $this->tpl->setVariable('BODY_ATTRIBUTES', 'onload="ilDisableChilds(\'cmd\');"');
        $table = new ilExportSelectionTableGUI($this, self::CMD_LIST_EXPORT_FILES, $this->export_handler);
        $table->parseContainer($this->parent_gui->getObject()->getRefId());
        $this->tpl->setContent($table->getHTML());
    }

    final protected function saveItemSelection(): void
    {
        // check export limitation
        $cp_options = $this->initExportOptionsFromPost();
        $exp_limit = new ilExportLimitation();
        try {
            $exp_limit->checkLimitation(
                $this->parent_gui->getObject()->getRefId(),
                $cp_options
            );
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $e->getMessage());
            $this->showItemSelection();
            return;
        }
        // create export
        $this->createXMLContainerExport();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('export_created'), true);
        $this->ctrl->redirect($this, self::CMD_LIST_EXPORT_FILES);
    }

    final protected function createXMLExport()
    {
        $manager = $this->export_handler->manager()->handler();
        $export_info = $manager->getExportInfo(
            $this->data_factory->objId($this->obj->getId()),
            time()
        );
        $element = $manager->createExport(
            $this->il_user->getId(),
            $export_info,
            ""
        );
    }

    final protected function createXMLContainerExport()
    {
        $tree_nodes = $this->tree->getSubTree($this->tree->getNodeData($this->parent_gui->getObject()->getRefId()));
        $post_export_options = $this->initExportOptionsFromPost();
        $eo = ilExportOptions::newInstance(ilExportOptions::allocateExportId());
        $eo->addOption(ilExportOptions::KEY_ROOT, 0, 0, $this->obj->getId());
        $items_selected = $eo->addOptions(
            $this->parent_gui->getObject()->getRefId(),
            $this->obj_definition,
            $this->access,
            $tree_nodes,
            $post_export_options
        );
        $eo->read();
        $ref_ids_export = [$this->parent_gui->getObject()->getRefId()];
        $ref_ids_all = [$this->parent_gui->getObject()->getRefId()];
        $tree_ref_ids = array_map(function ($node) { return (int) $node['ref_id']; }, $tree_nodes);
        $post_ref_ids = array_map(function ($key) {return (int) $key; }, array_keys($post_export_options));
        $valid_ref_ids = array_intersect($post_ref_ids, $tree_ref_ids);
        foreach ($valid_ref_ids as $ref_id) {
            $info = $post_export_options[$ref_id];
            $export_option_id = (int) $info["type"];
            if (
                $export_option_id === ilExportOptions::EXPORT_OMIT ||
                !$this->obj_definition->allowExport(ilObject::_lookupType($ref_id, true)) ||
                !$this->access->checkAccess('write', '', $ref_id)
            ) {
                continue;
            }
            if ($export_option_id === ilExportOptions::EXPORT_BUILD) {
                $ref_ids_export[] = $ref_id;
            }
            if (
                $export_option_id === ilExportOptions::EXPORT_BUILD ||
                $export_option_id === ilExportOptions::EXPORT_EXISTING
            ) {
                $ref_ids_all[] = $ref_id;
            }
        }
        $manager = $this->export_handler->manager()->handler();
        if (count($ref_ids_all) === 1) {
            $export_info = $manager->getExportInfo(
                $this->data_factory->objId($this->obj->getId()),
                time()
            );
            $element = $manager->createExport(
                $this->il_user->getId(),
                $export_info,
                ""
            );
        }
        if (count($ref_ids_all) > 1) {
            $obj_ids_export = array_map(function (int $ref_id) { return ilObject::_lookupObjId($ref_id); }, $ref_ids_export);
            $obj_ids_all = array_map(function (int $ref_id) { return ilObject::_lookupObjId($ref_id); }, $ref_ids_all);
            $object_id_collection_builder = $manager->getObjectIdCollectioBuilder();
            foreach ($obj_ids_all as $obj_id) {
                $object_id_collection_builder = $object_id_collection_builder->addObjectId(
                    $this->data_factory->objId($obj_id),
                    in_array($obj_id, $obj_ids_export)
                );
            }
            $container_export_info = $manager->getContainerExportInfo(
                $this->data_factory->objId($obj_ids_all[0]),
                $object_id_collection_builder->getCollection()
            );
            $element = $manager->createContainerExport($this->il_user->getId(), $container_export_info);
        }
        $eo->delete();
    }
}
