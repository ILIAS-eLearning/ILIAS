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

/**
 * Class ilPreview
 *
 * This class provides utility methods for previews.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @package ServicesPreview
 */
class ilPreview
{
    // status values
    public const RENDER_STATUS_NONE = "none";
    public const RENDER_STATUS_PENDING = "pending";
    public const RENDER_STATUS_CREATED = "created";
    public const RENDER_STATUS_FAILED = "failed";

    private const FILENAME_FORMAT = "preview_%02d.jpg";

    /**
     * The object id.
     */
    private ?int $obj_id = null;

    /**
     * The type of the object.
     */
    private ?string $obj_type = null;

    /**
     * The file storage instance.
     */
    private ?\ilFSStoragePreview $storage = null;

    /**
     * Defines whether the preview exists.
     */
    private bool $exists = false;

    /**
     * The timestamp when the preview was rendered.
     */
    private ?string $render_date = null;

    /**
     * The status of the rendering process.
     */
    private string $render_status = self::RENDER_STATUS_NONE;
    protected ilRendererFactory $factory;

    /**
     * Creates a new ilPreview.
     *
     * @param int $a_obj_id The object id.
     * @param string $a_type The type of the object.
     */
    public function __construct(int $a_obj_id, string $a_type = "")
    {
        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_type;
        $this->factory = new ilRendererFactory();
        $this->init();
    }

    /**
     * Creates the preview for the object with the specified id.
     *
     * @param ilObject $a_obj The object to create the preview for.
     * @param bool $a_force true, to force the creation of the preview; false, to create the preview only if needed.
     * @return bool true, if the preview was created; otherwise, false.
     */
    public static function createPreview(\ilObject $a_obj, bool $a_force = false): bool
    {
        $preview = new ilPreview($a_obj->getId(), $a_obj->getType());
        return $preview->create($a_obj, $a_force);
    }

    /**
     * Deletes the preview for the object with the specified id.
     *
     * @param int $a_obj_id The id of the object to create the preview for.
     */
    public static function deletePreview(int $a_obj_id): void
    {
        $preview = new ilPreview($a_obj_id);
        $preview->delete();
    }

    /**
     * Copies the preview images from one preview to a new preview object.
     *
     * @param int $a_src_id The id of the object to copy from.
     * @param int $a_dest_id The id of the object to copy to.
     */
    public static function copyPreviews(int $a_src_id, int $a_dest_id): void
    {
        if (!ilPreviewSettings::isPreviewEnabled()) {
            return;
        }

        // get source preview
        $src = new ilPreview($a_src_id);
        $status = $src->getRenderStatus();

        // created? copy the previews
        if ($status === self::RENDER_STATUS_CREATED) {
            // create destination preview and set it's properties
            $dest = new ilPreview($a_dest_id);
            $dest->setRenderDate($src->getRenderDate());
            $dest->setRenderStatus($src->getRenderStatus());

            // create path
            $dest->getStorage()->create();

            // copy previews
            ilFileUtils::rCopy($src->getStoragePath(), $dest->getStoragePath());

            // save copy
            $dest->doCreate();
        }
    }

    /**
     * Determines whether the object with the specified reference id has a preview.
     *
     * @param int $a_obj_id The id of the object to check.
     * @param string $a_type The type of the object to check.
     * @return bool true, if the object has a preview; otherwise, false.
     */
    public static function hasPreview(int $a_obj_id, string $a_type = ""): bool
    {
        if (!ilPreviewSettings::isPreviewEnabled()) {
            return false;
        }

        $preview = new ilPreview($a_obj_id, $a_type);
        if ($preview->exists()) {
            return true;
        }
        $factory = new ilRendererFactory();
        $renderer = $factory->getRenderer($preview);
        return $renderer !== null;
    }

    /**
     * Gets the render status for the object with the specified id.
     *
     * @param int $a_obj_id The id of the object to get the status for.
     * @return string The status of the rendering process.
     */
    public static function lookupRenderStatus(int $a_obj_id): string
    {
        $preview = new ilPreview($a_obj_id);
        return $preview->getRenderStatus();
    }

    /**
     * Determines whether the preview exists or not.
     *
     * @return bool true, if a preview exists for the object; otherwise, false.
     */
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * Creates the preview.
     *
     * @param ilObject $a_obj The object to create the preview for.
     * @param bool $a_force true, to force the creation of the preview; false, to create the preview only if needed.
     * @return bool true, if the preview was created; otherwise, false.
     */
    public function create(\ilObject $a_obj, bool $a_force = false): bool
    {
        if (!ilPreviewSettings::isPreviewEnabled()) {
            return false;
        }
        $factory = new ilRendererFactory();
        $renderer = $factory->getRenderer($this);

        // no renderer available?
        if ($renderer === null) {
            // bugfix mantis 23293
            $this->delete();
            return false;
        }

        // exists, but still pending?
        if ($this->getRenderStatus() === self::RENDER_STATUS_PENDING) {
            return false;
        }

        // not forced? check if update really needed
        if (!$a_force && $this->getRenderStatus() === self::RENDER_STATUS_CREATED) {
            // check last modified against last render date
            if ($a_obj->getLastUpdateDate() <= $this->getRenderDate()) {
                return false;
            }
        }

        // re-create the directory to store the previews
        $this->getStorage()->delete();
        $this->getStorage()->create();

        // let the renderer create the preview
        $renderer->render($this, $a_obj, true);

        // save to database
        $this->save();

        return true;
    }

