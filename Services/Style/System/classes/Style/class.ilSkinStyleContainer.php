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

        mkdir($this->getSkinDirectory(), 0775, true);

        foreach ($this->getSkin()->getStyles() as $style) {
            $this->file_system->createResourceDirectory(
                $this->getSystemStylesConf()->getDefaultImagesPath(),
                $this->getImagesStylePath($style->getId())
            );
            $this->file_system->createResourceDirectory(
                $this->getSystemStylesConf()->getDefaultSoundsPath(),
                $this->getSoundsStylePath($style->getId())
            );
            $this->file_system->createResourceDirectory(
                $this->getSystemStylesConf()->getDefaultFontsPath(),
                $this->getFontsStylePath($style->getId())
            );
            try {
                $this->createScssStructure($style);
            } catch (Exception $e) {
                $message_stack->addMessage(new ilSystemStyleMessage(
                    $this->lng->txt('scss_compile_failed') . ' ' . $e->getMessage(),
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

        if(!is_dir($this->getSkinDirectory().$style->getId())) {
            mkdir($this->getSkinDirectory().$style->getId(), 0775, true);
        }

        if ($style->getImageDirectory() != $old_style->getImageDirectory()) {
            if (is_dir($this->getSkinDirectory() .$old_style->getId()."/". $old_style->getImageDirectory())) {
                $this->file_system->changeResourceDirectory(
                    $this->getSkinDirectory(),
                    $style->getId()."/".$style->getImageDirectory(),
                    $old_style->getId()."/".$old_style->getImageDirectory()
                );
            } else {
                $this->file_system->createResourceDirectory(
                    $this->getSystemStylesConf()->getDefaultImagesPath(),
                    $this->getImagesStylePath($style->getId())
                );
            }
        }

        if ($style->getFontDirectory() != $old_style->getFontDirectory()) {
            if (is_dir($this->getSkinDirectory() . $old_style->getId()."/". $old_style->getFontDirectory())) {
                $this->file_system->changeResourceDirectory(
                    $this->getSkinDirectory(),
                    $style->getId()."/".$style->getFontDirectory(),
                    $old_style->getId()."/".$old_style->getFontDirectory()
                );
            } else {
                $this->file_system->createResourceDirectory(
                    $this->getSystemStylesConf()->getDefaultFontsPath(),
                    $this->getFontsStylePath($style->getId())
                );
            }
        }

        if ($style->getSoundDirectory() != $old_style->getSoundDirectory()) {
            if (is_dir($this->getSkinDirectory() . $old_style->getId()."/". $old_style->getSoundDirectory())) {
                $this->file_system->changeResourceDirectory(
                    $this->getSkinDirectory(),
                    $style->getId()."/".$style->getSoundDirectory(),
                    $old_style->getId()."/".$old_style->getSoundDirectory()
                );
            } else {
                $this->file_system->createResourceDirectory(
                    $this->getSystemStylesConf()->getDefaultSoundsPath(),
                    $this->getSoundsStylePath($style->getId())
                );
            }
        }

        if (is_dir($this->getScssSettingsPath($old_style->getId()))) {
            $this->file_system->changeResourceDirectory(
                $this->getSkinDirectory(),
                $style->getId().'/'.$this->getScssSettingsFolderName(),
                $old_style->getId().'/'.$this->getScssSettingsFolderName()
            );
        } else {
            $this->copySettingsFromDefault($style);
        }

        if (file_exists($this->getSkinDirectory() .$old_style->getId().'/'.$old_style->getCssFile() . '.scss')) {
            rename(
                $this->getSkinDirectory().$old_style->getId().'/'.$old_style->getCssFile().'.scss',
                $this->getScssFilePath($style->getId())
            );
        } else {
            $this->createMainScssFile($style);
        }

        if (file_exists($this->getSkinDirectory().$old_style->getId().'/'.$old_style->getCssFile().'.css')) {
            rename(
                $this->getSkinDirectory().$old_style->getId().'/'.$old_style->getCssFile().'.css',
                $this->getCSSFilePath($style->getId())
            );
        } else {
            try {
                $this->compileScss($style->getId());
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
     * Creates the Scss/css structure of a style
     * @throws ilSystemStyleException
     */
    protected function createScssStructure(ilSkinStyle $style): void
    {
        $this->copySettingsFromDefault($style);
        $this->createMainScssFile($style);
        $this->copyCSSFromDefault($style);
        $this->compileScss($style->getId());
    }

    /**
     * Creates the main Scss file
     */
    public function createMainScssFile(ilSkinStyle $style): void
    {

        $replacement_start = "// ## Begin Replacement Variables";
        $replacement_end = "// ## End Replacement Variables";

        $path = $this->getScssFilePath($style->getId());

        if(!is_file($path)) {
            $main_scss_content = $this->getNewMainScssFileContent($replacement_start, $replacement_end);
        } else {
            $main_scss_content = file_get_contents($path);
        }

        $regex_part_to_replace_start = "%$replacement_start.*?$replacement_end%s";
        $settings = new ilSystemStyleScssSettings($this->getScssSettingsPath($style->getId()));
        $replacement = $settings->getVariablesForDelosOverride();
        $new_variabales_content = "$replacement_start $replacement $replacement_end";

        $main_scss_content = preg_replace(
            $regex_part_to_replace_start,
            $new_variabales_content,
            $main_scss_content
        );

        file_put_contents($path, $main_scss_content);

        $this->getMessageStack()->addMessage(
            new ilSystemStyleMessage(
                $this->lng->txt('main_scss_created') . ' ' . $path,
                ilSystemStyleMessage::TYPE_SUCCESS
            )
        );
    }

    protected function getNewMainScssFileContent(string $replacement_start, string $replacement_end): string
    {
        return "// # ITCSS structure
            // Try to apply changes by only changing the variables in the settings
            @use \"./" . $this->getScssSettingsFolderName() . "\" as globals;
            
            // Default Skin is loaded with the custom setting being applied.
            @use \"" . $this->getSystemStylesConf()->getRelDelosPath() . "\" with ( \n".
            $replacement_start . "\n" .
            $replacement_end . "\n" .
            ");" . "\n" .
            "// Apply/load other styling changes here.";
    }

    /**
     * Copies (resets) the settings files from delos
     */
    public function copySettingsFromDefault(ilSkinStyle $style): ilSystemStyleScssSettings
    {
        if (is_dir($this->getScssSettingsPath($style->getId()))) {
            $this->file_system->removeResourceDirectory($this->getSkinDirectory(), $style->getId()."/".$this->getScssSettingsFolderName());
        }

        $this->file_system->createResourceDirectory(
            $this->getSystemStylesConf()->getDefaultSettingsPath(),
            $this->getScssSettingsPath($style->getId())
        );

        $settings = new ilSystemStyleScssSettings($this->getScssSettingsPath($style->getId()));

        $settings->readAndreplaceContentOfFolder([
            "@use \"../030-tools/" => "@use \"../../../../../../templates/default/030-tools/",
            "@use \"../050-layout/" => "@use \"../../../../../../templates/default/050-layout/"
        ]);

        return $settings;
    }

    /**
     * Copies (resets) the images from delos
     */
    public function resetImages(ilSkinStyle $style): void
    {
        $this->file_system->recursiveRemoveDir($this->getSkinDirectory() . $style->getImageDirectory());
        $this->file_system->createResourceDirectory(
            $this->getSystemStylesConf()->getDefaultImagesPath(),
            $this->getImagesStylePath($style->getId())
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

        $this->file_system->saveDeleteFile($this->getScssFilePath($style->getId()));
        $this->file_system->saveDeleteFile($this->getCSSFilePath($style->getId()));
        $this->file_system->removeResourceDirectory($this->getSkinDirectory(), $style->getId(), false);

        $this->getSkin()->removeStyle($style->getId());

        $this->file_system->removeResourceDirectory(
            $this->getSkinDirectory(),
            $style->getImageDirectory()
        );
        $this->file_system->removeResourceDirectory(
            $this->getSkinDirectory(),
            $style->getFontDirectory()
        );
        $this->file_system->removeResourceDirectory(
            $this->getSkinDirectory(),
            $style->getSoundDirectory()
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
        $skin_directory = $this->getSkinDirectory(); // parent of skin directory
        $output_file = dirname($skin_directory) . '/' . $this->getSkin()->getId() . '.zip';

        ilFileUtils::zip($skin_directory, $output_file, true);

        return $output_file;
    }

    /**
     * @throws ilSystemStyleException
     */
    public function compileScss(string $style_id): void
    {
        if (!PATH_TO_SCSS) {
            throw new ilSystemStyleException(ilSystemStyleException::SCSS_NOT_INSTALLED);
        }

        $output = shell_exec(PATH_TO_SCSS . ' ' . $this->getScssFilePath($style_id));
        if (!$output) {
            $Scss_error = shell_exec(PATH_TO_SCSS . ' ' . $this->getScssFilePath($style_id) . ' 2>&1');
            if (!$Scss_error) {
                throw new ilSystemStyleException(
                    ilSystemStyleException::SCSS_COMPILE_FAILED,
                    'Empty css output, unknown error.'
                );
            }
            throw new ilSystemStyleException(ilSystemStyleException::SCSS_COMPILE_FAILED, $Scss_error);
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
        return $this->getSkinDirectory() . $style_id . "/".$this->getSkin()->getStyle($style_id)->getCssFile() . '.css';
    }

    public function getScssFilePath(string $style_id): string
    {
        return $this->getSkinDirectory() . $style_id . "/".$this->getSkin()->getStyle($style_id)->getCssFile() . '.scss';
    }

    public function getScssSettingsPath(string $style_id): string
    {
        return $this->getSkinDirectory() . $style_id . "/".$this->getScssSettingsFolderName();
    }

    public function getScssSettingsFolderName(): string
    {
        return  $this->system_styles_conf->getScssSettingsFolderName();
    }

    public function getImagesStylePath(string $style_id): string
    {
        return $this->getSkinDirectory().$style_id."/".$this->getSkin()->getStyle($style_id)->getImageDirectory();
    }

    public function getSoundsStylePath(string $style_id): string
    {
        return $this->getSkinDirectory().$style_id."/".$this->getSkin()->getStyle($style_id)->getSoundDirectory();
    }

    public function getFontsStylePath(string $style_id): string
    {
        return $this->getSkinDirectory().$style_id."/".$this->getSkin()->getStyle($style_id)->getFontDirectory();
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
