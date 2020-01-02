<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
 * Class ilObjFileListGUI
 *
 * @author        Alex Killing <alex.killing@gmx.de>
 * @author        Stefan Born <stefan.born@phzh.ch>
 * @author        Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileListGUI extends ilObjectListGUI
{

    /**
     * initialisation
     */
    public function init()
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "file";
        $this->gui_class_name = "ilobjfilegui";

        // general commands array
        include_once('./Modules/File/classes/class.ilObjFileAccess.php');
        $this->commands = ilObjFileAccess::_getCommands();
    }


    /**
     * Get command target frame
     *
     * @param string $a_cmd command
     *
     * @return    string        command target frame
     */
    public function getCommandFrame($a_cmd)
    {
        $frame = "";
        switch ($a_cmd) {
            case 'sendfile':
                require_once('Modules/File/classes/class.ilObjFileAccess.php');
                if (ilObjFileAccess::_isFileInline($this->title)) {
                    $frame = '_blank';
                }
                break;
            case "":
                $frame = ilFrameTargetInfo::_getFrame("RepositoryContent");
                break;

            default:
        }

        return $frame;
    }


    /**
     * Returns the icon image type.
     * For most objects, this is same as the object type, e.g. 'cat','fold'.
     * We can return here other values, to express a specific state of an object,
     * e.g. 'crs_offline", and/or to express a specific kind of object, e.g.
     * 'file_inline'.
     */
    public function getIconImageType()
    {
        include_once('Modules/File/classes/class.ilObjFileAccess.php');

        return ilObjFileAccess::_isFileInline($this->title) ? $this->type . '_inline' : $this->type;
    }


    /**
     * getTitle overwritten in class.ilObjLinkResourceList.php
     *
     * @return string title
     */
    public function getTitle()
    {
        // Remove filename extension from title
        return preg_replace('/\\.[a-z0-9]+\\z/i', '', $this->title);
    }


    /**
     * Get item properties
     *
     * @return    array        array of property arrays:
     *                        "alert" (boolean) => display as an alert property (usually in red)
     *                        "property" (string) => property name
     *                        "value" (string) => property value
     */
    public function getProperties()
    {
        global $DIC;

        $props = parent::getProperties();

        // to do: implement extra smaller file info object

        // Display a warning if a file is not a hidden Unix file, and
        // the filename extension is missing
        if (!preg_match('/^\\.|\\.[a-zA-Z0-9]+$/', $this->title)) {
            $props[] = array(
                "alert"               => false,
                "property"            => $DIC->language()->txt("filename_interoperability"),
                "value"               => $DIC->language()->txt("filename_extension_missing"),
                'propertyNameVisible' => false,
            );
        }

        $props[] = array(
            "alert"               => false,
            "property"            => $DIC->language()->txt("type"),
            "value"               => ilObjFileAccess::_getFileExtension($this->title),
            'propertyNameVisible' => false,
        );

        $fileData = ilObjFileAccess::getListGUIData($this->obj_id);
        if (is_array($fileData)) {
            $props[] = array(
                "alert"               => false,
                "property"            => $DIC->language()->txt("size"),
                "value"               => ilUtil::formatSize($fileData['size'], 'short'),
                'propertyNameVisible' => false,
            );
            $version = $fileData['version'];
            if ($version > 1) {
                // add versions link
                if (parent::checkCommandAccess("write", "versions", $this->ref_id, $this->type)) {
                    $link = $this->getCommandLink("versions");
                    $value = "<a href=\"$link\">" . $DIC->language()->txt("version") . ": $version</a>";
                } else {
                    $value = $DIC->language()->txt("version") . ": $version";
                }
                $props[] = array(
                    "alert"               => false,
                    "property"            => $DIC->language()->txt("version"),
                    "value"               => $value,
                    "propertyNameVisible" => false,
                );
            }

            // #6040
            if ($fileData["date"]) {
                $props[] = array(
                    "alert"               => false,
                    "property"            => $DIC->language()->txt("last_update"),
                    "value"               => ilDatePresentation::formatDate(new ilDateTime($fileData["date"], IL_CAL_DATETIME)),
                    'propertyNameVisible' => false,
                );
            }

            if ($fileData["page_count"]) {
                $props[] = array(
                    "alert"               => false,
                    "property"            => $DIC->language()->txt("page_count"),
                    "value"               => $fileData["page_count"],
                    'propertyNameVisible' => true,
                );
            }
        }

        return $props;
    }


    /**
     * Get command icon image
     */
    public function getCommandImage($a_cmd)
    {
        switch ($a_cmd) {
            default:
                return "";
        }
    }


    /**
     * Get command link url.
     *
     * @param string $a_cmd The command to get the link for.
     *
     * @return string The command link.
     */
    public function getCommandLink($a_cmd)
    {
        // overwritten to always return the permanent download link

        // only create permalink for repository
        if ($a_cmd == "sendfile" && $this->context == self::CONTEXT_REPOSITORY) {
            // return the perma link for downloads
            return ilObjFileAccess::_getPermanentDownloadLink($this->ref_id);
        }

        return parent::getCommandLink($a_cmd);
    }
}
