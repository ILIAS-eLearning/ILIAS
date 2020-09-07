<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Rich Text Editor base class
 * This class provides access methods to a Rich Text Editor (RTE)
 * integrated in ILIAS
 * @author        Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version       $Id$
 * @module        class.ilRTE.php
 */
class ilRTE
{
    const ILIAS_IMG_MANAGER_PLUGIN = 'ilias_image_manager_plugin';

    /**
     * Additional plugins for the rich text editor
     * @var array
     */
    protected $plugins = array();

    /**
     * @var array
     */
    protected $buttons = array();

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilBrowser
     */
    protected $browser;

    /**
     * @var ilIniFile
     */
    protected $client_init;

    /**
     * @var integer|null
     */
    protected $initialWidth = null;

    /**
     * RTE root block element which surrounds the generated html
     * @var string
     */
    protected $root_block_element = null;

    /**
     * Array of RTE buttons which should be disabled
     * @var array
     */
    protected $disabled_buttons = array();

    /**
     * ilRTE constructor.
     * @param string $a_version
     */
    public function __construct($a_version = '')
    {
        global $DIC;

        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->browser = $DIC['ilBrowser'];
        $this->client_init = $DIC['ilClientIniFile'];
        $this->user = $DIC['ilUser'];
    }

    /**
     * Adds a plugin to the plugin list
     * @param string $a_plugin_name The name of the plugin
     */
    public function addPlugin($a_plugin_name)
    {
        array_push($this->plugins, $a_plugin_name);
    }

    /**
     * Adds a button to the button list
     * @param string $a_button_name The name of the button
     */
    public function addButton($a_button_name)
    {
        array_push($this->buttons, $a_button_name);
    }

    /**
     * Removes a plugin from the plugin list
     * @param string $a_plugin_name The name of the plugin
     */
    public function removePlugin($a_plugin_name)
    {
        $key = array_search($a_plugin_name, $this->plugins);
        if ($key !== false) {
            unset($this->plugins[$key]);
        }
    }

    /**
     * Removes all plugins from instance
     */
    public function removeAllPlugins()
    {
        foreach ($this->plugins as $plugin) {
            $this->removePlugin($plugin);
        }
    }

    /**
     * Removes a button from the button list
     * @param string $a_button_name The name of the button
     */
    public function removeButton($a_button_name)
    {
        $key = array_search($a_button_name, $this->buttons);
        if ($key !== false) {
            unset($this->buttons[$key]);
        }
    }

    /**
     * Adds support for an RTE in an ILIAS form
     * @param $obj_id            integer
     * @param $obj_type          string
     * @param $a_module          string
     * @param $allowFormElements bool
     * @param $cfg_template      bool
     * @param $hide_switch       bool
     */
    public function addRTESupport($obj_id, $obj_type, $a_module = "", $allowFormElements = false, $cfg_template = null, $hide_switch = false)
    {
    }

    /**
     * Adds support for an user text editor
     * @param $editor_selector string
     */
    public function addUserTextEditor($editor_selector)
    {
    }

    /**
     * Adds custom support for an RTE in an ILIAS form
     * @param $obj_id   integer
     * @param $obj_type string
     * @param $tags     array
     */
    public function addCustomRTESupport($obj_id, $obj_type, array $tags)
    {
    }

    /**
     * @return string
     */
    public static function _getRTEClassname()
    {
        require_once 'Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';
        switch (ilObjAdvancedEditing::_getRichTextEditor()) {
            case 'tinymce':
                return 'ilTinyMCE';
                break;

            default:
                return 'ilRTE';
                break;
        }
    }

    /**
     * Synchronises appearances of media objects in $a_text with media object usage table
     * @param    string $a_text       text, including media object tags
     * @param    string $a_usage_type type of context of usage, e.g. cat:html
     * @param    int    $a_usage_id   if of context of usage, e.g. category id
     */
    public static function _cleanupMediaObjectUsage($a_text, $a_usage_type, $a_usage_id)
    {
        require_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';

        $mobs = ilObjMediaObject::_getMobsOfObject($a_usage_type, $a_usage_id);
        while (preg_match("/data\/" . CLIENT_ID . "\/mobs\/mm_([0-9]+)/i", $a_text, $found)) {
            $a_text = str_replace($found[0], "", $a_text);
            if (!in_array($found[1], $mobs)) {
                // save usage if missing
                ilObjMediaObject::_saveUsage($found[1], $a_usage_type, $a_usage_id);
            } else {
                // if already saved everything ok -> take mob out of mobs array
                unset($mobs[$found[1]]);
            }
        }
        // remaining usages are not in text anymore -> delete them
        // and media objects (note: delete method of ilObjMediaObject
        // checks whether object is used in another context; if yes,
        // the object is not deleted!)
        foreach ($mobs as $mob) {
            ilObjMediaObject::_removeUsage($mob, $a_usage_type, $a_usage_id);
            $mob_obj = new ilObjMediaObject($mob);
            $mob_obj->delete();
        }
    }

