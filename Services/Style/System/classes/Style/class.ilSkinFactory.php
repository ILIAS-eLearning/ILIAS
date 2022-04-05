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
 * Factory to create Skin classes holds an manages the basic data of a skin as provide by the template of the skin.
 */
class ilSkinFactory
{
    protected ilSystemStyleConfig $config;
    protected ilLanguage $lng;

    public function __construct(ilLanguage $lng, ?ilSystemStyleConfig $config = null)
    {
        $this->lng = $lng;

        if ($config) {
            $this->config = $config;
        } else {
            $this->config = new ilSystemStyleConfig();
        }
    }

    /**
     * Create Skin classes holds an manages the basic data of a skin as provide by the template of the skin.
     * @throws ilSystemStyleException
     */
    public function skinFromXML(string $path = '') : ilSkin
    {
        try {
            $xml = new SimpleXMLElement(file_get_contents($path));
        } catch (Exception $e) {
            throw new ilSystemStyleException(ilSystemStyleException::FILE_OPENING_FAILED, $path);
        }

        $id = basename(dirname($path));
        $skin = new ilSkin($id, (string) $xml->attributes()['name']);
        $skin->setVersion((string) $xml->attributes()['version']);

        /**
         * @var ilSkinStyle $last_style
         */
        $last_style = null;

        foreach ($xml->children() as $style_xml) {
            $style = ilSkinStyle::parseFromXMLElement($style_xml);

            /**
             * @var SimpleXMLElement $style_xml
             */
            if ($style_xml->getName() == 'substyle') {
                if (!$last_style) {
                    throw new ilSystemStyleException(ilSystemStyleException::NO_PARENT_STYLE, $style->getId());
                }
                $style->setSubstyleOf($last_style->getId());
            } else {
                $last_style = $style;
            }
            $skin->addStyle($style);
        }
        return $skin;
    }

    /**
     * Get container class is responsible for all file system related actions related actions of a skin such as copying files and folders,
     * generating a new skin, deleting a skin etc.
     * @throws ilSystemStyleException
     */
    public function skinStyleContainerFromId(
        string $skin_id,
        ilSystemStyleMessageStack $message_stack
    ) : ilSkinStyleContainer {
        if (!$skin_id) {
            throw new ilSystemStyleException(ilSystemStyleException::NO_SKIN_ID);
        }

        if ($skin_id != 'default') {
            return new ilSkinStyleContainer(
                $this->lng,
                $this->skinFromXML($this->config->getCustomizingSkinPath() . $skin_id . '/template.xml'),
                $message_stack,
                $this->config
            );
        } else {
            return new ilSkinStyleContainer(
                $this->lng,
                $this->skinFromXML($this->config->getDefaultTemplatePath()),
                $message_stack,
                $this->config,
            );
        }
    }

    /**
     * Imports a skin from zip
     * @throws ilSystemStyleException
     */
    public function skinStyleContainerFromZip(
        string $import_zip_path,
        string $name,
        ilSystemStyleMessageStack $message_stack
    ) : ilSkinStyleContainer {
        $skin_id = preg_replace('/[^A-Za-z0-9\-_]/', '', rtrim($name, '.zip'));

        while (ilStyleDefinition::skinExists($skin_id, $this->config)) {
            $skin_id .= 'Copy';
        }

        $skin_path = $this->config->getCustomizingSkinPath() . $skin_id;

        mkdir($skin_path, 0775, true);

        $temp_zip_path = $skin_path . '/' . $name;
        rename($import_zip_path, $temp_zip_path);

        ilFileUtils::unzip($temp_zip_path);
        unlink($temp_zip_path);

        return $this->skinStyleContainerFromId($skin_id, $message_stack);
    }

    /**
     * Copies a complete Skin
     * @throws ilSystemStyleException
     */
    public function copyFromSkinStyleContainer(
        ilSkinStyleContainer $container,
        ilFileSystemHelper $file_system,
        ilSystemStyleMessageStack $message_stack,
        string $new_skin_txt_addon = 'Copy'
    ) : ilSkinStyleContainer {
        $new_skin_id_addon = '';
        $new_skin_name_addon = '';

        while (ilStyleDefinition::skinExists(
            $container->getSkin()->getId() . $new_skin_id_addon,
            $container->getSystemStylesConf()
        )) {
            $new_skin_id_addon .= $new_skin_txt_addon;
            $new_skin_name_addon .= ' ' . $new_skin_txt_addon;
        }

        $new_skin_path = rtrim($container->getSkinDirectory(), '/') . $new_skin_id_addon;

        mkdir($new_skin_path, 0775, true);
        $file_system->recursiveCopy($container->getSkinDirectory(), $new_skin_path);
        $skin_container = $this->skinStyleContainerFromId($container->getSkin()->getId() . $new_skin_id_addon, $message_stack);
        $skin_container->getSkin()->setName($skin_container->getSkin()->getName() . $new_skin_name_addon);
        $skin_container->getSkin()->setVersion('0.1');
        $skin_container->updateSkin($skin_container->getSkin());
        return $skin_container;
    }
}
