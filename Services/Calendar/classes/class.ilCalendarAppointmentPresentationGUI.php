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

use ILIAS\UI\Component\Item\Item;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HttpServices;

/**
 * Class ilCalendarAppointmentPresentationGUI
 * @author       Jesús López <lopez@leifos.com>
 * @ilCtrl_Calls ilCalendarAppointmentPresentationGUI: ilInfoScreenGUI, ilCalendarAppointmentGUI
 */
class ilCalendarAppointmentPresentationGUI
{
    protected const MODE_MODAL = "modal";
    protected const MODE_LIST_ITEM = "list_item";

    protected static ?self $instance = null;

    protected ilDate $seed;
    protected ilCalendarSettings $settings;
    protected array $appointment = [];

    protected string $mode = self::MODE_MODAL;

    protected ilToolbarGUI $toolbar;
    protected ilInfoScreenGUI $info_screen;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected HttpServices $http;
    protected RefineryFactory $refinery;


    protected ?Item $list_item = null;

    protected function __construct(ilDate $seed, array $a_app)
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->settings = ilCalendarSettings::_getInstance();

        $this->seed = $seed;
        $this->appointment = $a_app;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->info_screen = new ilInfoScreenGUI($this);
        $this->toolbar = new ilToolbarGUI();
    }

    /**
     * Set list item mode
     */
    public function setListItemMode(Item $a_val)
    {
        $this->list_item = $a_val;
        $this->mode = self::MODE_LIST_ITEM;
    }

    public function getListItem(): Item
    {
        return $this->list_item;
    }

    /**
     * get singleton instance
     */
    public static function _getInstance(ilDate $seed, array $a_app): self
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self($seed, $a_app);
        }
        return self::$instance;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("getHTML");

        switch ($next_class) {
            case 'ilcalendarappointmentgui':
                $app_id = 0;
                if ($this->http->wrapper()->query()->has('app_id')) {
                    $app_id = $this->http->wrapper()->query()->retrieve(
                        'app_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $app = new ilCalendarAppointmentGUI($this->seed, $this->seed, $app_id);
                $this->ctrl->forwardCommand($app);
                break;

            default:
                if ($next_class != '') {
                    // get the path and include
                    $class_path = $this->ctrl->lookupClassPath($next_class);

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
    public function getSeed(): ilDate
    {
        return $this->seed;
    }

    public function getHTML(): string
    {
        if ($this->mode == self::MODE_MODAL) {
            return $this->getModalHTML();
        }
        if ($this->mode == self::MODE_LIST_ITEM) {
            return $this->modifyListItem();
        }
        return "";
    }

    public function getModalHTML(): string
    {
        $tpl = new ilTemplate('tpl.appointment_presentation.html', true, true, 'Services/Calendar');

        $info_screen = $this->info_screen;
        $info_screen->setFormAction($this->ctrl->getFormAction($this));

        #21529 create new toolbar with unique id using the entry id for this purpose
        $toolbar = new ilToolbarGUI();
        $toolbar->setId((string) $this->appointment['event']->getEntryId());

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
    public function modifyListItem(): string
    {
        $li = $this->getListItem();
        $f = ilAppointmentPresentationFactory::getInstance($this->appointment, null, null, $li);
        $this->ctrl->getHTML($f);
        $this->list_item = $f->getListItem();
        return '';
    }

    protected function getActivePlugins(): Iterator
    {
        global $DIC;

        $component_factory = $DIC['component.factory'];
        return $component_factory->getActivePluginsInSlot("capm");
    }

    protected function getContentByPlugins($a_content, $a_toolbar): array
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
                $new_toolbar->setId((string) $a_toolbar->getId());
                $toolbar = $new_toolbar;
            }
        }
        return array(
            'content' => $content,
            'toolbar' => $toolbar
        );
    }
}
