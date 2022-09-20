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

/**
 * This class is responsible for all file system related actions related actions of a skin such as copying files and folders,
 * generating a new skin, deleting a skin etc..
 * It contains exactly one skin containing several styles. Use this class to parse a skin from xml.
 */
class ilSkinStyleContainer
{
    protected ilLanguage $lng;

    /**
     * Data-scope for the skin this container capsules
     */
    protected ilSkin $skin;

    /**
     * Used to stack messages to be displayed to the user (mostly reports for failed actions)
     */
    protected ilSystemStyleMessageStack $message_stack;

    /**
     * Used to wire this component up with the correct pathes into the customizing directory.
     */
    protected ilSystemStyleConfig $system_styles_conf;

    protected ilFileSystemHelper $file_system;

    public function __construct(
        ilLanguage $lng,
        ilSkin $skin,
        ilSystemStyleMessageStack $message_stack,
        ilSystemStyleConfig $system_styles_conf = null,
        ilFileSystemHelper $file_system = null
    ) {
        $this->lng = $lng;
        $this->skin = $skin;
        $this->setMessageStack($message_stack);

        if (!$system_styles_conf) {
            $this->setSystemStylesConf(new ilSystemStyleConfig());
        } else {
            $this->setSystemStylesConf($system_styles_conf);
        }

        if (!$file_system) {
            $this->file_system = new ilFileSystemHelper($this->lng, $message_stack);
        } else {
            $this->file_system = $file_system;
        }
    }

    /**
     * Creates a new skin. This includes the generation of the XML and the corresponding folders of all contained styles.
     * @throws ilSystemStyleException
     */
    public function create(ilSystemStyleMessageStack $message_stack): void
    {
        if (file_exists($this->getSkinDirectory())) {
            throw new ilSystemStyleException(ilSystemStyleException::SKIN_ALREADY_EXISTS, $this->getSkinDirectory());
        }

        mkdir($this->getSkinDirectory(), 0777, true);

        foreach ($this->getSkin()->getStyles() as $style) {
            $this->file_system->createResourceDirectory(
                $this->getSystemStylesConf()->getDefaultImagesPath(),
                $this->getImagesSkinPath($style->getId())
            );
            $this->file_system->createResourceDirectory(
                $this->getSystemStylesConf()->getDefaultSoundsPath(),
                $this->getSkinDirectory() . $style->getSoundDirectory()
            );
            $this->file_system->createResourceDirectory(
                $this->getSystemStylesConf()->getDefaultFontsPath(),
                $this->getSkinDirectory() . $style->getFontDirectory()
            );
            try {
                $this->createLessStructure($style);
            } catch (Exception $e) {
                $message_stack->addMessage(new ilSystemStyleMessage(
                    $this->lng->txt('less_compile_failed') . ' ' . $e->getMessage(),
                    ilSystemStyleMessage::TYPE_ERROR
                ));
            }
        }
        $this->writeSkinToXML();
    }

    /**
     * Updates the skin. Style are not updated, use updateStyle for that.
     * @throws ilSystemStyleException
     */
    public function updateSkin(ilSkin $old_skin = null): void
    {
        if (!$old_skin) {
            $old_skin = $this->getSkin();
        }
        $old_customizing_skin_directory = $this->getSystemStylesConf()->getCustomizingSkinPath() . $old_skin->getId() . '/';

        //Move if skin id has been changed
        if ($old_skin->getId() != $this->getSkin()->getId()) {
            $this->file_system->move($old_customizing_skin_directory, $this->getSkinDirectory());
        }

        //Delete old template.xml and write a new one
        $this->file_system->delete($this->getSkinDirectory() . 'template.xml');
        $this->writeSkinToXML();
    }

