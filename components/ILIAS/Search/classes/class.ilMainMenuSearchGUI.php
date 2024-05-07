<?php

declare(strict_types=1);

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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * Add a search box to main menu
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 *
 * @ingroup ServicesSearch
 */
class ilMainMenuSearchGUI
{
    protected ?ilTemplate $tpl = null;
    protected ilLanguage $lng;
    protected ilTree $tree;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;

    private GlobalHttpState $http;
    private Factory $refinery;


    private int $ref_id;
    private bool $isContainer = true;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("search");
        $this->tree = $DIC->repositoryTree();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $DIC->ui()->mainTemplate()->addJavascript('assets/js/SearchMainMenu.js');

        $this->initRefIdFromQuery();
    }

    protected function initRefIdFromQuery(): void
    {
        $this->ref_id = ROOT_FOLDER_ID;
        if ($this->http->wrapper()->query()->has('ref_id')) {
            $this->ref_id = $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
    }

    public function getHTML(): string
    {
        iljQueryUtil::initjQuery();
        iljQueryUtil::initjQueryUI();

        $this->tpl = new ilTemplate('tpl.main_menu_search.html', true, true, 'components/ILIAS/Search');
        if ($this->user->getId() != ANONYMOUS_USER_ID) {
            $this->tpl->setVariable('LABEL_SEARCH_OPTIONS', $this->lng->txt("label_search_options"));
            if (ilSearchSettings::getInstance()->isLuceneUserSearchEnabled() || ($this->ref_id != ROOT_FOLDER_ID)) {
                $this->tpl->setCurrentBlock("position");
                $this->tpl->setVariable('TXT_GLOBALLY', $this->lng->txt("search_globally"));
                $this->tpl->setVariable('ROOT_ID', ROOT_FOLDER_ID);
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock("position_hid");
                $this->tpl->setVariable('ROOT_ID_HID', ROOT_FOLDER_ID);
                $this->tpl->parseCurrentBlock();
            }
            if ($this->ref_id != ROOT_FOLDER_ID) {
                $this->tpl->setCurrentBlock('position_rep');
                $this->tpl->setVariable('TXT_CURRENT_POSITION', $this->lng->txt("search_at_current_position"));
                $this->tpl->setVariable('REF_ID', $this->ref_id);
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($this->user->getId() != ANONYMOUS_USER_ID && ilSearchSettings::getInstance()->isLuceneUserSearchEnabled()) {
            $this->tpl->setCurrentBlock('usr_search');
            $this->tpl->setVariable('TXT_USR_SEARCH', $this->lng->txt('search_users'));
            $this->tpl->setVariable('USER_SEARCH_ID', ilSearchControllerGUI::TYPE_USER_SEARCH);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable(
            'FORMACTION',
            $this->ctrl->getFormActionByClass(
                ilSearchControllerGUI::class,
                'remoteSearch'
            )
        );
        $this->tpl->setVariable('BTN_SEARCH', $this->lng->txt('search'));
        $this->tpl->setVariable('SEARCH_INPUT_LABEL', $this->lng->txt('search_field'));
        $this->tpl->setVariable(
            'AC_DATASOURCE',
            $this->ctrl->getLinkTargetByClass(
                ilSearchControllerGUI::class,
                'autoComplete',
                null,
                true
            )
        );

        $this->tpl->setVariable('IMG_MM_SEARCH', ilUtil::img(
            ilUtil::getImagePath("standard/icon_seas.svg"),
            $this->lng->txt("search")
        ));

        if ($this->user->getId() != ANONYMOUS_USER_ID) {
            $this->tpl->setVariable(
                'HREF_SEARCH_LINK',
                'ilias.php?baseClass=' . ilSearchControllerGUI::class
            );
            $this->tpl->setVariable('TXT_SEARCH_LINK', $this->lng->txt("last_search_result"));
        }
        // #10555 - we need the overlay for the autocomplete which is always active
        $this->tpl->setVariable('TXT_SEARCH', $this->lng->txt("search"));
        $ov = new ilOverlayGUI("mm_search_menu");
        //$ov->setTrigger("main_menu_search", "none",
        //	"main_menu_search", "tr", "br");
        //$ov->setAnchor("main_menu_search", "tr", "br");
        $ov->setAutoHide(false);
        $ov->add();

        return $this->tpl->get();
    }
}
