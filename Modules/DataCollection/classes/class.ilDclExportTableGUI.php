<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Export User Interface Class
 *
 * @author       Michael Herren <mh@studer-raimann.ch>
 */
class ilDclExportTableGUI extends ilExportTableGUI
{
    public function __construct($a_parent_obj, $a_parent_cmd, $a_exp_obj)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_exp_obj);

        $this->addCustomColumn($this->lng->txt('status'), $this, 'parseExportStatus');
    }


    public function getExportFiles()
    {
        $types = array();
        foreach ($this->parent_obj->getFormats() as $f) {
            $types[] = $f['key'];
            $this->formats[$f['key']] = $f['txt'];
        }

        $file = array();
        foreach ($types as $type) {
            $dir = ilExport::_getExportDirectory($this->obj->getId(), $type, 'dcl');

            // quit if import dir not available
            if (!is_dir($dir) || !is_writeable($dir)) {
                continue;
            }

            // open directory
            $h_dir = dir($dir);

            // get files and save the in the array
            while ($entry = $h_dir->read()) {
                if ($entry != "." && $entry != "..") {
                    $ts = substr($entry, 0, strpos($entry, "__"));

                    $filename = $entry; //($this->isExportInProgress($entry))? substr($entry, 0, - strlen(self::PROGRESS_IDENTIFIER)) : $entry;

                    $file[$entry . $type] = array("type"      => $type, "file" => $filename,
                                                  "size"      => ($this->isExportInProgress($entry)) ? '0' : filesize($dir . "/" . $entry),
                                                  "timestamp" => $ts);
                }
            }

            // close import directory
            $h_dir->close();
        }

        // sort files
        ksort($file);
        reset($file);

        return $file;

        return $files;
    }


    protected function fillRow($a_set)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        foreach ($this->getCustomColumns() as $c) {
            $this->tpl->setCurrentBlock('custom');
            $this->tpl->setVariable('VAL_CUSTOM', $c['obj']->{$c['func']}($a_set['type'], $a_set['file']) . ' ');
            $this->tpl->parseCurrentBlock();
        }

        $file_id = $this->getRowId($a_set);
        $this->tpl->setVariable('VAL_ID', $file_id);

        $type = ($this->formats[$a_set['type']] != "")
            ? $this->formats[$a_set['type']]
            : $a_set['type'];
        $this->tpl->setVariable('VAL_TYPE', $type);

        $filename = ($this->isExportInProgress($a_set['file'])) ? substr($a_set['file'], 0, -strlen(ilDclContentExporter::IN_PROGRESS_POSTFIX)) . ".xlsx" : $a_set['file'];
        $this->tpl->setVariable('VAL_FILE', $filename);

        $this->tpl->setVariable('VAL_SIZE', ilUtil::formatSize($a_set['size']));
        $this->tpl->setVariable('VAL_DATE', ilDatePresentation::formatDate(new ilDateTime($a_set['timestamp'], IL_CAL_UNIX)));

        if (!$this->isExportInProgress($a_set['file'])) {
            $this->tpl->setVariable('TXT_DOWNLOAD', $this->lng->txt('download'));

            $ilCtrl->setParameter($this->getParentObject(), "file", $file_id);
            $url = $ilCtrl->getLinkTarget($this->getParentObject(), "download");
            $ilCtrl->setParameter($this->getParentObject(), "file", "");
            $this->tpl->setVariable('URL_DOWNLOAD', $url);
        }
    }


    public function parseExportStatus($type, $file)
    {
        if ($type == 'xlsx') {
            if ($this->isExportInProgress($file)) {
                return $this->lng->txt('dcl_export_started');
            } else {
                return $this->lng->txt('dcl_export_finished');
            }

            return $file;
        } else {
            return $this->lng->txt('dcl_export_finished');
        }
    }


    protected function isExportInProgress($file)
    {
        $ending = substr($file, -strlen(ilDclContentExporter::IN_PROGRESS_POSTFIX));

        return ($ending == ilDclContentExporter::IN_PROGRESS_POSTFIX);
    }
}
