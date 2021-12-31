<?php declare(strict_types=1);

/**
 * This class is responsible for all file system related actions related actions of a skin such as copying files and folders,
 * generating a new skin, deleting a skin etc..
 * It contains exactly one skin containing several styles. Use this class to parse a skin from xml.
 */
class ilSystemStyleSkinContainer
{
    protected ilLanguage $lng;

    /**
     * Data-scope for the skin this container capsules
     */
    protected ilSkinXML $skin;

    /**
     * Used to stack messages to be displayed to the user (mostly reports for failed actions)
     */
    protected static ilSystemStyleMessageStack $message_stack;

    /**
     * Used to wire this component up with the correct pathes into the customizing directory.
     */
    protected ilSystemStyleConfig $system_styles_conf;

    public function __construct(
        ilSkinXML $skin,
        ilSystemStyleMessageStack $message_stack = null,
        ilSystemStyleConfig $system_styles_conf = null
    ) {
        global $DIC;

        $this->lng = $DIC->language();

        $this->skin = $skin;

        if (!$message_stack) {
            $this->setMessageStack(new ilSystemStyleMessageStack());
        } else {
            $this->setMessageStack($message_stack);
        }

        if (!$system_styles_conf) {
            $this->setSystemStylesConf(new ilSystemStyleConfig());
        } else {
            $this->setSystemStylesConf($system_styles_conf);
        }
    }

    /**
     * Generate the container class by parsing the corresponding XML
     * @throws ilSystemStyleException
     */
    public static function generateFromId(
        string $skin_id,
        ilSystemStyleMessageStack $message_stack = null,
        ilSystemStyleConfig $system_styles_conf = null
    ) : ilSystemStyleSkinContainer {
        if (!$skin_id) {
            throw new ilSystemStyleException(ilSystemStyleException::NO_SKIN_ID);
        }

        if (!$system_styles_conf) {
            $system_styles_conf = new ilSystemStyleConfig();
        }

        if ($skin_id != "default") {
            return new self(ilSkinXML::parseFromXML($system_styles_conf->getCustomizingSkinPath() . $skin_id . "/template.xml"),
                $message_stack, $system_styles_conf);
        } else {
            return new self(ilSkinXML::parseFromXML($system_styles_conf->getDefaultTemplatePath()), $message_stack,
                $system_styles_conf);
        }
    }