    /**
     * Replaces image source from mob image urls with the mob id or replaces mob id with the correct image source
     * @param    string $a_text      text, including media object tags
     * @param  integer  $a_direction 0 to replace image src => mob id, 1 to replace mob id => image src
     * @return string The text containing the replaced media object src
     */
    public static function _replaceMediaObjectImageSrc($a_text, $a_direction = 0, $nic = IL_INST_ID)
    {
        if (!strlen($a_text)) {
            return '';
        }

        switch ($a_direction) {
            case 0:
                $a_text = preg_replace('/src="([^"]*?\/mobs\/mm_([0-9]+)\/.*?)\"/', 'src="il_' . IL_INST_ID . '_mob_\\2"', $a_text);
                break;

            default:
                require_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
                $resulttext = $a_text;
                if (preg_match_all('/src="il_([0-9]+)_mob_([0-9]+)"/', $a_text, $matches)) {
                    foreach ($matches[2] as $idx => $mob) {
                        if (ilObject::_lookupType($mob) == "mob") {
                            $mob_obj = new ilObjMediaObject($mob);
                            $replace = "il_" . $matches[1][$idx] . "_mob_" . $mob;
                            require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
                            $path_to_file = ilWACSignedPath::signFile(ILIAS_HTTP_PATH . "/data/" . CLIENT_ID . "/mobs/mm_" . $mob . "/" . $mob_obj->getTitle());
                            $resulttext = str_replace("src=\"$replace\"", "src=\"" . $path_to_file . "\"", $resulttext);
                        }
                    }
                }
                $a_text = $resulttext;
                break;
        }

        return $a_text;
    }

    /**
     * Returns all media objects found in the passed string
     * @param  string  $a_text      text, including media object tags
     * @param  integer $a_direction 0 to find image src, 1 to find mob id
     * @return array array of media objects
     */
    public static function _getMediaObjects($a_text, $a_direction = 0)
    {
        if (!strlen($a_text)) {
            return array();
        }

        require_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';

        $mediaObjects = array();
        switch ($a_direction) {
            case 0:
                if (preg_match_all('/src="([^"]*?\/mobs\/mm_([0-9]+)\/.*?)\"/', $a_text, $matches)) {
                    foreach ($matches[2] as $idx => $mob) {
                        if (ilObjMediaObject::_exists($mob) && !in_array($mob, $mediaObjects)) {
                            $mediaObjects[] = $mob;
                        }
                    }
                }
                break;

            default:
                if (preg_match_all('/src="il_([0-9]+)_mob_([0-9]+)"/', $a_text, $matches)) {
                    foreach ($matches[2] as $idx => $mob) {
                        if (ilObjMediaObject::_exists($mob) && !in_array($mob, $mediaObjects)) {
                            $mediaObjects[] = $mob;
                        }
                    }
                }
                break;
        }

        return $mediaObjects;
    }

    /**
     * Setter for the RTE root block element
     * @param string $a_root_block_element Root block element
     * @return self
     */
    public function setRTERootBlockElement($a_root_block_element)
    {
        $this->root_block_element = $a_root_block_element;
        return $this;
    }

    /**
     * Getter for the RTE root block element
     * @return string Root block element of the RTE
     */
    public function getRTERootBlockElement()
    {
        return $this->root_block_element;
    }

    /**
     * Sets buttons which should be disabled in the RTE
     * @param array|string $a_button Either a button string or an array of button strings
     * @return self
     */
    public function disableButtons($a_button)
    {
        if (is_array($a_button)) {
            $this->disabled_buttons = array_unique(array_merge($this->disabled_buttons, $a_button));
        } else {
            $this->disabled_buttons = array_unique(array_merge($this->disabled_buttons, array($a_button)));
        }

        return $this;
    }

    /**
     * Returns the disabled RTE buttons
     * @param bool $as_array Should the disabled buttons be returned as a string or as an array
     * @return array|string
     */
    public function getDisabledButtons($as_array = true)
    {
        if (!$as_array) {
            return implode(',', $this->disabled_buttons);
        } else {
            return $this->disabled_buttons;
        }
    }

    /**
     * @return integer
     */
    public function getInitialWidth()
    {
        return $this->initialWidth;
    }

    /**
     * @param integer $initialWidth
     */
    public function setInitialWidth($initialWidth)
    {
        $this->initialWidth = $initialWidth;
    }
}
