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

use ILIAS\DI\Container;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\Data\DataSize;
use ILIAS\ResourceStorage\Revision\RevisionStatus;

/**
 * Class ilFileVersionsTableGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileVersionsTableGUI extends ilTable2GUI
{
    private Container $dic;
    private int $current_version;
    private \ilObjFile $file;
    private \ILIAS\ResourceStorage\Services $irss;
    private bool $current_version_is_draft;
    private int $amount_of_versions;
    protected \ILIAS\DI\UIServices $ui;
    protected bool $has_been_migrated = false;

    /**
     * @var Modal[]
     */
    protected array $modals = [];

    /**
     * ilFileVersionsTableGUI constructor.
     */
    public function __construct(
        ilFileVersionsGUI $calling_gui_class,
        string $a_parent_cmd = ilFileVersionsGUI::CMD_DEFAULT
    ) {
        global $DIC;
        $this->dic = $DIC;
        $this->ui = $DIC->ui();
        $this->irss = $DIC->resourceStorage();
        $this->setId(self::class);
        parent::__construct($calling_gui_class, $a_parent_cmd, "");
        $this->file = $calling_gui_class->getFile();
        $this->current_version = $this->file->getVersion(true);
        $rid = $this->irss->manage()->find(
            $this->file->getResourceId()
        );
        $revision = $this->irss->manage()->getCurrentRevisionIncludingDraft(
            $rid
        );
        $this->amount_of_versions = count(
            $this->irss->manage()->getResource($rid)->getAllRevisionsIncludingDraft()
        );
        $this->current_version_is_draft = $revision->getStatus() === RevisionStatus::DRAFT;

        // General
        $this->setPrefix("versions");
        $this->dic->language()->loadLanguageModule('file');

        // Appearance
        $this->setRowTemplate("tpl.file_version_row.html", "Modules/File");
        $this->setLimit(9999);
        $this->setEnableHeader(true);
        $this->disable("footer");
        $this->setTitle($this->dic->language()->txt("versions"));

        // Form

        $this->setFormAction($this->dic->ctrl()->getFormAction($calling_gui_class));
        $this->setSelectAllCheckbox("hist_id[]");
        //TODO: Use ilFileVersionsGUI::CMD_RENDER_DELETE_SELECTED_VERSIONS_MODAL instead of ilFileVersionsGUI::CMD_DELETE_VERSIONS as soon as new table gui is introduced.
        // ilFileVersionsGUI::CMD_DELETE_VERSIONS and its deprecated ilConfirmationGUI are only needed because the old ilTable2GUI doesn't support calling modals from its MultiCommands
        $this->addMultiCommand(ilFileVersionsGUI::CMD_DELETE_VERSIONS, $this->dic->language()->txt("delete"));
        $this->addMultiCommand(
            ilFileVersionsGUI::CMD_ROLLBACK_VERSION,
            $this->dic->language()->txt("file_rollback")
        );

        // Columns
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->dic->language()->txt("version"), "", "auto");
        $this->addColumn($this->dic->language()->txt("date"));
        $this->addColumn($this->dic->language()->txt("file_uploaded_by"));
        $this->addColumn($this->dic->language()->txt("filename"));
        $this->addColumn($this->dic->language()->txt("versionname"));
        $this->addColumn($this->dic->language()->txt("filesize"), "", "", false);
        $this->addColumn($this->dic->language()->txt("type"));
        $this->addColumn($this->dic->language()->txt("action"));
        $this->addColumn("", "", "1");

        $this->initData();
    }

    private function initData(): void
    {
        $versions = [];
        foreach ($this->file->getVersions() as $version) {
            $versions[] = $version->getArrayCopy();
        }
        usort($versions, static fn(array $i1, array $i2): int => $i2['version'] - $i1['version']);

        $this->setData($versions);
        $this->setMaxCount(is_array($versions) ? count($versions) : 0);
    }

    protected function fillRow(array $a_set): void
    {
        $action_entries = [];
        $hist_id = $a_set["hist_entry_id"];

        // split params
        $filename = $a_set["filename"];
        $version = $a_set["version"];
        $rollback_version = $a_set["rollback_version"];
        $rollback_user_id = $a_set["rollback_user_id"];

        // get user name
        $name = ilObjUser::_lookupName($a_set["user_id"]);
        $username = trim($name["title"] . " " . $name["firstname"] . " " . $name["lastname"]);

        // get file size
        $data_size = new DataSize(
            (int) ($a_set["size"] ?? 0),
            DataSize::KB
        );
        $filesize = (string) $data_size;

        // get action text
        $action = $this->dic->language()->txt(
            "file_version_" . $a_set["action"]
        ); // create, replace, new_version, rollback
        if ($a_set["action"] == "rollback") {
            $name = ilObjUser::_lookupName($rollback_user_id);
            $rollback_username = trim($name["title"] . " " . $name["firstname"] . " " . $name["lastname"]);
            $action = sprintf($action, $rollback_version, $rollback_username);
        }

        // get download link
        $this->dic->ctrl()->setParameter($this->parent_obj, ilFileVersionsGUI::HIST_ID, $hist_id);
        $link = $this->dic->ctrl()->getLinkTarget($this->parent_obj, ilFileVersionsGUI::CMD_DOWNLOAD_VERSION);

        // build actions
        $pseudo_modal = $this->ui->factory()->modal()->interruptive('', '', '')->withAsyncRenderUrl(
            $this->ctrl->getLinkTargetByClass(
                ilFileVersionsGUI::class,
                ilFileVersionsGUI::CMD_RENDER_DELETE_SELECTED_VERSIONS_MODAL,
                null,
                true
            )
        );

        $this->modals[] = $pseudo_modal;
        $buttons = $this->dic->ui()->factory()->button();


        if (!$this->current_version_is_draft) {
            $action_entries['delete'] = $buttons
                ->shy(
                    $this->dic->language()->txt("delete"),
                    ''
                )
                ->withOnClick(
                    $pseudo_modal->getShowSignal()
                );


            if ($this->current_version !== (int) $version) {
                $action_entries['file_rollback'] = $buttons
                    ->shy(
                        $this->dic->language()->txt("file_rollback"),
                        $this->dic->ctrl()->getLinkTarget($this->parent_obj, ilFileVersionsGUI::CMD_ROLLBACK_VERSION)
                    );
            } elseif ($this->amount_of_versions > 1) {
                $action_entries['unpublish'] = $buttons
                    ->shy(
                        $this->dic->language()->txt("file_unpublish"),
                        $this->dic->ctrl()->getLinkTarget($this->parent_obj, ilFileVersionsGUI::CMD_UNPUBLISH)
                    );
            }
        } elseif ($this->current_version === (int) $version) {
            $action_entries['publish'] = $buttons
                ->shy(
                    $this->dic->language()->txt("file_publish"),
                    $this->dic->ctrl()->getLinkTarget($this->parent_obj, ilFileVersionsGUI::CMD_PUBLISH)
                );
        }


        $actions = $this->dic->ui()->renderer()->render(
            $this->dic->ui()->factory()->dropdown()
                      ->standard($action_entries)
                      ->withLabel($this->lng->txt('actions'))
        );

        // reset history parameter
        $this->dic->ctrl()->setParameter($this->parent_obj, ilFileVersionsGUI::HIST_ID, "");

        // fill template
        $this->tpl->setVariable("TXT_VERSION", $version);
        $this->tpl->setVariable(
            "TXT_DATE",
            ilDatePresentation::formatDate(new ilDateTime($a_set['date'], IL_CAL_DATETIME))
        );
        $this->tpl->setVariable("TXT_UPLOADED_BY", $username);
        $this->tpl->setVariable("DL_LINK", $link);
        $this->tpl->setVariable("TXT_FILENAME", $filename);
        $this->tpl->setVariable("TXT_VERSIONNAME", $a_set['title']);
        $this->tpl->setVariable("TXT_FILESIZE", $data_size);

        // columns depending on confirmation
        $this->tpl->setCurrentBlock("version_selection");
        $this->tpl->setVariable("OBJ_ID", $hist_id);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("version_txt_actions");
        $this->tpl->setVariable("TXT_ACTION", $action);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("version_actions");

        $this->tpl->setVariable("ACTIONS", $actions);

        $this->tpl->parseCurrentBlock();
    }

    /**
     * Enables rendering modals OUTSIDE of the table. This is required because
     * this table uses multi-actions, which will render the table inside a form.
     * Since modals can contain forms as well, this would lead to invalid HTML
     * markup.
     */
    public function getHTML(): string
    {
        return parent::getHTML() . $this->ui->renderer()->render($this->modals);
    }
}