    /**
     * Creates a new skin. This includes the generation of the XML and the corresponding folders of all contained styles.
     * @throws ilSystemStyleException
     */
    public function create(ilSystemStyleMessageStack $message_stack)
    {
        if (file_exists($this->getSkinDirectory())) {
            throw new ilSystemStyleException(ilSystemStyleException::SKIN_ALREADY_EXISTS, $this->getSkinDirectory());
        }

        mkdir($this->getSkinDirectory(), 0777, true);

        foreach ($this->getSkin()->getStyles() as $style) {
            $this->createResourceDirectory($this->getSystemStylesConf()->getDefaultImagesPath(),
                $style->getImageDirectory());
            $this->createResourceDirectory($this->getSystemStylesConf()->getDefaultSoundsPath(),
                $style->getSoundDirectory());
            $this->createResourceDirectory($this->getSystemStylesConf()->getDefaultFontsPath(),
                $style->getFontDirectory());
            try {
                $this->createLessStructure($style);
            } catch (Exception $e) {
                $message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt("less_compile_failed") . " " . $e->getMessage(),
                    ilSystemStyleMessage::TYPE_ERROR));
            }
        }
        $this->writeSkinToXML();
    }

    /**
     * Updates the skin. Style are not updated, use updateStyle for that.
     * @throws ilSystemStyleException
     */
    public function updateSkin(ilSkinXML $old_skin = null)
    {
        if (!$old_skin) {
            $old_skin = $this->getSkin();
        }
        $old_customizing_skin_directory = $this->getSystemStylesConf()->getCustomizingSkinPath() . $old_skin->getId() . "/";

        //Move if skin id has been changed
        if ($old_skin->getId() != $this->getSkin()->getId()) {
            $this->move($old_customizing_skin_directory, $this->getSkinDirectory());
        }

        //Delete old template.xml and write a new one
        unlink($this->getSkinDirectory() . "template.xml");
        $this->writeSkinToXML();
    }

    /**
     * Updates one single style.
     */
    public function updateStyle(string $style_id, ilSkinStyleXML $old_style)
    {
        $style = $this->getSkin()->getStyle($style_id);

        if ($style->getImageDirectory() != $old_style->getImageDirectory()) {
            if (file_exists($this->getSkinDirectory() . $old_style->getImageDirectory())) {
                $this->changeResourceDirectory($style->getImageDirectory(), $old_style->getImageDirectory());
            } else {
                $this->createResourceDirectory($this->getSystemStylesConf()->getDefaultImagesPath(),
                    $style->getImageDirectory());
            }
        }

        if ($style->getFontDirectory() != $old_style->getFontDirectory()) {
            if (file_exists($this->getSkinDirectory() . $old_style->getFontDirectory())) {
                $this->changeResourceDirectory($style->getFontDirectory(), $old_style->getFontDirectory());
            } else {
                $this->createResourceDirectory($this->getSystemStylesConf()->getDefaultFontsPath(),
                    $style->getFontDirectory());
            }
        }

        if ($style->getSoundDirectory() != $old_style->getSoundDirectory()) {
            if (file_exists($this->getSkinDirectory() . $old_style->getSoundDirectory())) {
                $this->changeResourceDirectory($style->getSoundDirectory(), $old_style->getSoundDirectory());
            } else {
                $this->createResourceDirectory($this->getSystemStylesConf()->getDefaultSoundsPath(),
                    $style->getSoundDirectory());
            }
        }

        if (file_exists($this->getSkinDirectory() . $old_style->getCssFile() . ".less")) {
            rename($this->getSkinDirectory() . $old_style->getCssFile() . ".less",
                $this->getLessFilePath($style->getId()));
        } else {
            $this->createMainLessFile($style);
        }

        if (file_exists($this->getSkinDirectory() . $old_style->getCssFile() . "-variables.less")) {
            rename($this->getSkinDirectory() . $old_style->getCssFile() . "-variables.less",
                $this->getLessVariablesFilePath($style->getId()));
        } else {
            $this->copyVariablesFromDefault($style);
        }

        $this->changeVariablesImport($this->getLessFilePath($style->getId()),
            $old_style->getCssFile() . "-variables.less", $this->getLessVariablesName($style->getId()));

        if (file_exists($this->getSkinDirectory() . $old_style->getCssFile() . ".css")) {
            rename($this->getSkinDirectory() . $old_style->getCssFile() . ".css",
                $this->getCSSFilePath($style->getId()));
        } else {
            try {
                $this->compileLess($style->getId());
            } catch (Exception $e) {
                $this->getMessageStack()->addMessage(
                    new ilSystemStyleMessage(
                        $e->getMessage(),
                        ilSystemStyleMessage::TYPE_ERROR
                    )
                );
                copy($this->getSystemStylesConf()->getDelosPath() . ".css", $this->getCSSFilePath($style->getId()));
            }
        }

        $this->writeSkinToXML();
    }

    /**
     * Checks if a given resource (folder) is still referenced by a style of the containers skin
     */
    protected function resourcesStyleReferences(string $resource) : array
    {
        $references_ids = array();
        foreach ($this->getSkin()->getStyles() as $style) {
            if ($style->referencesResource($resource)) {
                $references_ids[] = $style->getId();
            }
        }
        return $references_ids;
    }

    /**
     * Creates a resource directory (sound, images or fonts) by copying from the source (mostly delos)
     * @throws ilSystemStyleException
     */
    protected function createResourceDirectory(string $source, string $target)
    {
        $path = $this->getSkinDirectory() . $target;

        mkdir($path, 0777, true);

        if ($source != "") {
            self::xCopy($source, $path);
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt("dir_created") . $path,
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        }
    }

    /**
     * Alters the name/path of a resource directory
     * @throws ilSystemStyleException
     */
    protected function changeResourceDirectory(string $new_dir, string $old_dir)
    {
        $absolut_new_dir = $this->getSkinDirectory() . $new_dir;
        $absolut_old_dir = $this->getSkinDirectory() . $old_dir;

        if (file_exists($absolut_new_dir)) {
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt("dir_changed_to") . " " . $absolut_new_dir,
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt("dir_preserved_backup") . " " . $absolut_old_dir,
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        } else {
            mkdir($absolut_new_dir, 0777, true);
            self::xCopy($absolut_old_dir, $absolut_new_dir);
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt("dir_copied_from") . " " . $absolut_old_dir . " " . $this->lng->txt("sty_copy_to") . " " . $absolut_new_dir,
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
            if (count($this->resourcesStyleReferences($old_dir)) == 0) {
                self::recursiveRemoveDir(self::getSkinDirectory() . $old_dir);
                $this->getMessageStack()->addMessage(
                    new ilSystemStyleMessage(
                        $this->lng->txt("dir_deleted") . " " . $absolut_old_dir,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    )
                );
            } else {
                $this->getMessageStack()->addMessage(
                    new ilSystemStyleMessage(
                        $this->lng->txt("dir_preserved_linked") . " " . $absolut_old_dir,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    )
                );
            }
        }
    }

    /**
     * Deletes a resource directory
     */
    protected function removeResourceDirectory(string $dir)
    {
        $absolut_dir = $this->getSkinDirectory() . $dir;

        if (file_exists($absolut_dir)) {
            if (count($this->resourcesStyleReferences($dir)) == 0) {
                self::recursiveRemoveDir($this->getSkinDirectory() . $dir);
                $this->getMessageStack()->addMessage(
                    new ilSystemStyleMessage(
                        $this->lng->txt("dir_deleted") . " " . $dir,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    )
                );
            } else {
                $this->getMessageStack()->addMessage(
                    new ilSystemStyleMessage(
                        $this->lng->txt("dir_preserved_linked") . " " . $dir,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    )
                );
            }
        }
    }

    /**
     * Creates the less/css structure of a style
     * @throws ilSystemStyleException
     */
    protected function createLessStructure(ilSkinStyleXML $style)
    {
        $this->createMainLessFile($style);
        $this->copyVariablesFromDefault($style);
        $this->copyCSSFromDefault($style);
        $this->compileLess($style->getId());
    }

    /**
     * Creates the main less file
     */
    public function createMainLessFile(ilSkinStyleXML $style)
    {
        $path = $this->getLessFilePath($style->getId());
        file_put_contents($path, $this->getLessMainFileDefautContent($style));
        $this->getMessageStack()->addMessage(
            new ilSystemStyleMessage(
                $this->lng->txt("main_less_created") . " " . $path,
                ilSystemStyleMessage::TYPE_SUCCESS
            )
        );
    }

    /**
     * Copies (resets) the variables file from delos
     */
    public function copyVariablesFromDefault(ilSkinStyleXML $style) : ilSystemStyleLessFile
    {
        $less_file = new ilSystemStyleLessFile($this->getSystemStylesConf()->getDefaultVariablesPath());
        $less_file->setLessVariablesFilePathName($this->getLessVariablesFilePath($style->getId()));
        $less_file->write();
        return $less_file;
    }

    /**
     * Copies (resets) the images from delos
     */
    public function resetImages(ilSkinStyleXML $style) : void
    {
        self::recursiveRemoveDir($this->getSkinDirectory() . $style->getImageDirectory());
        $this->createResourceDirectory($this->getSystemStylesConf()->getDefaultImagesPath(),
            $style->getImageDirectory());
    }

    /**
     * Copies (resets) the images from delos
     */
    public function copyCSSFromDefault(ilSkinStyleXML $style) : void
    {
        copy($this->getSystemStylesConf()->getDelosPath() . ".css", $this->getCSSFilePath($style->getId()));
    }

    /**
     * Recursive copy of a folder
     * @throws ilSystemStyleException
     */
    public static function xCopy(string $src, string $dest) : void
    {
        foreach (scandir($src) as $file) {
            $src_file = rtrim($src, '/') . '/' . $file;
            $dest_file = rtrim($dest, '/') . '/' . $file;
            if (!is_readable($src_file)) {
                throw new ilSystemStyleException(ilSystemStyleException::FILE_OPENING_FAILED, $src_file);
            }
            if (substr($file, 0, 1) != ".") {
                if (is_dir($src_file)) {
                    if (!file_exists($dest_file)) {
                        try {
                            mkdir($dest_file);
                        } catch (Exception $e) {
                            throw new ilSystemStyleException(ilSystemStyleException::FOLDER_CREATION_FAILED,
                                "Copy " . $src_file . " to " . $dest_file . " Error: " . $e);
                        }
                    }
                    self::xCopy($src_file, $dest_file);
                } else {
                    try {
                        copy($src_file, $dest_file);
                    } catch (Exception $e) {
                        throw new ilSystemStyleException(ilSystemStyleException::FILE_CREATION_FAILED,
                            "Copy " . $src_file . " to " . $dest_file . " Error: " . $e);
                    }
                }
            }
        }
    }

    /**
     * Recursive delete of a folder
     */
    public static function recursiveRemoveDir(string $dir) : void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        self::recursiveRemoveDir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Returns the main less default content if a new style is created
     */
    protected function getLessMainFileDefautContent(ilSkinStyleXML $style) : string
    {
        $content = "@import \"" . $this->getSystemStylesConf()->getRelDelosPath() . "\";\n";
        $content .= "// Import Custom Less Files here\n";

        $content .= "@import \"" . $this->getLessVariablesName($style->getId()) . "\";\n";
        return $content;
    }

    /**
     * Used to move a complete directory of a skin
     */
    public function move(string $from, string $to)
    {
        rename($from, $to);
    }

    /**
     * Deletes the container of a skin completely
     */
    public function delete() : void
    {
        self::recursiveRemoveDir(self::getSkinDirectory());
        $this->getMessageStack()->addMessage(
            new ilSystemStyleMessage(
                $this->lng->txt("skin_deleted") . $this->getSkinDirectory(),
                ilSystemStyleMessage::TYPE_SUCCESS
            )
        );
    }

    /**
     * Deletes a given file in the container
     */
    protected function deleteFile(string $path)
    {
        if (file_exists($path)) {
            unlink($path);
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt("file_deleted") . " " . $path,
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        }
    }

    /**
     * Deletes a style completely
     */
    public function deleteStyle(ilSkinStyleXML $style) : void
    {
        if ($style->isSubstyle()) {
            ilSystemStyleSettings::deleteSubStyleCategoryAssignments($this->getSkin()->getId(), $style->getSubstyleOf(),
                $style->getId());
            $this->getMessageStack()->prependMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt("style_assignments_deleted") . " " . $style->getName(),
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        }

        $this->deleteFile($this->getLessFilePath($style->getId()));
        $this->deleteFile($this->getCSSFilePath($style->getId()));
        $this->deleteFile($this->getLessVariablesFilePath($style->getId()));

        $this->getSkin()->removeStyle($style->getId());

        $this->removeResourceDirectory($style->getImageDirectory());
        $this->removeResourceDirectory($style->getFontDirectory());
        $this->removeResourceDirectory($style->getSoundDirectory());

        $this->writeSkinToXML();
        $this->getMessageStack()->prependMessage(
            new ilSystemStyleMessage(
                $this->lng->txt("style_deleted") . " " . $style->getName(),
                ilSystemStyleMessage::TYPE_SUCCESS
            )
        );
    }

    /**
     * Copies a complete Skin
     * @throws ilSystemStyleException
     */
    public function copy(string $new_skin_txt_addon = "Copy") : ilSystemStyleSkinContainer
    {
        $new_skin_id_addon = "";
        $new_skin_name_addon = "";

        while (ilStyleDefinition::skinExists($this->getSkin()->getId() . $new_skin_id_addon,
            $this->getSystemStylesConf())) {
            $new_skin_id_addon .= $new_skin_txt_addon;
            $new_skin_name_addon .= " " . $new_skin_txt_addon;
        }

        $new_skin_path = rtrim($this->getSkinDirectory(), "/") . $new_skin_id_addon;

        mkdir($new_skin_path, 0775, true);
        $this->xCopy($this->getSkinDirectory(), $new_skin_path);
        $this->getMessageStack()->addMessage(new ilSystemStyleMessage($this->lng->txt("directory_created") . " " . $new_skin_path,
            ilSystemStyleMessage::TYPE_SUCCESS));
        $skin_container = self::generateFromId($this->getSkin()->getId() . $new_skin_id_addon, null,
            $this->getSystemStylesConf());
        $skin_container->getSkin()->setName($skin_container->getSkin()->getName() . $new_skin_name_addon);
        $skin_container->getSkin()->setVersion("0.1");
        $skin_container->updateSkin($skin_container->getSkin());
        return $skin_container;
    }

    /**
     * Exports the complete skin to an zip file.
     */
    public function export()
    {
        ilFileDelivery::deliverFileAttached($this->createTempZip(), $this->getSkin()->getId() . ".zip",
            '', true);
    }

    /**
     * Creates a temp zip file
     */
    public function createTempZip() : string
    {
        $rel_tmp_zip = "../" . $this->getSkin()->getId() . ".zip";
        ilUtil::zip($this->getSkinDirectory(), $rel_tmp_zip, true);
        return rtrim($this->getSkinDirectory(), "/") . ".zip";
    }

    /**
     * Imports a skin from zip
     * @throws ilSystemStyleException
     */
    public static function import(
        string $import_zip_path,
        string $name,
        ilSystemStyleMessageStack $message_stack = null,
        ilSystemStyleConfig $system_styles_conf = null,
        bool $uploaded = true
    ) : ilSystemStyleSkinContainer {
        if (!$system_styles_conf) {
            $system_styles_conf = new ilSystemStyleConfig();
        }

        $skin_id = preg_replace('/[^A-Za-z0-9\-_]/', '', rtrim($name, ".zip"));

        while (ilStyleDefinition::skinExists($skin_id, $system_styles_conf)) {
            $skin_id .= "Copy";
        }

        $skin_path = $system_styles_conf->getCustomizingSkinPath() . $skin_id;
        mkdir($skin_path, 0777, true);

        $temp_zip_path = $skin_path . "/" . $name;
        if ($uploaded) {
            move_uploaded_file($import_zip_path, $temp_zip_path);
        } else {
            rename($import_zip_path, $temp_zip_path);
        }
        ilUtil::unzip($temp_zip_path);
        unlink($temp_zip_path);

        return self::generateFromId($skin_id, $message_stack, $system_styles_conf);
    }

    protected function changeVariablesImport(string $main_path, string $old_style_import, string $new_style_import) : void
    {
        $main_less_content = file_get_contents($main_path);
        $main_less_content = str_replace(
            "@import \"" . $old_style_import,
            "@import \"" . $new_style_import,
            $main_less_content
        );
        file_put_contents($main_path, $main_less_content);
    }

    /**
     * @throws ilSystemStyleException
     */
    public function compileLess(string $style_id) : void
    {
        if (!PATH_TO_LESSC) {
            throw new ilSystemStyleException(ilSystemStyleException::LESSC_NOT_INSTALLED);
        }

        $output = shell_exec(PATH_TO_LESSC . " " . $this->getLessFilePath($style_id));
        if (!$output) {
            $less_error = shell_exec(PATH_TO_LESSC . " " . $this->getLessFilePath($style_id) . " 2>&1");
            if (!$less_error) {
                throw new ilSystemStyleException(ilSystemStyleException::LESS_COMPILE_FAILED,
                    "Empty css output, unknown error.");
            }
            throw new ilSystemStyleException(ilSystemStyleException::LESS_COMPILE_FAILED, $less_error);
        }
        file_put_contents($this->getCSSFilePath($style_id), $output);
    }

    public function getSkin() : ilSkinXML
    {
        return $this->skin;
    }

    public function setSkin(ilSkinXML $skin) : void
    {
        $this->skin = $skin;
    }

    public function getSkinDirectory() : string
    {
        return $this->getSystemStylesConf()->getCustomizingSkinPath() . $this->getSkin()->getId() . "/";
    }

    public function getCSSFilePath(string $style_id) : string
    {
        return $this->getSkinDirectory() . $this->getSkin()->getStyle($style_id)->getCssFile() . ".css";
    }

    public function getLessFilePath(string $style_id) : string
    {
        return $this->getSkinDirectory() . $this->getSkin()->getStyle($style_id)->getCssFile() . ".less";
    }

    public function getLessVariablesFilePath(string $style_id) : string
    {
        return $this->getSkinDirectory() . $this->getLessVariablesName($style_id);
    }

    public function getLessVariablesName(string $style_id) : string
    {
        return $this->getSkin()->getStyle($style_id)->getCssFile() . "-variables.less";
    }

    public function getImagesSkinPath(string $style_id) : string
    {
        return $this->getSkinDirectory() . $this->getSkin()->getStyle($style_id)->getImageDirectory();
    }

    public static function getMessageStack() : ilSystemStyleMessageStack
    {
        return self::$message_stack;
    }

    public static function setMessageStack(ilSystemStyleMessageStack $message_stack) : void
    {
        self::$message_stack = $message_stack;
    }

    public function addStyle(ilSkinStyleXML $style) : void
    {
        $this->getSkin()->addStyle($style);
        $old_style = new ilSkinStyleXML("", "");
        $this->updateStyle($style->getId(), $old_style);
    }

    protected function writeSkinToXML() : void
    {
        $this->getSkin()->writeToXMLFile($this->getSkinDirectory() . "template.xml");
    }

    public function getSystemStylesConf() : ilSystemStyleConfig
    {
        return $this->system_styles_conf;
    }

    public function setSystemStylesConf(ilSystemStyleConfig $system_styles_conf) : void
    {
        $this->system_styles_conf = $system_styles_conf;
    }
}
