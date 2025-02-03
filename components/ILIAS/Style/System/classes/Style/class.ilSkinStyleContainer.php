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

use ILIAS\Language\Language;

/**
 * This class is responsible for all file system related actions related actions of a skin such as copying files and folders,
 * generating a new skin, deleting a skin etc..
 * It contains exactly one skin containing several styles. Use this class to parse a skin from xml.
 */
class ilSkinStyleContainer
{
    protected Language $lng;

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

    public function __construct(
        Language $lng,
        ilSkin $skin,
        ilSystemStyleMessageStack $message_stack,
        ?ilSystemStyleConfig $system_styles_conf = null,
    ) {
        $this->lng = $lng;
        $this->skin = $skin;
        $this->setMessageStack($message_stack);

        if (!$system_styles_conf) {
            $this->setSystemStylesConf(new ilSystemStyleConfig());
        } else {
            $this->setSystemStylesConf($system_styles_conf);
        }
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

        if (strpos($skin_directory, '../') === 0) { // we must resolve relative paths here
            $skin_directory = realpath(__DIR__ . '/../../../../../../templates/' . $skin_directory);
        }
        $output_file = dirname($skin_directory) . '/' . $this->getSkin()->getId() . '.zip';

        ilFileUtils::zip($skin_directory, $output_file, true);

        return $output_file;
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
