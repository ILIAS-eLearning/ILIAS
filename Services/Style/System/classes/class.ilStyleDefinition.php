<?php
include_once("Services/Style/System/classes/Utilities/class.ilSkinXML.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleSkinContainer.php");
include_once("Services/Style/System/classes/class.ilSystemStyleSettings.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleConfig.php");


/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * ilStyleDefinition acts as a wrapper of style related actions. Use this class to get the systems current style.
 * Currently some of the logic is not clearly separated from ilSystemStyleSettings. This is due to legacy reasons.
 * In a future refactoring, this class might be completely merged with ilSystemStyleSettings.
 *
 * The following terminology is used:
 *
 * (system) style:
 *    A style that can be set as system style for the complete ILIAS installations. This includes, less
 *    css, fonts, icons and sounds as well as possible html tpl files to overide ILIAS templates.
 * (stystem) sub style:
 *    A sub style can be assigned to exactly one system style to be displayed for a set of categories.
 * skin:
 *    A skin can hold multiple style. A skin is defined by it's folder carrying the name of the skin and the
 *    template.xml in this exact folder, listing the skins styles and substyles. Mostly a skin caries exactly one style.
 *    Through the GUI in the administration it is not possible to define multiple style per skin. It is however possible
 *    to define multiple sub styles for one style stored in one skin.
 * template:
 *    The template is the xml file of the skin storing the skin styles and sub styles information.
 *
 *
 *
 * Skins, styles ans stub styles are always used globally (not client specific).
 *
 * This class is currently also used as global $styleDefinition.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ilStyleDefinition
{
    /**
     * currently selected style, used for caching
     *
     * @var bool
     */
    public static $current_style = false;

    /**
     * Skins available, used for caching
     * @var ilSkinXML[]
     */
    public static $skins = [];

    /**
     * Sets the current skin. This is used by the global instance of this class.
     *
     * @var ilSkinXML
     */
    protected $skin;

    /**
     * Used for caching.
     * @var array|null
     */
    protected static $cached_all_styles_information = null;

    /**
     * Used to wire this component up with the correct paths into the customizing directory.
     * This is dynamic and not constant for this class to remain testable
     *
     * @var ilSystemStyleConfigMock
     */
    protected $system_style_config;

    /**
     * ilStyleDefinition constructor.
     * @param string $skin_id
     * @param ilSystemStyleConfig|null $system_style_config
     * @throws ilSystemStyleException
     */
    public function __construct($skin_id = "", ilSystemStyleConfig $system_style_config = null)
    {
        if ($skin_id == "") {
            $skin_id = self::getCurrentSkin();
        }

        if (!$system_style_config) {
            $this->setSystemStylesConf(new ilSystemStyleConfig());
        } else {
            $this->setSystemStylesConf($system_style_config);
        }

        if ($skin_id != $this->getSystemStylesConf()->getDefaultSkinId()) {
            $this->setSkin(ilSkinXML::parseFromXML($this->getSystemStylesConf()->getCustomizingSkinPath() . $skin_id . "/template.xml"));
        } else {
            $this->setSkin(ilSkinXML::parseFromXML($this->getSystemStylesConf()->getDefaultTemplatePath()));
        }
    }

    /**
     * get the current skin
     *
     * use always this function instead of getting the account's skin
     * the current skin may be changed on the fly by setCurrentSkin()
     *
     * @return	string|null	skin id
     */
    public static function getCurrentSkin()
    {
        global $DIC;

        if (!$DIC) {
            return null;
        }
        if ($DIC->isDependencyAvailable("systemStyle") && is_object($DIC->systemStyle()->getSkin())) {
            return $DIC->systemStyle()->getSkin()->getId();
        } else {
            $system_style_conf = new ilSystemStyleConfig();

            if ($DIC->isDependencyAvailable("user") && is_object($DIC->user()) && property_exists($DIC->user(), "skin")) {
                $skin_id = $DIC->user()->skin;
                if (!self::skinExists($skin_id)) {
                    ilUtil::sendFailure($DIC->language()->txt("set_skin_does_not_exist") . " " . $skin_id);
                    $skin_id = $system_style_conf->getDefaultSkinId();
                }
                return $skin_id;
            } else {
                return null;
            }
        }
    }

    /**
     * @return ilSkinStyleXML[]
     */
    public function getStyles()
    {
        return $this->getSkin()->getStyles();
    }


    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->getSkin()->getName();
    }


    /**
     * @param $a_id
     * @return ilSkinStyleXML
     * @throws ilSystemStyleException
     */
    public function getStyle($a_id)
    {
        return $this->getSkin()->getStyle($a_id);
    }

    /**
     * @param $a_id
     * @return string
     * @throws ilSystemStyleException
     */
    public function getStyleName($a_id)
    {
        return $this->getSkin()->getStyle($a_id)->getName();
    }

    /**
     * @param $style_id
     * @return string
     * @throws ilSystemStyleException
     */
    public function getImageDirectory($style_id)
    {
        if (!$style_id) {
            throw new ilSystemStyleException(ilSystemStyleException::NO_STYLE_ID, $style_id);
        }
        if (!$this->getSkin()->getStyle($style_id)) {
            throw new ilSystemStyleException(ilSystemStyleException::NOT_EXISTING_STYLE, $style_id);
        }
        return $this->getSkin()->getStyle($style_id)->getImageDirectory();
    }

    /**
     * @param $style_id
     * @return string
     * @throws ilSystemStyleException
     */
    public function getSoundDirectory($style_id)
    {
        return $this->getSkin()->getStyle($style_id)->getSoundDirectory();
    }


    /**
     * @param ilSystemStyleConfig|null $system_style_config
     * @return ilSkinXML[]
     * @throws ilSystemStyleException
     */
    public static function getAllSkins(ilSystemStyleConfig $system_style_config = null)
    {
        if (!self::$skins) {
            if (!$system_style_config) {
                $system_style_config = new ilSystemStyleConfig();
            }

            /**
             * @var $skins ilSkinXML[]
             */
            $skins = [];
            $skins[$system_style_config->getDefaultSkinId()] = ilSkinXML::parseFromXML($system_style_config->getDefaultTemplatePath());


            if (is_dir($system_style_config->getCustomizingSkinPath())) {
                $cust_skins_directory = new RecursiveDirectoryIterator($system_style_config->getCustomizingSkinPath(), FilesystemIterator::SKIP_DOTS);
                foreach ($cust_skins_directory as $skin_folder) {
                    if ($skin_folder->isDir()) {
                        $template_path = $skin_folder->getRealPath() . "/template.xml";
                        if (file_exists($template_path)) {
                            $skin = ilSkinXML::parseFromXML($template_path);
                            $skins[$skin->getId()] = $skin;
                        }
                    }
                }
            }

            self::setSkins($skins);
        }

        return self::$skins;
    }

    /**
     * @deprecated due to bad naming.
     *
     * @return ilSkinXML[]
     * @throws ilSystemStyleException
     */
    public static function getAllTemplates()
    {
        return self::getAllSkins();
    }

    /**
     * Check whether a skin exists. Not using array_key_exists($skin_id,self::getAllSkins()); for performance reasons
     * @param	string	$skin_id
     * @param ilSystemStyleConfig|null $system_style_config
     * @return bool
     */
    public static function skinExists($skin_id, ilSystemStyleConfig $system_style_config = null)
    {
        if (!$system_style_config) {
            $system_style_config = new ilSystemStyleConfig();
        }

        if ($skin_id == $system_style_config->getDefaultSkinId()) {
            if (is_file($system_style_config->getDefaultTemplatePath())) {
                return true;
            }
        } else {
            if (is_file($system_style_config->getCustomizingSkinPath() . $skin_id . "/template.xml")) {
                return true;
            }
        }
        return false;
    }

    /**
     * get the current style or sub style
     *
     * use always this function instead of getting the account's style
     * the current style may be changed on the fly by setCurrentStyle()
     *
     * @return bool|null
     * @throws ilSystemStyleException
     */
    public static function getCurrentStyle()
    {
        global $DIC;
        
        if (self::$current_style) {
            return self::$current_style;
        }

        if (!$DIC || !$DIC->isDependencyAvailable("user")) {
            return null;
        }

        self::setCurrentStyle($DIC->user()->prefs['style']);

        if ($DIC->isDependencyAvailable("systemStyle") && self::styleExistsForCurrentSkin(self::$current_style)) {
            if ($DIC->systemStyle()->getSkin()->hasStyleSubstyles(self::$current_style)) {
                // read assignments, if given
                $assignments = ilSystemStyleSettings::getSystemStyleCategoryAssignments(self::getCurrentSkin(), self::$current_style);
                if (count($assignments) > 0) {
                    $ref_ass = [];
                    foreach ($assignments as $a) {
                        if ($DIC->systemStyle()->getSkin()->hasStyle($a["substyle"])) {
                            $ref_ass[$a["ref_id"]] = $a["substyle"];
                        }
                    }

                    $ref_id = false;
                    if ($_GET["ref_id"]) {
                        $ref_id = $_GET["ref_id"];
                    } elseif ($_GET["target"]) {
                        $target_arr = explode("_", $_GET["target"]);
                        $ref_id = $target_arr[1];
                    }

                    // check whether any ref id assigns a new style
                    if ($DIC->isDependencyAvailable("repositoryTree") && $ref_id && $DIC->repositoryTree()->isInTree($ref_id)) {
                        $path = $DIC->repositoryTree()->getPathId($ref_id);
                        for ($i = count($path) - 1; $i >= 0; $i--) {
                            if (isset($ref_ass[$path[$i]])) {
                                self::$current_style = $ref_ass[$path[$i]];
                                return self::$current_style;
                            }
                        }
                    }
                }
            }
        }

        if (!self::styleExistsForCurrentSkin(self::$current_style)) {
            ilUtil::sendFailure($DIC->language()->txt("set_style_does_not_exist") . " " . self::$current_style);
            $system_style_config = new ilSystemStyleConfig();
            self::setCurrentSkin($system_style_config->getDefaultSkinId());
            self::setCurrentStyle($system_style_config->getDefaultStyleId());
        }

        return self::$current_style;
    }

    /**
     * Get all skins/styles as array (convenient for tables)
     * Attention: tempalte_name/template_id in this array is only used for legacy reasons an might be removed in future.
     *
     * @return array|null
     * @throws ilSystemStyleException
     */
    public static function getAllSkinStyles()
    {
        global $DIC;

        if (!self::getCachedAllStylesInformation()) {
            $all_styles = [];

            $skins = $DIC->systemStyle()->getSkins();

            foreach ($skins as $skin) {
                foreach ($skin->getStyles() as $style) {
                    $num_users = ilObjUser::_getNumberOfUsersForStyle($skin->getId(), $style->getId());

                    $parent_name = "";
                    if ($style->getSubstyleOf()) {
                        $parent_name = $skin->getStyle($style->getSubstyleOf())->getName();
                    }

                    // default selection list
                    $all_styles[$skin->getId() . ":" . $style->getId()] = [
                                    "title" => $skin->getName() . " / " . $style->getName(),
                                    "id" => $skin->getId() . ":" . $style->getId(),
                                    "skin_id" => $skin->getId(),
                                    "skin_name" => $skin->getName(),
                                    "template_id" => $skin->getId(),
                                    "template_name" => $skin->getName(),
                                    "style_id" =>  $style->getId(),
                                    "style_name" => $style->getName(),
                                    "substyle_of" => $style->getSubstyleOf(),
                                    "substyle_of_name" => $parent_name,
                                    "users" => $num_users
                            ];
                }
            }
            self::setCachedAllStylesInformation($all_styles);
        }

        return self::getCachedAllStylesInformation();
    }


    /**
     * @param $a_skin
     * @throws ilSystemStyleException
     */
    public static function setCurrentSkin($a_skin)
    {
        global $DIC;

        if ($DIC->isDependencyAvailable("systemStyle") && $DIC->systemStyle()->getSkin()->getName() != $a_skin) {
            $styleDefinition = new ilStyleDefinition($a_skin);
            if (!self::styleExistsForCurrentSkin(self::$current_style)) {
                $styleDefinition->setCurrentStyle($DIC->systemStyle()->getSkin()->getDefaultStyle()->getId());
            }
        }
    }


    /**
     * @param $style_id
     * @return bool
     * @throws ilSystemStyleException
     */
    public static function styleExists($style_id)
    {
        foreach (self::getSkins() as $skin) {
            if ($skin->hasStyle($style_id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $skin_id
     * @param $style_id
     * @return bool
     * @throws ilSystemStyleException
     */
    public static function styleExistsForSkinId($skin_id, $style_id)
    {
        if (!self::skinExists($skin_id)) {
            return false;
        }
        $skin = ilSystemStyleSkinContainer::generateFromId($skin_id)->getSkin();
        return $skin->hasStyle($style_id);
    }

    /**
     * @param $style_id
     * @return bool
     */
    public static function styleExistsForCurrentSkin($style_id)
    {
        global $DIC;

        return $DIC->systemStyle()->getSkin()->hasStyle($style_id);
    }

    /**
     * @param $a_style
     */
    public static function setCurrentStyle($a_style)
    {
        self::$current_style = $a_style;
    }

    /**
     * @return ilSkinXML[]
     * @throws ilSystemStyleException
     */
    public static function getSkins()
    {
        return self::getAllSkins();
    }

    /**
     * @param ilSkinXML[] $skins
     */
    public static function setSkins($skins)
    {
        self::$skins = $skins;
    }

    /**
     * @return ilSkinXML
     */
    public function getSkin()
    {
        return $this->skin;
    }

    /**
     * @param ilSkinXML $skin
     */
    public function setSkin($skin)
    {
        $this->skin = $skin;
    }

    /**
     * @return array|null
     */
    protected static function getCachedAllStylesInformation()
    {
        return self::$cached_all_styles_information;
    }

    /**
     * @param array|null $cached_all_styles_information
     */
    protected static function setCachedAllStylesInformation($cached_all_styles_information)
    {
        self::$cached_all_styles_information = $cached_all_styles_information;
    }

    /**
     * @return ilSystemStyleConfig
     */
    public function getSystemStylesConf()
    {
        return $this->system_style_config;
    }

    /**
     * @param $system_style_config
     */
    public function setSystemStylesConf($system_style_config)
    {
        $this->system_style_config = $system_style_config;
    }
}
