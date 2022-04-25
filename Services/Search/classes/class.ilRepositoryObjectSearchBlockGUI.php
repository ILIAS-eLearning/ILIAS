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

/**
 * Class ilRepositoryObjectSearchBlockGUI
 * Repository object search
 *
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @package ServicesSearch
 *
 */
class ilRepositoryObjectSearchBlockGUI extends ilBlockGUI
{
    private \ILIAS\HTTP\GlobalHttpState $http;
    private \ILIAS\Refinery\Factory $refinery;


    public static string $block_type = "objectsearch";

    public function __construct(string $a_title)
    {
        global $DIC;

        parent::__construct();

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        
        $this->setEnableNumInfo(false);
        
        $this->setTitle($a_title);
        $this->allow_moving = false;
        $this->new_rendering = true;
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
     * Get Screen Mode for current command.
     */
    public static function getScreenMode() : string
    {
        return IL_SCREEN_SIDE;
    }

    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                $this->$cmd();
        }
    }

    public function getHTML() : string
    {
        return parent::getHTML();
    }

    public function fillDataSection() : void
    {
        $this->setDataSection($this->getLegacyContent());
    }

    /**
     * @inheritdoc
     */
    protected function getLegacyContent() : string
    {
        $tpl = new ilTemplate("tpl.search_search_block.html", true, true, 'Services/Search');

        $this->lng->loadLanguageModule('search');
        $tpl->setVariable("TXT_SEARCH_INPUT_LABEL", $this->lng->txt('search_field'));
        $tpl->setVariable("TXT_PERFORM", $this->lng->txt('btn_search'));
        $tpl->setVariable("FORMACTION", $this->ctrl->getFormActionByClass('ilrepositoryobjectsearchgui', 'performSearch'));

        $post_search_term = '';
        if ($this->http->wrapper()->post()->has('search_term')) {
            $post_search_term = $this->http->wrapper()->post()->retrieve(
                'search_term',
                $this->refinery->kindlyTo()->string()
            );
        }
        $tpl->setVariable(
            "SEARCH_TERM",
            ilLegacyFormElementsUtil::prepareFormOutput($post_search_term)
        );
        return $tpl->get();
    }
}
