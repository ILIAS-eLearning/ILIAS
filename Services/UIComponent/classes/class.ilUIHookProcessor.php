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
 * Class ilUIHookProcessor
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUIHookProcessor
{
    private bool $replaced = false;
    protected array $append = [];
    protected array $prepend = [];
    protected string $replace = '';

    public function __construct(
        string $a_comp,
        string $a_part,
        array $a_pars
    ) {
        global $DIC;

        $component_factory = $DIC["component.factory"];

        // user interface hook [uihk]
        foreach ($component_factory->getActivePluginsInSlot("uihk") as $plugin) {
            /**
             * @var $gui_class ilUIHookPluginGUI
             */
            $gui_class = $plugin->getUIClassInstance();
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
    public function replaced(): bool
    {
        return $this->replaced;
    }

    public function getHTML(string $html): string
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
