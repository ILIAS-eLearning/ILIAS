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
	function setListItemMode(\ILIAS\UI\Component\Item\Standard $a_val)
	{
		$this->list_item = $a_val;
		$this->mode = self::MODE_LIST_ITEM;
	}
	
	/**
	 * Get list item mode
	 *
	 * @return \ILIAS\UI\Component\Item\Standard
	 */
	function getListItem()
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

	function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			case 'ilcalendarappointmentgui':
				include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
				$app = new ilCalendarAppointmentGUI($this->seed,$this->seed, (int) $_GET['app_id']);
				$this->ctrl->forwardCommand($app);
				break;

			default:
				if ($next_class != '')
				{
					// get the path and include
					$class_path = $this->ctrl->lookupClassPath($next_class);
					include_once($class_path);

					// check if the class implements our interface
					$class_name = $this->ctrl->getClassForClasspath($class_path);
					if (in_array("ilCalendarAppointmentPresentation", class_implements($class_name)))
					{
						// forward command to class
						$gui_class = new $class_name();
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
		if ($this->mode == self::MODE_MODAL)
		{
			return $this->getModalHTML();
		}
		if ($this->mode == self::MODE_LIST_ITEM)
		{
			return $this->modifyListItem();
		}
		return "";
	}

	/**
	 * Get modal html
	 * @return string
	 */
	function getModalHTML()
	{
		include_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationFactory.php";

		$tpl = new ilTemplate('tpl.appointment_presentation.html',true,true,'Services/Calendar');

		$info_screen = $this->info_screen;
		$info_screen->setFormAction($this->ctrl->getFormAction($this));

		$toolbar = $this->toolbar;

		$f = ilAppointmentPresentationFactory::getInstance($this->appointment, $info_screen, $toolbar, null);

		$this->ctrl->getHTML($f);
		$content = $info_screen->getHTML();

		$content = $this->getContentByPlugins($content);

		// show toolbar
		$tpl->setCurrentBlock("toolbar");
		$tpl->setVariable("TOOLBAR",$toolbar->getHTML());
		$tpl->parseCurrentBlock();


		// show infoscreen
		$tpl->setVariable("CONTENT", $content);

		return $tpl->get();
	}

	/**
	 * Modify List item
	 */
	function modifyListItem()
	{
		$li = $this->getListItem();
		include_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationFactory.php";
		$f = ilAppointmentPresentationFactory::getInstance($this->appointment, null, null, $li);
		$this->ctrl->getHTML($f);
		$this->list_item = $f->getListItem();
	}

	protected function getActivePlugins()
	{
		global $ilPluginAdmin;

		$res = array();

		foreach($ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "Calendar", "capm") as $plugin_name)
		{
			$res[] = $ilPluginAdmin->getPluginObject(IL_COMP_SERVICE,
			"Calendar", "capm", $plugin_name);
		}

		return $res;
	}

	protected function getContentByPlugins($a_content)
	{
		$content = $a_content;
		foreach($this->getActivePlugins() as $plugin)
		{
			//pass only the appointment stuff
			$plugin->setAppointment($this->appointment['event'], $this->appointment['dstart']);

			if($new_content = $plugin->replaceContent()) {
				$content = $new_content;
			} else {
				$this->info_screen = $plugin->infoscreenAddContent($this->info_screen);
				$extra_content = $plugin->addExtraContent();
				$content =  $this->info_screen->getHTML().$extra_content;
			}

			if($new_toolbar = $plugin->toolbarReplaceContent()) {
				$this->toolbar = $new_toolbar;
			} else {
				$this->toolbar = $plugin->toolbarAddItems($this->toolbar);
			}
		}

		return $content;
	}
}