    /**
     * Deletes the preview.
     */
    public function delete(): void
    {
        // does exist?
        if ($this->exists()) {
            // delete files and database entry
            $this->getStorage()->delete();
            $this->doDelete();

            // reset values
            $this->exists = false;
            $this->render_date = null;
            $this->render_status = self::RENDER_STATUS_NONE;
        }
    }

    /**
     * Gets an array of preview images.
     *
     * @return array The preview images.
     */
    public function getImages(): array
    {
        $images = array();

        // status must be created
        $path = $this->getAbsoluteStoragePath();
        if ($this->getRenderStatus() === self::RENDER_STATUS_CREATED && ($handle = @opendir($path))) {
            // load files
            while (false !== ($file = readdir($handle))) {
                $filepath = $path . "/" . $file;
                if (!is_file($filepath)) {
                    continue;
                }

                if ($file !== '.' && $file !== '..' && strpos($file, "preview_") === 0) {
                    $image = array();
                    $image["url"] = ilUtil::getHtmlPath($filepath);

                    // get image size
                    $size = @getimagesize($filepath);
                    if ($size !== false) {
                        $image["width"] = $size[0];
                        $image["height"] = $size[1];
                    }

                    $images[$file] = $image;
                }
            }
            closedir($handle);

            // sort by key
            ksort($images);
        }

        return $images;
    }

    /**
     * Saves the preview data to the database.
     */
    public function save(): void
    {
        if ($this->exists) {
            $this->doUpdate();
        } else {
            $this->doCreate();
        }
    }

    /**
     * Create entry in database.
     */
    protected function doCreate(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->insert(
            "preview_data",
            array(
                "obj_id" => array("integer", $this->getObjId()),
                "render_date" => array("timestamp", $this->getRenderDate()),
                "render_status" => array("text", $this->getRenderStatus())
            )
        );
        $this->exists = true;
    }

    /**
     * Read data from database.
     */
    protected function doRead(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $set = $ilDB->queryF(
            "SELECT * FROM preview_data WHERE obj_id=%s",
            array("integer"),
            array($this->getObjId())
        );

        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setRenderDate($rec["render_date"]);
            $this->setRenderStatus($rec["render_status"]);
            $this->exists = true;
        }
    }

    /**
     * Update data in database.
     */
    protected function doUpdate(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->update(
            "preview_data",
            array(
                "render_date" => array("timestamp", $this->getRenderDate()),
                "render_status" => array("text", $this->getRenderStatus())
            ),
            array("obj_id" => array("integer", $this->getObjId()))
        );
    }

    /**
     * Delete data from database.
     */
    protected function doDelete(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            "DELETE FROM preview_data WHERE obj_id=%s",
            array("integer"),
            array($this->getObjId())
        );
    }

    /**
     * Gets the id of the object the preview is for.
     *
     * @return int The id of the object the preview is for.
     */
    public function getObjId(): ?int
    {
        return $this->obj_id;
    }

    /**
     * Gets the type of the object the preview is for.
     *
     * @return string The type of the object the preview is for.
     */
    public function getObjType(): string
    {
        // not evaluated before or specified?
        if (empty($this->obj_type)) {
            $this->obj_type = ilObject::_lookupType($this->getObjId(), false);
        }

        return $this->obj_type;
    }

    /**
     * Gets the path where the previews are stored relative to the web directory.
     *
     * @return string The path where the previews are stored.
     */
    public function getStoragePath(): string
    {
        return $this->getStorage()->getPath();
    }

    /**
     * Gets the absolute path where the previews are stored.
     *
     * @return string The path where the previews are stored.
     */
    public function getAbsoluteStoragePath(): string
    {
        return ILIAS_WEB_DIR . "/" . CLIENT_ID . "/{$this->getStorage()->getPath()}";
    }

    /**
     * Gets the absolute file path for preview images that contains a placeholder
     * in the file name ('%02d') to be formatted with the preview number (use 'sprintf' for that).
     *
     * @return string The format of the absolute file path.
     */
    public function getFilePathFormat(): string
    {
        $path = ilFileUtils::removeTrailingPathSeparators($this->getAbsoluteStoragePath());
        return $path . "/" . self::FILENAME_FORMAT;
    }

    public function getRenderDate(): ?string
    {
        return $this->render_date;
    }

    public function setRenderDate(string $a_date): void
    {
        $this->render_date = $a_date;
    }

    /**
     * Gets the status of the rendering process.
     *
     * @return string The status of the rendering process.
     */
    public function getRenderStatus(): string
    {
        return $this->render_status;
    }

    /**
     * Sets the status of the rendering process.
     *
     * @param string $a_status The status to set.
     */
    public function setRenderStatus(string $a_status): void
    {
        $this->render_status = $a_status;
    }

    /**
     * Gets the storage object for the preview.
     *
     * @return ilFSStoragePreview The storage object.
     */
    public function getStorage(): \ilFSStoragePreview
    {
        if ($this->storage === null) {
            $this->storage = new ilFSStoragePreview($this->obj_id);
        }

        return $this->storage;
    }

    /**
     * Initializes the preview object.
     */
    private function init(): void
    {
        // read entry
        $this->doRead();
    }
}
