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

namespace ILIAS\Administration;

use ilCtrl;
use ilInstallationHeadingTableGUI;
use ilUtil;
use ilGlobalTemplateInterface;
use ilLanguage;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UICore\GlobalTemplate;

/**
 * GUI to change the header title of the installation
 *
 * @ilCtrl_isCalledBy   ILIAS\Administration\HeaderTitleGUI: ilObjGeneralSettingsGUI
 */
readonly class HeaderTitleGUI
{
    public function __construct(
        private ilCtrl $ctrl,
        private ilGlobalTemplateInterface $tpl,
        private ilLanguage $lng,
        private ServerRequestInterface $request,
        private HeaderTitleRepo $repo,
        private bool $has_write_access,
    ) {
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("view");
        switch ($cmd) {
            case 'view':
                $this->view();
                break;

            case 'add':
            case 'save':
            case 'delete':
                if ($this->has_write_access) {
                    $this->$cmd();
                }
                break;
        }
    }

    /**
     * Show header title
     */
    public function view(
        $a_get_post_values = false,
        bool $add_entry = false
    ): void {
        $table = new ilInstallationHeadingTableGUI($this, "view", $this->has_write_access);
        $post = $this->request->getParsedBody();

        if ($a_get_post_values) {
            $vals = array();
            foreach (($post["title"] ?? []) as $k => $v) {
                $def = $post["default"] ?? "";
                $vals[] = array("title" => $v,
                                "desc" => ($post["desc"][$k] ?? ""),
                                "lang" => ($post["lang"][$k] ?? ""),
                                "default" => ($def == $k));
            }
            if ($add_entry) {
                $vals[] = array("title" => "",
                                "desc" => "",
                                "lang" => "",
                                "default" => false);
            }
            $table->setData($vals);
        } else {
            $data = $this->repo->getHeaderTitleTranslations();
            if (isset($data["Fobject"]) && is_array($data["Fobject"])) {
                foreach ($data["Fobject"] as $k => $v) {
                    if ($k == $data["default_language"]) {
                        $data["Fobject"][$k]["default"] = true;
                    } else {
                        $data["Fobject"][$k]["default"] = false;
                    }
                }
            } else {
                $data["Fobject"] = array();
            }
            $table->setData($data["Fobject"]);
        }
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Add a header title
     */
    public function add(): void
    {
        $this->view(true, true);
    }

    /**
     * Save header titles
     */
    public function save(bool $delete = false)
    {
        $post = $this->request->getParsedBody();
        foreach ($post["title"] as $k => $v) {
            if ($delete && ($post["check"][$k] ?? false)) {
                unset($post["title"][$k]);
                unset($post["desc"][$k]);
                unset($post["lang"][$k]);
                if ($k == $post["default"]) {
                    unset($post["default"]);
                }
            }
        }

        // default language set?
        if (!isset($post["default"]) && count($post["lang"]) > 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_default_language"));
            $this->view(true);
            return;
        }

        // all languages set?
        if (array_key_exists("", $post["lang"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_language_selected"));
            $this->view(true);
            return;
        }

        // no single language is selected more than once?
        if (count(array_unique($post["lang"])) < count($post["lang"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_multi_language_selected"));
            $this->view(true);
            return;
        }

        // save the stuff
        $this->repo->removeHeaderTitleTranslations();
        foreach ($post["title"] as $k => $v) {
            $desc = $post["desc"][$k] ?? "";
            $this->repo->addHeaderTitleTranslation(
                ilUtil::stripSlashes($v),
                ilUtil::stripSlashes($post["lang"][$k]),
                ($post["default"] == $k)
            );
        }

        $this->tpl->setOnScreenMessage(GlobalTemplate::MESSAGE_TYPE_SUCCESS, $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "view");
    }

    /**
     * Remove header titles
     */
    public function delete(): void
    {
        $this->save(true);
    }
}
