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
 * Class ilCommonActionDispatcherGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilCommonActionDispatcherGUI: ilNoteGUI, ilTaggingGUI, ilObjectActivationGUI
 * @ilCtrl_Calls ilCommonActionDispatcherGUI: ilRatingGUI, ilObjRootFolderGUI
 */
class ilCommonActionDispatcherGUI implements ilCtrlBaseClassInterface
{
    const TYPE_REPOSITORY = 1;
    const TYPE_WORKSPACE = 2;

    protected ilCtrl $ctrl;
    protected ilSetting $settings;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    protected int $node_type = 0;
    /**
     * @var ilDummyAccessHandler|ilPortfolioAccessHandler|ilWorkspaceAccessHandler|mixed
     */
    protected $access_handler;
    protected string $obj_type = "";
    protected int $node_id = 0;
    protected int $obj_id = 0;
    protected int $news_id = 0;

    protected ?string $sub_type = null;
    protected ?int $sub_id = null;
    protected bool $enable_comments_settings = false;
    protected array $rating_callback = [];
    private ilObjectRequestRetriever $retriever;
    
    public function __construct(
        int $node_type,
        $access_handler,
        string $obj_type,
        int $node_id,
        int $obj_id,
        int $news_id = 0
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->settings = $DIC->settings();
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();
        $this->retriever = new ilObjectRequestRetriever($DIC->http()->wrapper(), $this->refinery);

        $this->node_type = $node_type;
        $this->access_handler = $access_handler;
        $this->obj_type = $obj_type;
        $this->node_id = $node_id;
        $this->obj_id = $obj_id;
        $this->news_id = $news_id;
    }
    
    /**
     * Build ajax hash for current (object/node) properties
     */
    public function getAjaxHash() : string
    {
        return self::buildAjaxHash(
            $this->node_type,
            $this->node_id,
            $this->obj_type,
            $this->obj_id,
            $this->sub_type,
            $this->sub_id,
            $this->news_id
        );
    }
    
    /**
     * Build ajax hash
     */
    public static function buildAjaxHash(
        int $node_type,
        ?int $node_id,
        string $obj_type,
        int $obj_id,
        string $sub_type = null,
        int $sub_id = null,
        int $news_id = 0
    ) : string {
        return
            $node_type . ";" . $node_id . ";" . $obj_type . ";" . $obj_id . ";" .
            $sub_type . ";" . $sub_id . ";" . $news_id
        ;
    }
    
    /**
     * (Re-)Build instance from ajax call
     */
    public static function getInstanceFromAjaxCall() : ?ilCommonActionDispatcherGUI
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilUser = $DIC->user();
        $request_wrapper = $DIC->http()->wrapper()->query();
        $refinery = $DIC->refinery();