    /**
     * Updates one single style.
     */
    public function updateStyle(string $style_id, ilSkinStyle $old_style): void
    {
        $style = $this->getSkin()->getStyle($style_id);
        if ($style->getImageDirectory() != $old_style->getImageDirectory()) {
            if (file_exists($this->getSkinDirectory() . $old_style->getImageDirectory())) {
                $this->file_system->changeResourceDirectory(
                    $this->getSkinDirectory(),
                    $style->getImageDirectory(),
                    $old_style->getImageDirectory(),
                    count($this->resourcesStyleReferences($old_style->getImageDirectory())) > 0
                );
            } else {
                $this->file_system->createResourceDirectory(
                    $this->getSystemStylesConf()->getDefaultImagesPath(),
                    $this->getImagesSkinPath($style->getId())
                );
            }
        }

        if ($style->getFontDirectory() != $old_style->getFontDirectory()) {
            if (file_exists($this->getSkinDirectory() . $old_style->getFontDirectory())) {
                $this->file_system->changeResourceDirectory(
                    $this->getSkinDirectory(),
                    $style->getFontDirectory(),
                    $old_style->getFontDirectory(),
                    count($this->resourcesStyleReferences($old_style->getFontDirectory())) > 0
                );
            } else {
                $this->file_system->createResourceDirectory(
                    $this->getSystemStylesConf()->getDefaultFontsPath(),
                    $this->getSkinDirectory() . $style->getFontDirectory()
                );
            }
        }

        if ($style->getSoundDirectory() != $old_style->getSoundDirectory()) {
            if (file_exists($this->getSkinDirectory() . $old_style->getSoundDirectory())) {
                $this->file_system->changeResourceDirectory(
                    $this->getSkinDirectory(),
                    $style->getSoundDirectory(),
                    $old_style->getSoundDirectory(),
                    count($this->resourcesStyleReferences($old_style->getSoundDirectory())) > 0
                );
            } else {
                $this->file_system->createResourceDirectory(
                    $this->getSystemStylesConf()->getDefaultSoundsPath(),
                    $this->getSkinDirectory() . $style->getSoundDirectory()
                );
            }
        }

        if (file_exists($this->getSkinDirectory() . $old_style->getCssFile() . '.less')) {
            rename(
                $this->getSkinDirectory() . $old_style->getCssFile() . '.less',
                $this->getLessFilePath($style->getId())
            );
        } else {
            $this->createMainLessFile($style);
        }

        if (file_exists($this->getSkinDirectory() . $old_style->getCssFile() . '-variables.less')) {
            rename(
                $this->getSkinDirectory() . $old_style->getCssFile() . '-variables.less',
                $this->getLessVariablesFilePath($style->getId())
            );
        } else {
            $this->copyVariablesFromDefault($style);
        }

        $this->changeVariablesImport(
            $this->getLessFilePath($style->getId()),
            $old_style->getCssFile() . '-variables.less',
            $this->getLessVariablesName($style->getId())
        );

        if (file_exists($this->getSkinDirectory() . $old_style->getCssFile() . '.css')) {
            rename(
                $this->getSkinDirectory() . $old_style->getCssFile() . '.css',
                $this->getCSSFilePath($style->getId())
            );
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
                copy($this->getSystemStylesConf()->getDelosPath() . '.css', $this->getCSSFilePath($style->getId()));
            }
        }

