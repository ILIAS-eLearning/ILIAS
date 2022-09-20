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

use ILIAS\FileUpload\MimeType;
use ILIAS\Filesystem\Util\LegacyPathHelper;

/**
 * @deprecated $
 */
class ilFileSystemTableGUI extends ilTable2GUI
{
    protected bool $has_multi = false;
    protected array $row_commands = [];
    protected bool $label_enable = false;
    protected string $label_header = "";
    protected string $cur_dir = '';
    protected string $cur_subdir = '';
    protected string $relative_cur_dir;
    protected ?bool $post_dir_path = null;
    protected array $file_labels = [];
    protected \ILIAS\Filesystem\Filesystem $filesystem;
    protected ilFileSystemGUI $filesystem_gui;

    /**
     * Constructor
     */
    public function __construct(
        ilFileSystemGUI $a_parent_obj,
        string $a_parent_cmd,
        string $a_cur_dir,
        string $a_cur_subdir,
        bool $a_label_enable,
        ?array $a_file_labels = [],
        ?string $a_label_header = "",
        ?array $a_commands = [],
        ?bool $a_post_dir_path = false,
        ?string $a_table_id = ""
    ) {
        global $DIC;
        $this->setId($a_table_id);
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        if ($a_cur_dir !== realpath($a_cur_dir)) {
            throw new \InvalidArgumentException('$a_cur_dir must be a absolute path');
        }
        $this->filesystem = LegacyPathHelper::deriveFilesystemFrom($a_cur_dir);
        $this->relative_cur_dir = LegacyPathHelper::createRelativePath($a_cur_dir);
        $this->cur_dir = $a_cur_dir;
        $this->cur_subdir = $a_cur_subdir;
        $this->label_enable = $a_label_enable;
        $this->label_header = $a_label_header;
        $this->file_labels = $a_file_labels;
        $this->post_dir_path = $a_post_dir_path;
        $this->filesystem_gui = $a_parent_obj;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($this->lng->txt("cont_files") . " " . $this->cur_subdir);

        $this->has_multi = false;

        foreach ((array) $a_commands as $i => $command) {
            if (!($command["single"] ?? false)) {
                // does also handle internal commands
                $this->addMultiCommand("extCommand_" . $i, $command["name"]);
                $this->has_multi = true;
            } else {
                $this->row_commands[] = array(
                    "cmd" => "extCommand_" . $i,
                    "caption" => $command["name"],
                    "allow_dir" => $command["allow_dir"] ?? ""
                );
            }
        }
        $this->addColumns();

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.directory_row.html",
            "Services/FileSystem"
        );
        $this->setEnableTitle(true);
    }

    public function numericOrdering(string $a_field): bool
    {
        if ($a_field == "size") {
            return true;
        }
        return false;
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
        if ($this->filesystem->has($this->relative_cur_dir)) {
            $entries = [];
            foreach ($this->filesystem->listContents($this->relative_cur_dir) as $i => $content) {
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
            $entries = array(array("type" => "dir", "entry" => ".."));
        }
        $items = array();

        foreach ($entries as $e) {
            if (($e["entry"] == ".") || ($e["entry"] == ".." && empty($this->cur_subdir))) {
                continue;
            }
            $cfile = (!empty($this->cur_subdir))
                ? $this->cur_subdir . "/" . $e["entry"]
                : $e["entry"];

            if ($this->label_enable) {
                $label = (isset($this->file_labels[$cfile]) && is_array($this->file_labels[$cfile]))
                    ? implode(", ", $this->file_labels[$cfile])
                    : "";
            }

            $pref = ($e["type"] == "dir")
                ? ($this->getOrderDirection() != "desc" ? "1_" : "9_")
                : "5_";
            $items[] = array("file" => $cfile,
                             "entry" => $e["entry"],
                             "type" => $e["type"],
                             "label" => $label ?? '',
                             "size" => $e["size"] ?? '',
                             "name" => $pref . $e["entry"]
            );
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

        if (sizeof($this->row_commands)) {
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
    protected function fillRow(array $a_set): void
    {
        $hash = $this->post_dir_path
            ? md5($a_set["file"])
            : md5($a_set["entry"]);

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

            $this->tpl->setVariable("ICON", "<img src=\"" .
                ilUtil::getImagePath("icon_cat.svg") . "\">");
            $this->ctrl->setParameter($this->parent_obj, "resetoffset", "");
        } else {
            $this->tpl->setCurrentBlock("File");
            $this->tpl->setVariable("TXT_FILENAME2", $a_set["entry"]);
            $this->tpl->parseCurrentBlock();
        }

        if ($a_set["type"] != "dir") {
            $this->tpl->setVariable("TXT_SIZE", ilUtil::formatSize($a_set["size"]));
        }

        // single item commands
        if (sizeof($this->row_commands) &&
            !($a_set["type"] == "dir" && $a_set["entry"] == "..")) {
            $advsel = new ilAdvancedSelectionListGUI();
            $advsel->setListTitle('');
            foreach ($this->row_commands as $rcom) {
                if ($rcom["allow_dir"] || $a_set["type"] != "dir") {
                    if (($rcom["caption"] == "Unzip" && MimeType::getMimeType($this->cur_dir . $a_set['entry']) == "application/zip") || $rcom["caption"] != "Unzip") {
                        $this->ctrl->setParameter($this->parent_obj, "fhsh", $hash);
                        $url = $this->ctrl->getLinkTarget($this->parent_obj, $rcom["cmd"]);
                        $this->ctrl->setParameter($this->parent_obj, "fhsh", "");

                        $advsel->addItem($rcom["caption"], "", $url);
                    }
                }
            }
            $this->tpl->setVariable("ACTIONS", $advsel->getHTML());
        }
    }
}