        if ($request_wrapper->has("cadh")) {
            $parts = explode(";", $request_wrapper->retrieve("cadh", $refinery->kindlyTo()->string()));
            
            $node_type = (int) $parts[0];
            $node_id = (int) $parts[1];
            $obj_type = (string) $parts[2];
            $obj_id = (int) $parts[3];
            $sub_type = (string) $parts[4];
            $sub_id = (int) $parts[5];
            $news_id = (int) $parts[6];
            
            switch ($node_type) {
                case self::TYPE_REPOSITORY:
                    $access_handler = $ilAccess;
                    break;
                
                case self::TYPE_WORKSPACE:
                    $tree = new ilWorkspaceTree($ilUser->getId());
                    $access_handler = new ilWorkspaceAccessHandler($tree);
                    break;
                
                default:
                    return null;
            }
            
            $dispatcher = new self($node_type, $access_handler, $obj_type, $node_id, $obj_id, $news_id);
            
            if ($sub_type && $sub_id) {
                $dispatcher->setSubObject($sub_type, $sub_id);
            }

            // poll comments have specific settings

            if ($node_type == self::TYPE_REPOSITORY && $obj_type != "poll") {
                $dispatcher->enableCommentsSettings(true);
            }
            
            return $dispatcher;
        }
        return null;
    }
        
    public function executeCommand() : void
    {
        // check access for object
        if (
            $this->node_id &&
            !$this->access_handler->checkAccess("visible", "", $this->node_id) &&
            !$this->access_handler->checkAccess("read", "", $this->node_id)
        ) {
            exit();
        }
        
        $next_class = $this->ctrl->getNextClass($this);

        $this->ctrl->saveParameter($this, "cadh");
        
        switch ($next_class) {
            case "ilnotegui":
                
                $obj_type = $this->obj_type;
                if ($this->sub_type) {
                    $obj_type = $this->sub_type;
                }
                
                $note_gui = new ilNoteGUI($this->obj_id, (int) $this->sub_id, $obj_type, false, $this->news_id);
                $note_gui->enablePrivateNotes();
                
                $has_write = $this->access_handler->checkAccess("write", "", $this->node_id);
                if ($has_write && $this->settings->get("comments_del_tutor", "1")) {
                    $note_gui->enablePublicNotesDeletion();
                }
                
                // comments cannot be turned off globally
                if ($this->enable_comments_settings) {
                    // should only be shown if active or permission to toggle
                    if (
                        $has_write ||
                        $this->access_handler->checkAccess("edit_permissions", "", $this->node_id)
                    ) {
                        $note_gui->enableCommentsSettings();
                    }
                }
                /* this is different to the info screen but we need this
                   for sub-object action menus, e.g. wiki page */
                elseif ($this->sub_id) {
                    $note_gui->enablePublicNotes();
                }

                $this->ctrl->forwardCommand($note_gui);
                break;

            case "iltagginggui":
                $tags_gui = new ilTaggingGUI();
                $tags_gui->setObject($this->obj_id, $this->obj_type);
                $this->ctrl->forwardCommand($tags_gui);
                break;
            
            case "ilobjectactivationgui":
                $parent_id = $this->retriever->getMaybeInt('parent_id') ?? 0;
                $this->ctrl->setParameter($this, "parent_id", $parent_id);
                $act_gui = new ilObjectActivationGUI($parent_id, $this->node_id);
                $this->ctrl->forwardCommand($act_gui);
                break;
            
            case "ilratinggui":
                $rating_gui = new ilRatingGUI();
                if (
                    $this->request_wrapper->has("rnsb")
                ) {
                    $rating_gui->setObject($this->obj_id, $this->obj_type, $this->sub_id, $this->sub_type);
                } else {
                    // coming from headaction ignore sub-objects
                    $rating_gui->setObject($this->obj_id, $this->obj_type);
                }
                $this->ctrl->forwardCommand($rating_gui);
                if ($this->rating_callback) {
                    // as rating in categories is form-based we need to redirect
                    // somewhere after saving
                    $this->ctrl->redirect($this->rating_callback[0], $this->rating_callback[1]);
                }
                break;
            
            default:
                break;
        }
        
        exit();
    }
    
    /**
     * Set sub object attributes
     */
    public function setSubObject(?string $sub_obj_type, ?int $sub_obj_id) : void
    {
        $this->sub_type = $sub_obj_type;
        $this->sub_id = $sub_obj_id;
    }
    
    /**
     * Toggle comments settings
     */
    public function enableCommentsSettings(bool $value) : void
    {
        $this->enable_comments_settings = $value;
    }
    
    /**
     * Add callback for rating gui
     */
    public function setRatingCallback(object $gui, string $cmd) : void
    {
        $this->rating_callback = array($gui, $cmd);
    }
    
    /**
     * Set header action menu
     */
    public function initHeaderAction() : ?ilObjectListGUI
    {
        // check access for object
        if (
            $this->node_id &&
            !$this->access_handler->checkAccess("visible", "", $this->node_id) &&
            !$this->access_handler->checkAccess("read", "", $this->node_id)
        ) {
            return null;
        }
        
        $header_action = ilObjectListGUIFactory::_getListGUIByType(
            $this->obj_type,
            ($this->node_type == self::TYPE_REPOSITORY) ?
                ilObjectListGUI::CONTEXT_REPOSITORY : ilObjectListGUI::CONTEXT_WORKSPACE
        );
        
        // remove all currently unwanted actions
        $header_action->enableCopy(false);
        $header_action->enableCut(false);
        $header_action->enableDelete(false);
        $header_action->enableLink(false);
        $header_action->enableInfoScreen(false);
        $header_action->enableTimings(false);
        $header_action->enableSubscribe($this->node_type == self::TYPE_REPOSITORY);
        
        $header_action->initItem($this->node_id, $this->obj_id, $this->obj_type);
        $header_action->setHeaderSubObject($this->sub_type, $this->sub_id);
        $header_action->setAjaxHash($this->getAjaxHash());
        
        return $header_action;
    }
}
