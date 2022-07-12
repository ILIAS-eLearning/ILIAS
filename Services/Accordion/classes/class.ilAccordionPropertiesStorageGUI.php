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
 * Saves (mostly asynchronously) user properties of accordions
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAccordionPropertiesStorageGUI implements ilCtrlBaseClassInterface
{
    protected int $tab_nr;
    protected string $req_acc_id;
    protected int $user_id;
    protected \ILIAS\Accordion\StandardGUIRequest $request;
    protected ilObjUser $user;
    protected ilCtrl $ctrl;
    protected ilDBInterface $db;
    public array $properties = array(
        "opened" => array("storage" => "session")
    );

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->db = $DIC->database();
        $this->request = new \ILIAS\Accordion\StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
        $this->user_id = $this->request->getUserId();
        $this->req_acc_id = $this->request->getId();
        $this->tab_nr = $this->request->getTabNr();
    }

    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;
        $cmd = $ilCtrl->getCmd();
        $this->$cmd();
    }
    
    public function setOpenedTab() : void
    {
        $ilUser = $this->user;
        
        if ($this->user_id == $ilUser->getId()) {
            switch ($this->request->getAction()) {

                case "add":
                    $cur = $this->getProperty(
                        $this->req_acc_id,
                        $this->user_id,
                        "opened"
                    );
                    $cur_arr = explode(";", $cur);
                    if (!in_array($this->tab_nr, $cur_arr)) {
                        $cur_arr[] = $this->tab_nr;
                    }
                    $this->storeProperty(
                        $this->req_acc_id,
                        $this->user_id,
                        "opened",
                        implode(";", $cur_arr)
                    );
                    break;

                case "rem":
                    $cur = $this->getProperty(
                        $this->req_acc_id,
                        $this->user_id,
                        "opened"
                    );
                    $cur_arr = explode(";", $cur);
                    if (($key = array_search($this->tab_nr, $cur_arr)) !== false) {
                        unset($cur_arr[$key]);
                    }
                    $this->storeProperty(
                        $this->req_acc_id,
                        $this->user_id,
                        "opened",
                        implode(";", $cur_arr)
                    );
                    break;

                case "clear":
                    $this->storeProperty(
                        $this->req_acc_id,
                        $this->user_id,
                        "opened",
                        ""
                    );
                    break;

                case "set":
                default:
                    $this->storeProperty(
                        $this->req_acc_id,
                        $this->user_id,
                        "opened",
                        $this->tab_nr
                    );
                    break;
            }
        }
    }
    
    /**
     * Store property in session
     */
    public function storeProperty(
        string $a_table_id,
        int $a_user_id,
        string $a_property,
        string $a_value
    ) : void {
        switch ($this->properties[$a_property]["storage"]) {
            case "session":
                if (ilSession::has("accordion")) {
                    $acc = ilSession::get("accordion");
                }
                $acc[$a_table_id][$a_user_id][$a_property] = $a_value;
                ilSession::set("accordion", $acc);
                break;
        }
    }
    
    public function getProperty(
        string $a_table_id,
        int $a_user_id,
        string $a_property
    ) : string {
        $acc = [];
        if (ilSession::has("accordion")) {
            $acc = ilSession::get("accordion");
        }
        return $acc[$a_table_id][$a_user_id][$a_property] ?? "";
    }
}
