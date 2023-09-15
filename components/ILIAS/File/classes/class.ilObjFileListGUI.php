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
use ILIAS\File\Icon\IconDatabaseRepository;
use ILIAS\ResourceStorage\Flavour\Definition\CropToSquare;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\ResourceStorage\Services;

/**
 * Class ilObjFileListGUI
 * @author        Alex Killing <alex.killing@gmx.de>
 * @author        Stefan Born <stefan.born@phzh.ch>
 * @author        Fabian Schmid <fs@studer-raimann.ch>
 * @author        Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilObjFileListGUI extends ilObjectListGUI
{
    use ilObjFileSecureString;

    private bool $use_flavor_for_cards = false;
    protected string $title;
    private bool $persist = true;
    private int $max_size = 512;
    private FlavourDefinition $crop_definition;
    private FlavourDefinition $extract_definition;
    private IconDatabaseRepository $icon_repo;
    private Services $irss;

    public function __construct(int $context = self::CONTEXT_REPOSITORY)
    {
        global $DIC;
        parent::__construct($context);

        $this->irss = $DIC->resourceStorage();
        $this->crop_definition = new CropToSquare($this->persist, $this->max_size);
        $this->extract_definition = new PagesToExtract($this->persist, $this->max_size, 1, true);
    }

    protected function getTileImagePath(): string
    {
        if (!$this->use_flavor_for_cards) {
            return parent::getTileImagePath();
        }
        // First we use a configured Tile Image
        $img = $this->object_service->commonSettings()->tileImage()->getByObjId($this->obj_id);
        if ($img->exists()) {
            return $img->getFullPath();
        }

        // Fallback to use a flavour as tile image
        if ($this->use_flavor_for_cards && ($flavour_path = $this->getCardImageFallbackPath(
            $this->obj_id,
            $this->type
        )) !== '') {
            return $flavour_path;
        }

        // Fallback to use a default tile image
        return ilUtil::getImagePath('cont_tile/cont_tile_default_' . $this->type . '.svg');
    }

    /**
     * @description Can be used to take preview flavours as card images
     */
    protected function getCardImageFallbackPath(int $obj_id, string $type): string
    {
        $rid = $this->irss->manage()->find(ilObjFileAccess::getListGUIData($obj_id)['rid'] ?? '');
        if ($rid !== null) {
            if ($this->irss->flavours()->possible($rid, $this->crop_definition)) {
                $url = $this->irss->consume()->flavourUrls(
                    $this->irss->flavours()->get(
                        $rid,
                        $this->crop_definition
                    )
                )->getURLs(false)->current();
                if ($url !== null) {
                    return $url;
                }
            }
            if ($this->irss->flavours()->possible($rid, $this->extract_definition)) {
                $url = $this->irss->consume()->flavourUrls(
                    $this->irss->flavours()->get(
                        $rid,
                        $this->extract_definition
                    )
                )->getURLs(false)->current();
                if ($url !== null) {
                    return $url;
                }
            }
        }
        return '';
    }


    /**
     * initialisation
     */
    public function init(): void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = ilObjFile::OBJECT_TYPE;
        $this->gui_class_name = ilObjFileGUI::class;
        $this->icon_repo = new IconDatabaseRepository();

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }

        $this->commands = ilObjFileAccess::_getCommands();
    }

    /**
     * Get command target frame
     */
    public function getCommandFrame(string $cmd): string
    {
        $frame = "";
        switch ($cmd) {
            case 'sendfile':
                if (ilObjFileAccess::_shouldDownloadDirectly($this->obj_id) &&
                    ilObjFileAccess::_isFileInline($this->title)
                ) {
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
     * e.g. 'crs_offline', and/or to express a specific kind of object, e.g.
     * 'file_inline'.
     */
    public function getIconImageType(): string
    {
        return ilObjFileAccess::_isFileInline($this->title) ? $this->type . '_inline' : $this->type;
    }

    public function getTypeIcon(): string
    {
        $suffix = ilObjFileAccess::getListGUIData($this->obj_id)["suffix"] ?? "";
        return $this->icon_repo->getIconFilePathBySuffix($suffix);
    }

    /**
     * getTitle overwritten in class.ilObjLinkResourceList.php
     */
    public function getTitle(): string
    {
        // Remove filename extension from title
        return $this->secure(preg_replace('/\\.[a-z0-9]+\\z/i', '', $this->title));
    }

    /**
     * Get item properties
     * @return    array        array of property arrays:
     *                        "alert" (boolean) => display as an alert property (usually in red)
     *                        "property" (string) => property name
     *                        "value" (string) => property value
     */
    public function getProperties(): array
    {
        global $DIC;

        $props = parent::getProperties();

        ilObjFileAccess::_preloadData([$this->obj_id], [$this->ref_id]);
        $file_data = ilObjFileAccess::getListGUIData($this->obj_id);
        $revision = $file_data['version'] ?? null;

        // Display a warning if a file is not a hidden Unix file, and
        // the filename extension is missing
        if (null === $revision && !preg_match('/^\\.|\\.[a-zA-Z0-9]+$/', $this->title)) {
            $props[] = array(
                "alert" => false,
                "property" => $DIC->language()->txt("filename_interoperability"),
                "value" => $DIC->language()->txt("filename_extension_missing"),
                'propertyNameVisible' => false,
            );
        }

        $props[] = array(
            "alert" => false,
            "property" => $DIC->language()->txt("type"),
            "value" => ilObjFileAccess::_getFileExtension(
                (null !== $revision) ?
                    ($file_data['title'] ?? "") :
                    $this->title
            ),
            'propertyNameVisible' => false,
        );


        $props[] = array(
            "alert" => false,
            "property" => $DIC->language()->txt("size"),
            "value" => ilUtil::formatSize($file_data['size'] ?? 0, 'short'),
            'propertyNameVisible' => false,
        );
        $version = $file_data['version'] ?? 1;
        if ($version > 1) {
            // add versions link
            if (parent::checkCommandAccess("write", "versions", $this->ref_id, $this->type)) {
                $link = $this->getCommandLink("versions");
                $value = "<a href=\"$link\">" . $DIC->language()->txt("version") . ": $version</a>";
            } else {
                $value = $DIC->language()->txt("version") . ": $version";
            }
            $props[] = array(
                "alert" => false,
                "property" => $DIC->language()->txt("version"),
                "value" => $value,
                "propertyNameVisible" => false,
            );
        }

        if (isset($file_data["date"])) {
            $props[] = array(
                "alert" => false,
                "property" => $DIC->language()->txt("last_update"),
                "value" => ilDatePresentation::formatDate(new ilDateTime($file_data["date"], IL_CAL_DATETIME)),
                'propertyNameVisible' => false,
            );
        }

        if (isset($file_data["page_count"]) && (int)$file_data["page_count"] > 0) {
            $props[] = array(
                "alert" => false,
                "property" => $DIC->language()->txt("page_count"),
                "value" => $file_data["page_count"],
                'propertyNameVisible' => true,
            );
        }

        return $props;
    }

    /**
     * Get command icon image
     */
    public function getCommandImage($a_cmd): string
    {
        return "";
    }

    public function checkCommandAccess(
        string $permission,
        string $cmd,
        int $ref_id,
        string $type,
        ?int $obj_id = null
    ): bool {
        if (ilFileVersionsGUI::CMD_UNZIP_CURRENT_REVISION === $cmd) {
            $file_data = ilObjFileAccess::getListGUIData($this->obj_id);

            return ilObjFileAccess::isZIP($file_data['mime'] ?? null);
        }

        return parent::checkCommandAccess(
            $permission,
            $cmd,
            $ref_id,
            $type,
            $obj_id
        );
    }

    public function getCommandLink(string $cmd): string
    {
        // only create permalink for repository
        if ($cmd === "sendfile" && $this->context === self::CONTEXT_REPOSITORY) {
            if (ilObjFileAccess::_shouldDownloadDirectly($this->obj_id)) {
                // return the perma link for downloads
                return ilObjFileAccess::_getPermanentDownloadLink($this->ref_id);
            }

            $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $this->ref_id);
            return $this->ctrl->getLinkTargetByClass(
                ilRepositoryGUI::class,
                'infoScreen'
            );
        }

        if (ilFileVersionsGUI::CMD_UNZIP_CURRENT_REVISION === $cmd) {
            $file_data = ilObjFileAccess::getListGUIData($this->obj_id);
            if (ilObjFileAccess::isZIP($file_data['mime'] ?? null)) {
                $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $this->ref_id);
                $cmd_link = $this->ctrl->getLinkTargetByClass(
                    ilRepositoryGUI::class,
                    ilFileVersionsGUI::CMD_UNZIP_CURRENT_REVISION
                );
                $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $this->requested_ref_id);
            } else {
                $access_granted = false;
            }
        }


        return parent::getCommandLink($cmd);
    }
}
