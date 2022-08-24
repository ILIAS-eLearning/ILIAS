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
 * Class ilUIHookPluginGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUIHookPluginGUI
{
    protected ?ilUserInterfaceHookPlugin $plugin_object = null;

    public const UNSPECIFIED = '';
    public const KEEP = '';
    public const REPLACE = 'r';
    public const APPEND = 'a';
    public const PREPEND = 'p';


    final public function setPluginObject(ilUserInterfaceHookPlugin $a_val): void
    {
        $this->plugin_object = $a_val;
    }

    final public function getPluginObject(): ?ilUserInterfaceHookPlugin
    {
        return $this->plugin_object;
    }


    /**
     * @return array array with entries "mode" => modification mode, "html" => your html
     * @deprecated Note this method is deprecated. There are several issues with hacking into already rendered html
     *             as provided here:
     *             - The generation of html might be performed twice (especially if REPLACE is used).
     *             - There is limited access to data used to generate the original html. If needed this data needs to be gathered again.
     *             - If an element inside the html needs to be changed, some crude string replace magic is needed.
     *
     *
     * Modify HTML output of GUI elements. Modifications modes are:
     * - ilUIHookPluginGUI::KEEP (No modification)
     * - ilUIHookPluginGUI::REPLACE (Replace default HTML with your HTML)
     * - ilUIHookPluginGUI::APPEND (Append your HTML to the default HTML)
     * - ilUIHookPluginGUI::PREPEND (Prepend your HTML to the default HTML)
     *
     */
    public function getHTML(
        string $a_comp,
        string $a_part,
        array $a_par = array()
    ): array {
        return array('mode' => self::KEEP, 'html' => '');
    }


    /**
     * @deprecated Note this method is deprecated. User Interface components are migrated towards the UIComponents and
     *             Global Screen which do not make use of the mechanism provided here. Make use of the extension possibilities provided
     *             by Global Screen and UI Components instead.
     *
     * In ILIAS 6.0 still working for working for:
     * - $a_comp="Services/Ini" ; $a_part="init_style"
     * - $a_comp="" ; $a_part="tabs"
     * - $a_comp="" ; $a_part="sub_tabs"
     *
     * Allows to modify user interface objects before they generate their output.
     *
     */
    public function modifyGUI(
        string $a_comp,
        string $a_part,
        array $a_par = array()
    ): void {
    }


    /**
     * @deprecated Reason, see getHTML
     *
     * Modify HTML based on default html and plugin response
     */
    final public function modifyHTML(
        string $a_def_html,
        array $a_resp
    ): string {
        switch ($a_resp['mode']) {
            case self::REPLACE:
                $a_def_html = $a_resp['html'];
                break;
            case self::APPEND:
                $a_def_html .= $a_resp['html'];
                break;
            case self::PREPEND:
                $a_def_html = $a_resp['html'] . $a_def_html;
                break;
        }

        return $a_def_html;
    }


    /**
     * Goto script hook
     *
     * Can be used to interfere with the goto script behaviour
     */
    public function gotoHook(): void
    {
    }


    /**
     * Goto script hook
     *
     * Can be used to interfere with the goto script behaviour
     */
    public function checkGotoHook(string $a_target): array
    {
        return array('target' => false);
    }
}
