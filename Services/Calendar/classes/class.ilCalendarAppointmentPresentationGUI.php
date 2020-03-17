<?php
include_once './Services/Calendar/classes/class.ilCalendarSettings.php';

/**
 * Class ilCalendarAppointmentPresentationGUI
 *
 * @author	Jesús López <lopez@leifos.com>
 * @version  $Id$
 * @ilCtrl_Calls ilCalendarAppointmentPresentationGUI: ilInfoScreenGUI, ilCalendarAppointmentGUI
*/
class ilCalendarAppointmentPresentationGUI
{
    const MODE_MODAL = "modal";
    const MODE_LIST_ITEM = "list_item";

    protected $seed = null;
    protected static $instance = null;
    protected $settings = null;
    protected $appointment;

    protected $mode = self::MODE_MODAL;

    protected $toolbar;
    protected $info_screen;


    /**
     * @var \ILIAS\UI\Component\Item\Standard|null
     */
    protected $list_item = null;

    /**
     * Singleton
     *
     * @access public
     * @param
     * @param
     * @return
     */
    protected function __construct(ilDate $seed = null, $a_app)
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->settings = ilCalendarSettings::_getInstance();

        $this->seed = $seed;
        $this->appointment = $a_app;

        $this->tpl = $DIC["tpl"];

        $this->info_screen = new ilInfoScreenGUI($this);
        $this->toolbar = new ilToolbarGUI();
    }
    
    /**
     * Set list item mode
     *
     * @param \ILIAS\UI\Component\Item\Standard $a_val
     */
    public function setListItemMode(\ILIAS\UI\Component\Item\Standard $a_val)
    {
        $this->list_item = $a_val;
        $this->mode = self::MODE_LIST_ITEM;
    }
    
    /**
     * Get list item mode
     *
     * @return \ILIAS\UI\Component\Item\Standard
     */
    public function getListItem()
    {
        return $this->list_item;
    }

    /**
     * get singleton instance
     *
     * @access public
     * @param ilDate $seed
     * @param  $a_app
     * @return ilCalendarAppointmentPresentationGUI
     * @static
     */
    public static function _getInstance(ilDate $seed, $a_app)
    {
        return new static($seed, $a_app);
    }

    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            case 'ilcalendarappointmentgui':
                include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
                $app = new ilCalendarAppointmentGUI($this->seed, $this->seed, (int) $_GET['app_id']);
                $this->ctrl->forwardCommand($app);
                break;

            default:
                if ($next_class != '') {
                    // get the path and include
                    $class_path = $this->ctrl->lookupClassPath($next_class);
                    include_once($class_path);

                    // check if the class implements our interface
                    $class_name = $this->ctrl->getClassForClasspath($class_path);
                    if (in_array("ilCalendarAppointmentPresentation", class_implements($class_name))) {
                        // forward command to class
                        $gui_class = new $class_name($this->appointment, $this->info_screen, $this->toolbar, null);
                        $this->ctrl->forwardCommand($gui_class);
                    }
                }
                break;
        }
    }

    /**
     * Get seed date
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * Get modal html
     * @return string
     */
    public function getHTML()
    {
        if ($this->mode == self::MODE_MODAL) {
            return $this->getModalHTML();
        }
        if ($this->mode == self::MODE_LIST_ITEM) {
            return $this->modifyListItem();
        }
        return "";
    }

    /**
     * Get modal html
     * @return string
     */
    public function getModalHTML()
    {
        include_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationFactory.php";

        $tpl = new ilTemplate('tpl.appointment_presentation.html', true, true, 'Services/Calendar');

        $info_screen = $this->info_screen;
        $info_screen->setFormAction($this->ctrl->getFormAction($this));

        #21529 create new toolbar with unique id using the entry id for this purpose
        //$toolbar = $this->toolbar;
        $toolbar = new ilToolbarGUI();
        $toolbar->setId($this->appointment['event']->getEntryId());

        $f = ilAppointmentPresentationFactory::getInstance($this->appointment, $info_screen, $toolbar, null);

        $this->ctrl->getHTML($f);
        $content = $info_screen->getHTML();

        //because #21529
        $plugin_results = $this->getContentByPlugins($content, $toolbar);
        $content = $plugin_results['content'];
        $toolbar = $plugin_results['toolbar'];

        // show toolbar
        $tpl->setCurrentBlock("toolbar");
        $tpl->setVariable("TOOLBAR", $toolbar->getHTML());
        $tpl->parseCurrentBlock();


        // show infoscreen
        $tpl->setVariable("CONTENT", $content);

        return $tpl->get();
    }

    /**
     * Modify List item
     */
    public function modifyListItem()
    {
        $li = $this->getListItem();
        include_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationFactory.php";
        $f = ilAppointmentPresentationFactory::getInstance($this->appointment, null, null, $li);
        $this->ctrl->getHTML($f);
        $this->list_item = $f->getListItem();
    }

    protected function getActivePlugins()
    {
        global $DIC;

        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $res = array();

        foreach ($ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "Calendar", "capm") as $plugin_name) {
            $res[] = $ilPluginAdmin->getPluginObject(
                IL_COMP_SERVICE,
                "Calendar",
                "capm",
                $plugin_name
            );
        }

        return $res;
    }

    protected function getContentByPlugins($a_content, $a_toolbar)
    {
        $content = $a_content;
        $toolbar = $a_toolbar;
        foreach ($this->getActivePlugins() as $plugin) {
            //pass only the appointment stuff
            $plugin->setAppointment($this->appointment['event'], new ilDateTime($this->appointment['dstart']));

            if ($new_infoscreen = $plugin->infoscreenAddContent($this->info_screen)) {
                $this->info_screen = $new_infoscreen;
            }

            $content = $this->info_screen->getHTML();
            $extra_content = $plugin->addExtraContent();
            if ($extra_content != '') {
                $content .= $extra_content;
            }

            if ($new_content = $plugin->replaceContent()) {
                $content = $new_content;
            }

            if ($new_toolbar = $plugin->toolbarAddItems($toolbar)) {
                $toolbar = $new_toolbar;
            }

            if ($new_toolbar = $plugin->toolbarReplaceContent()) {
                $new_toolbar->setId($a_toolbar->getId());
                $toolbar = $new_toolbar;
            }
        }

        return array(
            'content' => $content,
            'toolbar' => $toolbar
        );
    }
}