        $this->writeSkinToXML();
    }

    /**
     * Checks if a given resource (folder) is still referenced by a style of the containers skin
     */
    protected function resourcesStyleReferences(string $resource): array
    {
        $references_ids = [];
        foreach ($this->getSkin()->getStyles() as $style) {
            if ($style->referencesResource($resource)) {
                $references_ids[] = $style->getId();
            }
        }
        return $references_ids;
    }

    /**
     * Creates the less/css structure of a style
     * @throws ilSystemStyleException
     */
    protected function createLessStructure(ilSkinStyle $style): void
    {
        $this->createMainLessFile($style);
        $this->copyVariablesFromDefault($style);
        $this->copyCSSFromDefault($style);
        $this->compileLess($style->getId());
    }

    /**
     * Creates the main less file
     */
    public function createMainLessFile(ilSkinStyle $style): void
    {
        $path = $this->getLessFilePath($style->getId());
        file_put_contents($path, $this->getLessMainFileDefautContent($style));
        $this->getMessageStack()->addMessage(
            new ilSystemStyleMessage(
                $this->lng->txt('main_less_created') . ' ' . $path,
                ilSystemStyleMessage::TYPE_SUCCESS
            )
        );
    }

    /**
     * Copies (resets) the variables file from delos
     */
    public function copyVariablesFromDefault(ilSkinStyle $style): ilSystemStyleLessFile
    {
        $less_file = new ilSystemStyleLessFile($this->getSystemStylesConf()->getDefaultVariablesPath());
        $less_file->setLessVariablesFilePathName($this->getLessVariablesFilePath($style->getId()));
        $less_file->write();
        return $less_file;
    }

    /**
     * Copies (resets) the images from delos
     */
    public function resetImages(ilSkinStyle $style): void
    {
        $this->file_system->recursiveRemoveDir($this->getSkinDirectory() . $style->getImageDirectory());
        $this->file_system->createResourceDirectory(
            $this->getSystemStylesConf()->getDefaultImagesPath(),
            $this->getImagesSkinPath($style->getId())
        );
    }

    /**
     * Copies (resets) the images from delos
     */
    public function copyCSSFromDefault(ilSkinStyle $style): void
    {
        copy($this->getSystemStylesConf()->getDelosPath() . '.css', $this->getCSSFilePath($style->getId()));
    }

    /**
     * Returns the main less default content if a new style is created
     */
    protected function getLessMainFileDefautContent(ilSkinStyle $style): string
    {
        $content = "@import \"" . $this->getSystemStylesConf()->getRelDelosPath() . "\";\n";
        $content .= "// Import Custom Less Files here\n";

        $content .= "@import \"" . $this->getLessVariablesName($style->getId()) . "\";\n";
        return $content;
    }

    /**
     * Deletes the container of a skin completely
     */
    public function delete(): void
    {
        $this->file_system->recursiveRemoveDir(self::getSkinDirectory());
        $this->getMessageStack()->addMessage(
            new ilSystemStyleMessage(
                $this->lng->txt('skin_deleted') . $this->getSkinDirectory(),
                ilSystemStyleMessage::TYPE_SUCCESS
            )
        );
    }

    /**
     * Deletes a style completely
     */
    public function deleteStyle(ilSkinStyle $style): void
    {
        if ($style->isSubstyle()) {
            ilSystemStyleSettings::deleteSubStyleCategoryAssignments(
                $this->getSkin()->getId(),
                $style->getSubstyleOf(),
                $style->getId()
            );
            $this->getMessageStack()->prependMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('style_assignments_deleted') . ' ' . $style->getName(),
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        }

        $this->file_system->saveDeleteFile($this->getLessFilePath($style->getId()));
        $this->file_system->saveDeleteFile($this->getCSSFilePath($style->getId()));
        $this->file_system->saveDeleteFile($this->getLessVariablesFilePath($style->getId()));

        $this->getSkin()->removeStyle($style->getId());

        $this->file_system->removeResourceDirectory(
            $this->getSkinDirectory(),
            $style->getImageDirectory(),
            count($this->resourcesStyleReferences($style->getImageDirectory())) > 0
        );
        $this->file_system->removeResourceDirectory(
            $this->getSkinDirectory(),
            $style->getFontDirectory(),
            count($this->resourcesStyleReferences($style->getImageDirectory())) > 0
        );
        $this->file_system->removeResourceDirectory(
            $this->getSkinDirectory(),
            $style->getSoundDirectory(),
            count($this->resourcesStyleReferences($style->getImageDirectory())) > 0
        );

        $this->writeSkinToXML();
        $this->getMessageStack()->prependMessage(
            new ilSystemStyleMessage(
                $this->lng->txt('style_deleted') . ' ' . $style->getName(),
                ilSystemStyleMessage::TYPE_SUCCESS
            )
        );
    }

    /**
     * Exports the complete skin to an zip file.
     */
    public function export(): void
    {
        ilFileDelivery::deliverFileAttached(
            $this->createTempZip(),
            $this->getSkin()->getId() . '.zip',
            '',
            true
        );
    }

    /**
     * Creates a temp zip file
     */
    public function createTempZip(): string
    {
        $rel_tmp_zip = '../' . $this->getSkin()->getId() . '.zip';
        ilFileUtils::zip($this->getSkinDirectory(), $rel_tmp_zip, true);
        return rtrim($this->getSkinDirectory(), '/') . '.zip';
    }

    protected function changeVariablesImport(
        string $main_path,
        string $old_style_import,
        string $new_style_import
    ): void {
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
    public function compileLess(string $style_id): void
    {
        if (!PATH_TO_LESSC) {
            throw new ilSystemStyleException(ilSystemStyleException::LESSC_NOT_INSTALLED);
        }

        $output = shell_exec(PATH_TO_LESSC . ' ' . $this->getLessFilePath($style_id));
        if (!$output) {
            $less_error = shell_exec(PATH_TO_LESSC . ' ' . $this->getLessFilePath($style_id) . ' 2>&1');
            if (!$less_error) {
                throw new ilSystemStyleException(
                    ilSystemStyleException::LESS_COMPILE_FAILED,
                    'Empty css output, unknown error.'
                );
            }
            throw new ilSystemStyleException(ilSystemStyleException::LESS_COMPILE_FAILED, $less_error);
        }
        file_put_contents($this->getCSSFilePath($style_id), $output);
    }

    public function getSkin(): ilSkin
    {
        return $this->skin;
    }

    public function setSkin(ilSkin $skin): void
    {
        $this->skin = $skin;
    }

    public function getSkinDirectory(): string
    {
        return $this->getSystemStylesConf()->getCustomizingSkinPath() . $this->getSkin()->getId() . '/';
    }

    public function getCSSFilePath(string $style_id): string
    {
        return $this->getSkinDirectory() . $this->getSkin()->getStyle($style_id)->getCssFile() . '.css';
    }

    public function getLessFilePath(string $style_id): string
    {
        return $this->getSkinDirectory() . $this->getSkin()->getStyle($style_id)->getCssFile() . '.less';
    }

    public function getLessVariablesFilePath(string $style_id): string
    {
        return $this->getSkinDirectory() . $this->getLessVariablesName($style_id);
    }

    public function getLessVariablesName(string $style_id): string
    {
        return $this->getSkin()->getStyle($style_id)->getCssFile() . '-variables.less';
    }

    public function getImagesSkinPath(string $style_id): string
    {
        return $this->getSkinDirectory() . $this->getSkin()->getStyle($style_id)->getImageDirectory();
    }

    public function getMessageStack(): ilSystemStyleMessageStack
    {
        return $this->message_stack;
    }

    public function setMessageStack(ilSystemStyleMessageStack $message_stack): void
    {
        $this->message_stack = $message_stack;
    }

    public function addStyle(ilSkinStyle $style): void
    {
        $this->getSkin()->addStyle($style);
        $old_style = new ilSkinStyle('', '');
        $this->updateStyle($style->getId(), $old_style);
    }

    protected function writeSkinToXML(): void
    {
        $this->getSkin()->writeToXMLFile($this->getSkinDirectory() . 'template.xml');
    }

    public function getSystemStylesConf(): ilSystemStyleConfig
    {
        return $this->system_styles_conf;
    }

    public function setSystemStylesConf(ilSystemStyleConfig $system_styles_conf): void
    {
        $this->system_styles_conf = $system_styles_conf;
    }
}
