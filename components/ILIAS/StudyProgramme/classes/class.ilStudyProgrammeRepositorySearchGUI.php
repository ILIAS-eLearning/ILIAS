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
* Custom repository search gui class for study programme to make it possible
* to get a handle on users selected in the repository search gui.
*
* @ilCtrl_Calls ilStudyProgrammeRepositorySearchGUI: ilPropertyFormGUI
*/
class ilStudyProgrammeRepositorySearchGUI extends ilRepositorySearchGUI
{
    public function addUser(): void
    {
        global $DIC;
        $post_wrapper = $DIC->http()->wrapper()->post();
        $refinery = $DIC->refinery();
        $class = $this->callback['class'];
        $method = $this->callback['method'];

        // call callback if that function does give a return value => show error message
        // listener redirects if everything is ok.
        $class->$method($post_wrapper->retrieve('user', $refinery->kindlyTo()->listOf($refinery->kindlyTo()->string())));
        // Removed this from overwritten class, as we do not want to show the
        // results again...
        //$this->showSearchResults();
    }

    /**
     * This is just the same as in the parent class, except for the hardcoded class name.
     */
    public static function fillAutoCompleteToolbar(
        $parent_object,
        ilToolbarGUI $toolbar = null,
        $a_options = array(),
        $a_sticky = false
    ): ilToolbarGUI {
        global $DIC;
        $ilToolbar = $DIC['ilToolbar'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tree = $DIC['tree'];
        $ui_factory = $DIC['ui.factory'];

        if (!$toolbar instanceof ilToolbarGUI) {
            $toolbar = $ilToolbar;
        }

        // Fill default options
        if (!isset($a_options['auto_complete_name'])) {
            $a_options['auto_complete_name'] = $lng->txt('obj_user');
        }
        if (!isset($a_options['auto_complete_size'])) {
            $a_options['auto_complete_size'] = 15;
        }
        if (!isset($a_options['submit_name'])) {
            $a_options['submit_name'] = $lng->txt('btn_add');
        }

        $ajax_url = $ilCtrl->getLinkTargetByClass(
            array(get_class($parent_object),'ilStudyProgrammeRepositorySearchGUI'),
            'doUserAutoComplete',
            '',
            true,
            false
        );

        $ul = new ilTextInputGUI($a_options['auto_complete_name'], 'user_login');
        $ul->setDataSource($ajax_url);
        $ul->setSize($a_options['auto_complete_size']);
        if (!$a_sticky) {
            $toolbar->addInputItem($ul, true);
        } else {
            $toolbar->addStickyItem($ul, true);
        }

        if (count($a_options['user_type'] ?? [])) {
            $si = new ilSelectInputGUI("", "user_type");
            $si->setOptions($a_options['user_type']);
            if (!$a_sticky) {
                $toolbar->addInputItem($si);
            } else {
                $toolbar->addStickyItem($si);
            }
        }

        $button = $ui_factory->button()->standard($a_options['submit_name'], '#')
        ->withOnLoadCode(
            fn($id) => "
                document.getElementById('$id').addEventListener('click',
                function() {
                    let frm = this.closest('form');
                    frm.action = frm.action + '&cmd=addUserFromAutoComplete';
                    frm.submit();
                    return false;
                });
            "
        );
        $toolbar->addComponent($button);

        if ((bool) $a_options['add_search'] ||
            is_numeric($a_options['add_from_container'])) {
            $lng->loadLanguageModule("search");

            $toolbar->addSeparator();

            if ((bool) $a_options['add_search']) {
                $link = $ui_factory->link()->standard(
                    $lng->txt('search_users'),
                    $ilCtrl->getLinkTargetByClass('ilStudyProgrammeRepositorySearchGUI', '')
                );
                $toolbar->addComponent($link);
            }

            if (isset($a_options['add_from_container']) && is_numeric($a_options['add_from_container'])) {
                $parent_ref_id = (int) $a_options['add_from_container'];
                $parent_container_ref_id = $tree->checkForParentType($parent_ref_id, "grp");
                $parent_container_type = "grp";
                if (!$parent_container_ref_id) {
                    $parent_container_ref_id = $tree->checkForParentType($parent_ref_id, "crs");
                    $parent_container_type = "crs";
                }
                if ($parent_container_ref_id) {
                    if ((bool) $a_options['add_search']) {
                        $toolbar->addSpacer();
                    }

                    $ilCtrl->setParameterByClass(
                        'ilStudyProgrammeRepositorySearchGUI',
                        "list_obj",
                        ilObject::_lookupObjId($parent_container_ref_id)
                    );

                    $link = $ui_factory->link()->standard(
                        $lng->txt('search_add_members_from_container_' . $parent_container_type),
                        $ilCtrl->getLinkTargetByClass(
                            [get_class($parent_object),'ilStudyProgrammeRepositorySearchGUI'],
                            'listUsers'
                        )
                    );
                    $toolbar->addComponent($link);
                }
            }
        }

        $toolbar->setFormAction(
            $ilCtrl->getFormActionByClass(
                array(
                    get_class($parent_object),
                    'ilStudyProgrammeRepositorySearchGUI')
            )
        );
        return $toolbar;
    }
}
