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
 
use ILIAS\Refinery\Factory as Factory;
use ILIAS\HTTP\Services as Services;

/**
 * Export User Interface Class
 * @author       Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilExportGUI:
 */
class ilExportGUI
{
    protected Factory $refinery;
    protected Services $http;
    protected array $formats = array();
    protected array $custom_columns = array();
    protected array $custom_multi_commands = array();

    private object $parent_gui;
    protected ilObject $obj;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilAccessHandler $access;
    protected ilErrorHandling $error;
    protected ilToolbarGUI $toolbar;
    protected ilObjectDefinition $objDefinition;
    protected ilTree $tree;


    public function __construct(object $a_parent_gui, ?ilObject $a_main_obj = null)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("exp");

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->error = $DIC['ilErr'];
        $this->toolbar = $DIC->toolbar();
        $this->parent_gui = $a_parent_gui;
        $this->objDefinition = $DIC['objDefinition'];
        $this->tree = $DIC->repositoryTree();
        if ($a_main_obj == null) {
            $this->obj = $a_parent_gui->getObject();
        } else {
            $this->obj = $a_main_obj;
        }
    }

    protected function initFileIdentifierFromQuery() : string
    {
        if ($this->http->wrapper()->query()->has('file')) {
            return $this->http->wrapper()->query()->retrieve(
                'file',
                $this->refinery->kindlyTo()->string()
            );
        }
        return '';
    }

    protected function initFileIdentifiersFromPost() : array
    {
        if ($this->http->wrapper()->post()->has('file')) {
            return $this->http->wrapper()->post()->retrieve(
                'file',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->string()
                )
            );
        }
        return [];
    }

    protected function initFormatFromPost() : string
    {
        if ($this->http->wrapper()->post()->has('format')) {
            return $this->http->wrapper()->post()->retrieve(
                'format',
                $this->refinery->kindlyTo()->string()
            );
        }
        return '';
    }

    protected function initExportOptionsFromPost() : array
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


    protected function buildExportTableGUI() : ilExportTableGUI
    {
        return new ilExportTableGUI($this, "listExportFiles", $this->obj);
    }

    protected function getParentGUI() : object
    {
        return $this->parent_gui;
    }

    public function addFormat(
        string $a_key,
        string $a_txt = "",
        object $a_call_obj = null,
        string $a_call_func = ""
    ) : void {
        if ($a_txt == "") {
            $a_txt = $this->lng->txt("exp_" . $a_key);
        }
        $this->formats[] = array(
            "key" => $a_key,
            "txt" => $a_txt,
            "call_obj" => $a_call_obj,
            "call_func" => $a_call_func
        );
    }

    public function getFormats() : array
    {
        return $this->formats;
    }

    public function addCustomColumn(string $a_txt, object $a_obj, string $a_func) : void
    {
        $this->custom_columns[] = array("txt" => $a_txt,
                                        "obj" => $a_obj,
                                        "func" => $a_func
        );
    }

    public function addCustomMultiCommand(string $a_txt, object $a_obj, string $a_func) : void
    {
        $this->custom_multi_commands[] = array("txt" => $a_txt,
                                               "obj" => $a_obj,
                                               "func" => $a_func
        );
    }

    public function getCustomMultiCommands() : array
    {
        return $this->custom_multi_commands;
    }

    public function getCustomColumns() : array
    {
        return $this->custom_columns;
    }

    public function executeCommand() : void
    {
        // this should work (at least) for repository objects
        if (method_exists($this->obj, 'getRefId') and $this->obj->getRefId()) {
            if (!$this->access->checkAccess('write', '', $this->obj->getRefId())) {
                $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->WARNING);
            }

            // check export activation of container
            $exp_limit = new ilExportLimitation();
            if ($this->objDefinition->isContainer(ilObject::_lookupType($this->obj->getRefId(), true)) &&
                $exp_limit->getLimitationMode() == ilExportLimitation::SET_EXPORT_DISABLED) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exp_error_disabled"));
                return;
            }
        }

        $cmd = $this->ctrl->getCmd("listExportFiles");

        switch ($cmd) {
            case "listExportFiles":
                $this->$cmd();
                break;

            default:
                if (substr($cmd, 0, 7) == "create_") {
                    $this->createExportFile();
                } elseif (substr($cmd, 0, 6) == "multi_") {    // custom multi command
                    $this->handleCustomMultiCommand();
                } else {
                    $this->$cmd();
                }
                break;
        }
    }

    public function listExportFiles() : void
    {
        $button = ilSubmitButton::getInstance();

        // creation buttons
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        if (count($this->getFormats()) > 1) {
            // type selection
            $options = [];
            foreach ($this->getFormats() as $f) {
                $options[$f["key"]] = $f["txt"];
            }
            $si = new ilSelectInputGUI($this->lng->txt("type"), "format");
            $si->setOptions($options);
            $this->toolbar->addInputItem($si, true);

            $button->setCaption("exp_create_file");
            $button->setCommand("createExportFile");
        } else {
            $format = $this->getFormats();
            $format = $format[0];

            $button->setCaption($this->lng->txt("exp_create_file") . " (" . $format["txt"] . ")", false);
            $button->setCommand("create_" . $format["key"]);
        }

        $this->toolbar->addButtonInstance($button);

        $table = $this->buildExportTableGUI();
        $table->setSelectAllCheckbox("file");
        foreach ($this->getCustomColumns() as $c) {
            $table->addCustomColumn($c["txt"], $c["obj"], $c["func"]);
        }
        foreach ($this->getCustomMultiCommands() as $c) {
            $table->addCustomMultiCommand($c["txt"], "multi_" . $c["func"]);
        }
        $this->tpl->setContent($table->getHTML());
    }

    public function createExportFile() : void
    {
        if ($this->ctrl->getCmd() == "createExportFile") {
            $format = $this->initFormatFromPost();
        } else {
            $format = substr($this->ctrl->getCmd(), 7);
        }
        foreach ($this->getFormats() as $f) {
            if ($f["key"] == $format) {
                if (is_object($f["call_obj"])) {
                    $f["call_obj"]->{$f["call_func"]}();
                } elseif ($this->getParentGUI() instanceof ilContainerGUI) {
                    $this->showItemSelection();
                    return;
                } elseif ($format == "xml") {        // standard procedure
                    $exp = new ilExport();
                    $exp->exportObject($this->obj->getType(), $this->obj->getId());
                }
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("exp_file_created"), true);
        $this->ctrl->redirect($this, "listExportFiles");
    }

    /**
     * Confirm file deletion
     */
    public function confirmDeletion() : void
    {
        $files = $this->initFileIdentifiersFromPost();
        if (!count($files)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listExportFiles");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("exp_really_delete"));
            $cgui->setCancel($this->lng->txt("cancel"), "listExportFiles");
            $cgui->setConfirm($this->lng->txt("delete"), "delete");

            foreach ($files as $i) {
                if (strpos($i, ':') !== false) {
                    $iarr = explode(":", $i);
                    $filename = $iarr[1];
                } else {
                    $filename = $i;
                }
                $cgui->addItem("file[]", $i, $filename);
            }
            $this->tpl->setContent($cgui->getHTML());
        }
    }

    public function delete() : void
    {
        $files = $this->initFileIdentifiersFromPost();
        foreach ($files as $file) {
            $file = explode(":", $file);

            $file[1] = basename($file[1]);

            $export_dir = ilExport::_getExportDirectory(
                $this->obj->getId(),
                str_replace("..", "", $file[0]),
                $this->obj->getType()
            );

            $exp_file = $export_dir . "/" . str_replace("..", "", $file[1]);
            $exp_dir = $export_dir . "/" . substr($file[1], 0, strlen($file[1]) - 4);
            if (is_file($exp_file)) {
                unlink($exp_file);
            }
            if (is_dir($exp_dir)) {
                ilFileUtils::delDir($exp_dir);
            }

            // delete entry in database
            $info = new ilExportFileInfo($this->obj->getId(), $file[0], $file[1]);
            $info->delete();
        }
        $this->ctrl->redirect($this, "listExportFiles");
    }

    /**
     * Download file
     */
    public function download() : void
    {
        $file = $this->initFileIdentifierFromQuery();
        if (!$file) {
            $this->ctrl->redirect($this, "listExportFiles");
        }

        $file = explode(":", trim($file));
        $export_dir = ilExport::_getExportDirectory(
            $this->obj->getId(),
            str_replace("..", "", $file[0]),
            $this->obj->getType()
        );

        $file[1] = basename($file[1]);

        ilFileDelivery::deliverFileLegacy(
            $export_dir . "/" . $file[1],
            $file[1]
        );
    }

    public function handleCustomMultiCommand() : void
    {
        $cmd = substr($this->ctrl->getCmd(), 6);
        foreach ($this->getCustomMultiCommands() as $c) {
            if ($c["func"] == $cmd) {
                $c["obj"]->{$c["func"]}($this->initFileIdentifiersFromPost());
            }
        }
    }

    /**
     * Show container item selection table
     */
    protected function showItemSelection() : void
    {
        $this->tpl->addJavaScript('./Services/CopyWizard/js/ilContainer.js');
        $this->tpl->setVariable('BODY_ATTRIBUTES', 'onload="ilDisableChilds(\'cmd\');"');

        $table = new ilExportSelectionTableGUI($this, 'listExportFiles');
        $table->parseContainer($this->getParentGUI()->getObject()->getRefId());
        $this->tpl->setContent($table->getHTML());
    }

    protected function saveItemSelection() : void
    {
        $eo = ilExportOptions::newInstance(ilExportOptions::allocateExportId());
        $eo->addOption(ilExportOptions::KEY_ROOT, 0, 0, $this->obj->getId());

        $cp_options = $this->initExportOptionsFromPost();

        // check export limitation
        $exp_limit = new ilExportLimitation();
        try {
            $exp_limit->checkLimitation(
                $this->getParentGUI()->getObject()->getRefId(),
                $cp_options
            );
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $e->getMessage());
            $this->showItemSelection();
            return;
        }

        $items_selected = false;
        foreach ($this->tree->getSubTree($root = $this->tree->getNodeData($this->getParentGUI()->getObject()->getRefId())) as $node) {
            if ($node['type'] === 'rolf') {
                continue;
            }
            if ($node['ref_id'] == $this->getParentGUI()->getObject()->getRefId()) {
                $eo->addOption(
                    ilExportOptions::KEY_ITEM_MODE,
                    (int) $node['ref_id'],
                    (int) $node['obj_id'],
                    ilExportOptions::EXPORT_BUILD
                );
                continue;
            }
            // no export available or no access
            if (!$this->objDefinition->allowExport($node['type']) || !$this->access->checkAccess(
                'write',
                '',
                (int) $node['ref_id']
            )) {
                $eo->addOption(
                    ilExportOptions::KEY_ITEM_MODE,
                    (int) $node['ref_id'],
                    (int) $node['obj_id'],
                    ilExportOptions::EXPORT_OMIT
                );
                continue;
            }

            $mode = $cp_options[$node['ref_id']]['type'] ?? ilExportOptions::EXPORT_OMIT;
            $eo->addOption(
                ilExportOptions::KEY_ITEM_MODE,
                (int) $node['ref_id'],
                (int) $node['obj_id'],
                $mode
            );
            if ($mode != ilExportOptions::EXPORT_OMIT) {
                $items_selected = true;
            }
        }

        if ($items_selected) {
            // TODO: move this to background soap
            $eo->read();
            $exp = new ilExport();
            foreach ($eo->getSubitemsForCreation($this->obj->getRefId()) as $ref_id) {
                $obj_id = ilObject::_lookupObjId($ref_id);
                $type = ilObject::_lookupType($obj_id);
                $exp->exportObject($type, $obj_id);
            }
            // Fixme: there is a naming conflict between the container settings xml and the container subitem xml.
            sleep(1);
            // Export container
            $cexp = new ilExportContainer($eo);
            $cexp->exportObject($this->obj->getType(), $this->obj->getId());
        } else {
            $exp = new ilExport();
            $exp->exportObject($this->obj->getType(), $this->obj->getId());
        }

        // Delete export options
        $eo->delete();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('export_created'), true);
        $this->ctrl->redirect($this, "listExportFiles");
    }
}
