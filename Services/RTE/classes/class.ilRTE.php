<?php declare(strict_types=1);

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

use ILIAS\HTTP\Agent\AgentDetermination;

/**
 * Rich Text Editor base class
 * This class provides access methods to a Rich Text Editor (RTE)
 * integrated in ILIAS
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilRTE
{
    public const ILIAS_IMG_MANAGER_PLUGIN = 'ilias_image_manager_plugin';

    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected AgentDetermination $browser;
    protected ilIniFile $client_init;
    protected ?int $initialWidth = null;

    /**
     * RTE root block element which surrounds the generated html
     * @var string|null
     */
    protected ?string $root_block_element = null;

    /** @var string[] */
    protected array $plugins = [];

    /** @var string[] */
    protected array $buttons = [];

    /**
     * Array of RTE buttons which should be disabled
     * @var string[]
     */
    protected array $disabled_buttons = [];

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->browser = $DIC->http()->agent();
        $this->client_init = $DIC['ilClientIniFile'];
        $this->user = $DIC['ilUser'];
    }

    public function addPlugin(string $a_plugin_name) : void
    {
        $this->plugins[] = $a_plugin_name;
    }

    public function addButton(string $a_button_name) : void
    {
        $this->buttons[] = $a_button_name;
    }

    public function removePlugin(string $a_plugin_name) : void
    {
        $key = array_search($a_plugin_name, $this->plugins, true);
        if ($key !== false) {
            unset($this->plugins[$key]);
        }
    }

    public function removeAllPlugins() : void
    {
        foreach ($this->plugins as $plugin) {
            $this->removePlugin($plugin);
        }
    }

    public function removeButton(string $a_button_name) : void
    {
        $key = array_search($a_button_name, $this->buttons, true);
        if ($key !== false) {
            unset($this->buttons[$key]);
        }
    }

    public function addRTESupport(
        int $obj_id,
        string $obj_type,
        string $a_module = '',
        bool $allowFormElements = false,
        ?string $cfg_template = null,
        bool $hide_switch = false
    ) : void {
    }

    public function addUserTextEditor(string $editor_selector) : void
    {
    }

    /**
     * Adds custom support for an RTE in an ILIAS form
     * @param int $obj_id
     * @param string $obj_type
     * @param string[] $tags
     */
    public function addCustomRTESupport(int $obj_id, string $obj_type, array $tags) : void
    {
    }

    public static function _getRTEClassname() : string
    {
        $editor = ilObjAdvancedEditing::_getRichTextEditor();
        if (strtolower($editor) === 'tinymce') {
            return ilTinyMCE::class;
        }

        return self::class;
    }

    /**
     * Synchronises appearances of media objects in $a_text with media object usage table
     * @param string $a_text text, including media object tags
     * @param string $a_usage_type type of context of usage, e.g. cat:html
     * @param int $a_usage_id if of context of usage, e.g. category id
     */
    public static function _cleanupMediaObjectUsage(string $a_text, string $a_usage_type, int $a_usage_id) : void
    {
        $mobs = ilObjMediaObject::_getMobsOfObject($a_usage_type, $a_usage_id);
        while (preg_match("/data\/" . CLIENT_ID . "\/mobs\/mm_([0-9]+)/i", $a_text, $found)) {
            $a_text = str_replace($found[0], '', $a_text);
            $found_mob_id = (int) $found[1];

            if (!in_array($found_mob_id, $mobs, true)) {
                // save usage if missing
                ilObjMediaObject::_saveUsage($found_mob_id, $a_usage_type, $a_usage_id);
            } else {
                // if already saved everything ok -> take mob out of mobs array
                unset($mobs[$found_mob_id]);
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
     * @param string $a_text text, including media object tags
     * @param integer $a_direction 0 to replace image src => mob id, 1 to replace mob id => image src
     * @param string $nic
     * @return string The text containing the replaced media object src
     */
    public static function _replaceMediaObjectImageSrc(
        string $a_text,
        int $a_direction = 0,
        string $nic = ''
    ) : string {
        if ($a_text === '') {
            return '';
        }

        if ($nic === '' && defined('IL_INST_ID')) {
            $nic = (string) IL_INST_ID;
        }

        if ($a_direction === 0) {
            $a_text = preg_replace(
                '/src="([^"]*?\/mobs\/mm_([0-9]+)\/.*?)\"/',
                'src="il_' . $nic . '_mob_\\2"',
                $a_text
            );
        } else {
            $resulttext = $a_text;
            if (preg_match_all('/src="il_([0-9]+)_mob_([0-9]+)"/', $a_text, $matches)) {
                foreach ($matches[2] as $idx => $mob) {
                    if (ilObject::_lookupType((int) $mob) === 'mob') {
                        $mob_obj = new ilObjMediaObject((int) $mob);
                        $replace = 'il_' . $matches[1][$idx] . '_mob_' . $mob;
                        $path_to_file = ilWACSignedPath::signFile(
                            ILIAS_HTTP_PATH . '/data/' . CLIENT_ID . '/mobs/mm_' . $mob . '/' . $mob_obj->getTitle()
                        );
                        $resulttext = str_replace("src=\"$replace\"", "src=\"" . $path_to_file . "\"", $resulttext);
                    }
                }
            }
            $a_text = $resulttext;
        }

        return $a_text;
    }

    /**
     * Returns all media objects found in the passed string
     * @param string $a_text text, including media object tags
     * @param integer $a_direction 0 to find image src, 1 to find mob id
     * @return int[] Array of media object ids
     */
    public static function _getMediaObjects(string $a_text, int $a_direction = 0) : array
    {
        if ($a_text === '') {
            return [];
        }

        $mediaObjects = [];
        if ($a_direction === 0) {
            $is_matching = preg_match_all('/src="([^"]*?\/mobs\/mm_([0-9]+)\/.*?)\"/', $a_text, $matches);
        } else {
            $is_matching = preg_match_all('/src="il_([0-9]+)_mob_([0-9]+)"/', $a_text, $matches);
        }

        if ($is_matching) {
            foreach ($matches[2] as $idx => $mob) {
                $mob = (int) $mob;

                if (ilObjMediaObject::_exists($mob) && !in_array($mob, $mediaObjects, true)) {
                    $mediaObjects[] = $mob;
                }
            }
        }

        return $mediaObjects;
    }

    public function setRTERootBlockElement(?string $a_root_block_element) : self
    {
        $this->root_block_element = $a_root_block_element;
        return $this;
    }

    public function getRTERootBlockElement() : ?string
    {
        return $this->root_block_element;
    }

    /**
     * Sets buttons which should be disabled in the RTE
     * @param string[]|string $a_button Either a button string or an array of button strings
     * @return self
     */
    public function disableButtons($a_button) : self
    {
        if (is_array($a_button)) {
            $this->disabled_buttons = array_unique(array_merge($this->disabled_buttons, $a_button));
        } else {
            $this->disabled_buttons = array_unique(array_merge($this->disabled_buttons, [$a_button]));
        }

        return $this;
    }

    /**
     * Returns the disabled RTE buttons
     * @param bool $as_list Should the disabled buttons be returned as a string or as an array
     * @return string[]|string
     */
    public function getDisabledButtons(bool $as_list = true)
    {
        if (!$as_list) {
            return implode(',', $this->disabled_buttons);
        }

        return $this->disabled_buttons;
    }

    public function getInitialWidth() : ?int
    {
        return $this->initialWidth;
    }

    public function setInitialWidth(?int $initialWidth) : void
    {
        $this->initialWidth = $initialWidth;
    }
}
