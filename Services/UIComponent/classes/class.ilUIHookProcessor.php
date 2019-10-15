<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUIHookProcessor
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 */
class ilUIHookProcessor
{

    /**
     * @var bool
     */
    private $replaced = false;
    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;
    /**
     * @var array
     */
    protected $append = [];
    /**
     * @var array
     */
    protected $prepend = [];
    /**
     * @var string
     */
    protected $replace = '';


    /**
     * ilUIHookProcessor constructor.
     *
     * @param $a_comp
     * @param $a_part
     * @param $a_pars
     */
    public function __construct($a_comp, $a_part, $a_pars)
    {
        global $DIC;

        $this->plugin_admin = $DIC["ilPluginAdmin"];

        // user interface hook [uihk]
        $pl_names = ilPluginAdmin::getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        foreach ($pl_names as $pl) {
            $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            /**
             * @var $gui_class ilUIHookPluginGUI
             */
            $gui_class = $ui_plugin->getUIClassInstance();
            $resp = $gui_class->getHTML($a_comp, $a_part, $a_pars);

            $mode = $resp['mode'];
            if ($mode !== ilUIHookPluginGUI::KEEP) {
                $html = $resp['html'];
                switch ($mode) {
                    case ilUIHookPluginGUI::PREPEND:
                        $this->prepend[] = $html;
                        break;

                    case ilUIHookPluginGUI::APPEND:
                        $this->append[] = $html;
                        break;

                    case ilUIHookPluginGUI::REPLACE:
                        if (!$this->replaced) {
                            $this->replace = $html;
                            $this->replaced = true;
                        }
                        break;
                }
            }
        }
    }


    /**
     * @return bool Should HTML be replaced completely?
     */
    public function replaced()
    {
        return $this->replaced;
    }


    /**
     * @param string $html
     *
     * @return string
     */
    public function getHTML($html)
    {
        if ($this->replaced) {
            $html = $this->replace;
        }
        foreach ($this->append as $a) {
            $html .= $a;
        }
        foreach ($this->prepend as $p) {
            $html = $p . $html;
        }

        return $html;
    }
}

