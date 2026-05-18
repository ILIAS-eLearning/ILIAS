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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\MimeType;
use ILIAS\Filesystem\Util\LegacyPathHelper;
use ILIAS\ResourceStorage\Preloader\SecureString;

/**
 * @deprecated Will be removed in ILIAS 10. Use ILIAS ResourceStorageService as replacement.
 */
class ilFileSystemTableGUI extends ilTable2GUI
{
    use SecureString;

    // This is just for those legacy classes which will be removed soon anyway.
    private Factory $ui_factory;
    private Renderer $ui_renderer;
    protected bool $has_multi = false;
    protected array $row_commands = [];
    protected string $cur_dir = '';
    protected string $relative_cur_dir;
    protected Filesystem $filesystem;

    /**
     * Constructor
     */
    public function __construct(
        protected ilFileSystemGUI $filesystem_gui,
        string $a_parent_cmd,
        string $a_cur_dir,
        protected string $cur_subdir,
        protected bool $label_enable,
        protected array $file_labels = [],
        protected string $label_header = "",
        ?array $a_commands = [],
        protected ?bool $post_dir_path = false,
        ?string $a_table_id = ""
    ) {
        global $DIC;
        $this->setId($a_table_id);
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        if ($a_cur_dir === '' || $a_cur_dir !== realpath($a_cur_dir)) {
            // The requested directory is not a valid canonical absolute path,
            // e.g. because its name contains non-UTF-8/special characters
            // created by an older ILIAS version (Mantis 45045). Fall back to
            // the managed base directory instead of throwing a fatal error
            // that breaks the whole page.
            $a_cur_dir = $this->filesystem_gui->getMainAbsoluteDir();
            $this->cur_subdir = '';
        }
        $this->filesystem = LegacyPathHelper::deriveFilesystemFrom($a_cur_dir);
        $this->relative_cur_dir = LegacyPathHelper::createRelativePath($a_cur_dir);
        $this->cur_dir = $a_cur_dir;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        parent::__construct($this->filesystem_gui, $a_parent_cmd);
        $this->setTitle($this->lng->txt("cont_files") . " " . $this->cur_subdir);

        $this->has_multi = false;

        foreach ((array) $a_commands as $i => $command) {
            if (!($command["single"] ?? false)) {
                // does also handle internal commands
                $this->addMultiCommand("extCommand_" . $i, $command["name"]);
                $this->has_multi = true;
            } else {
                $this->row_commands[] = [
                    "cmd" => "extCommand_" . $i,
                    "caption" => $command["name"],
                    "allow_dir" => $command["allow_dir"] ?? false,
                    "method" => $command["method"] ?? null,
                ];
            }
        }
        $this->addColumns();

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($this->filesystem_gui));
        $this->setRowTemplate(
            "tpl.directory_row.html",
            "components/ILIAS/Filesystem"
        );
        $this->setEnableTitle(true);
    }

    #[\Override]
    public function numericOrdering(string $a_field): bool
    {
        return $a_field === "size";
    }

    protected function prepareOutput(): void
    {
        $this->determineOffsetAndOrder(true);
        $this->setData($this->getEntries());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEntries(): array
    {
        $path = $this->relative_cur_dir;
        if ($this->filesystem->has($path)) {
            $entries = [];
            if ($this->cur_dir !== '') {
                $entries['..'] = [
                    'order_val' => -1,
                    'order_id' => -1,
                    'entry' => '..',
                    'type' => 'dir',
                    'subdir' => '',
                    'size' => 0
                ];
            }

            foreach ($this->filesystem->listContents($path) as $i => $content) {
                $basename = basename($content->getPath());
                $entries[$basename] = [
                    'order_val' => $i,
                    'order_id' => $i,
                    'entry' => $basename,
                    'type' => $content->isDir() ? 'dir' : 'file',
                    'subdir' => '',
                    'size' => $content->isFile() ? $this->filesystem->getSize($content->getPath(), 1)->inBytes() : 0
                ];
            }
        } else {
            $entries = [["type" => "dir", "entry" => ".."]];
        }
        $items = [];

        foreach ($entries as $e) {
            if ($e["entry"] === ".") {
                continue;
            }
            if ($e["entry"] === ".." && empty($this->cur_subdir)) {
                continue;
            }
            $cfile = (empty($this->cur_subdir))
                ? $e["entry"]
                : $this->cur_subdir . "/" . $e["entry"];

            if ($this->label_enable) {
                $label = (isset($this->file_labels[$cfile]) && is_array($this->file_labels[$cfile]))
                    ? implode(", ", $this->file_labels[$cfile])
                    : "";
            }

            $pref = ($e["type"] === "dir")
                ? ($this->getOrderDirection() !== "desc" ? "1_" : "9_")
                : "5_";
            $items[] = [
                "file" => $cfile,
                "entry" => $e["entry"],
                "type" => $e["type"],
                "label" => $label ?? '',
                "size" => $e["size"] ?? '',
                "name" => $pref . $e["entry"]
            ];
        }
        return $items;
    }

    public function addColumns(): void
    {
        if ($this->has_multi) {
            $this->setSelectAllCheckbox("file[]");
            $this->addColumn("", "", "1", true);
        }
        $this->addColumn("", "", "1", true); // icon

        $this->addColumn($this->lng->txt("cont_dir_file"), "name");
        $this->addColumn($this->lng->txt("cont_size"), "size");

        if ($this->label_enable) {
            $this->addColumn($this->label_header, "label");
        }

        if ($this->row_commands !== []) {
            $this->addColumn($this->lng->txt("actions"));
        }
    }

    private function isDoubleDotDirectory(array $entry): bool
    {
        return $entry['entry'] === '..';
    }

    /**
     * Fill table row
     */
    #[\Override]
    protected function fillRow(array $a_set): void
    {
        $hash = $this->post_dir_path
            ? md5((string) $a_set["file"])
            : md5((string) $a_set["entry"]);

        if ($this->has_multi) {
            if ($this->isDoubleDotDirectory($a_set)) {
                $this->tpl->touchBlock('no_checkbox');
            } else {
                $this->tpl->setVariable("CHECKBOX_ID", $hash);
            }
        }

        // label
        if ($this->label_enable) {
            $this->tpl->setCurrentBlock("Label");
            $this->tpl->setVariable("TXT_LABEL", $a_set["label"]);
            $this->tpl->parseCurrentBlock();
        }

        $this->ctrl->setParameter($this->parent_obj, "cdir", $this->cur_subdir);

        if ($a_set["type"] == "dir") {
            $this->tpl->setCurrentBlock("FileLink");
            $this->ctrl->setParameter($this->parent_obj, "newdir", $a_set["entry"]);
            $this->ctrl->setParameter($this->parent_obj, "resetoffset", 1);
            $this->tpl->setVariable(
                "LINK_FILENAME",
                $this->ctrl->getLinkTarget($this->parent_obj, "listFiles")
            );
            $this->ctrl->setParameter($this->parent_obj, "newdir", "");
            $this->tpl->setVariable("TXT_FILENAME", $a_set["entry"]);
            $this->tpl->parseCurrentBlock();

            $this->tpl->setVariable(
                "ICON",
                "<img src=\"" .
                ilUtil::getImagePath("standard/icon_cat.svg") . "\">"
            );
            $this->ctrl->setParameter($this->parent_obj, "resetoffset", "");
        } else {
            $this->tpl->setCurrentBlock("File");
            $this->tpl->setVariable("TXT_FILENAME2", $this->secure($a_set["entry"]));
            $this->tpl->parseCurrentBlock();
        }

        if ($a_set["type"] != "dir") {
            $this->tpl->setVariable("TXT_SIZE", ilUtil::formatSize($a_set["size"]));
        }

        // single item commands
        if ($this->row_commands !== [] && !($a_set["type"] === "dir" && $a_set["entry"] === "..")) {
            $actions = [];

            foreach ($this->row_commands as $rcom) {
                if ($rcom["allow_dir"] || $a_set["type"] !== "dir") {
                    $file_path = $this->cur_dir . $a_set['entry'];
                    if (
                        $rcom['method'] !== ilFileSystemGUI::CMD_UNZIP_FILE
                        || ($rcom['method'] === ilFileSystemGUI::CMD_UNZIP_FILE && MimeType::getMimeType($file_path) === "application/zip")
                    ) {
                        $this->ctrl->setParameter($this->parent_obj, "fhsh", $hash);
                        $url = $this->ctrl->getLinkTarget($this->parent_obj, $rcom["cmd"]);
                        $this->ctrl->setParameter($this->parent_obj, "fhsh", "");

                        $actions[] = $this->ui_factory->link()->standard($rcom["caption"], $url);
                    }
                }
            }

            $dropdown = $this->ui_factory->dropdown()->standard($actions);
            $this->tpl->setVariable("ACTIONS", $this->ui_renderer->render($dropdown));
        }
    }
}
