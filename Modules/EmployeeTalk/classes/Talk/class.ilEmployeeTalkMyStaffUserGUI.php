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

use ILIAS\MyStaff\ilMyStaffAccess;
use Psr\Http\Message\RequestInterface;
use ILIAS\EmployeeTalk\UI\ControlFlowCommandHandler;
use ILIAS\EmployeeTalk\UI\ControlFlowCommand;
use ILIAS\Modules\EmployeeTalk\Talk\Repository\EmployeeTalkRepository;
use ILIAS\DI\UIServices;

/**
 * Class ilEmployeeTalkMyStaffUserGUI
 *
 * @author            Nicolas Schaefli <ns@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilEmployeeTalkMyStaffUserGUI: ilMStShowUserGUI
 * @ilCtrl_IsCalledBy ilEmployeeTalkMyStaffUserGUI: ilFormPropertyDispatchGUI
 * @ilCtrl_Calls ilEmployeeTalkMyStaffUserGUI: ilObjEmployeeTalkGUI
 * @ilCtrl_Calls ilEmployeeTalkMyStaffUserGUI: ilObjEmployeeTalkSeriesGUI
 */
final class ilEmployeeTalkMyStaffUserGUI implements ControlFlowCommandHandler
{
    private int $usrId;
    private ilMyStaffAccess $access;
    private ilCtrl $ctrl;
    private ilLanguage $language;
    private RequestInterface $request;
    private ilGlobalTemplateInterface $template;
    private ilTabsGUI $tabs;
    private EmployeeTalkRepository $repository;
    private UIServices $ui;
    private ilObjEmployeeTalkAccess $talkAccess;
    private ilObjUser $currentUser;

    /**
     * ilEmployeeTalkMyStaffUserGUI constructor.
     * @param ilMyStaffAccess           $access
     * @param ilCtrl                    $ctrl
     * @param ilLanguage                $language
     * @param RequestInterface          $request
     * @param ilGlobalTemplateInterface $template
     * @param ilTabsGUI                 $tabs
     * @param EmployeeTalkRepository    $repository
     * @param UIServices                $ui
     */
    public function __construct(
        ilMyStaffAccess $access,
        ilCtrl $ctrl,
        ilLanguage $language,
        RequestInterface $request,
        ilGlobalTemplateInterface $template,
        ilTabsGUI $tabs,
        EmployeeTalkRepository $repository,
        UIServices $ui,
        ilObjEmployeeTalkAccess $employeeTalkAccess,
        ilObjUser $currentUser
    ) {
        $this->access = $access;
        $this->ctrl = $ctrl;
        $this->language = $language;
        $this->request = $request;
        $this->template = $template;
        $this->tabs = $tabs;
        $this->repository = $repository;
        $this->ui = $ui;
        $this->talkAccess = $employeeTalkAccess;
        $this->currentUser = $currentUser;

        $this->usrId = intval($this->request->getQueryParams()['usr_id']);
        $this->ctrl->setParameter($this, 'usr_id', $this->usrId);
        $this->language->loadLanguageModule('etal');
    }

    /**
     *
     */
    private function checkAccessOrFail(): void
    {
        if (!$this->usrId) {
            $this->template->setOnScreenMessage('failure', $this->language->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilDashboardGUI::class, "");
        }

        if (!$this->access->hasCurrentUserAccessToMyStaff() || !$this->access->hasCurrentUserAccessToUser($this->usrId)) {
            $this->template->setOnScreenMessage('failure', $this->language->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilDashboardGUI::class, "");
        }
    }


    /**
     *
     */
    public function executeCommand(): void
    {
        $this->checkAccessOrFail();

        $cmd = $this->ctrl->getCmd();
        $nextClass = $this->ctrl->getNextClass();

        switch ($nextClass) {
            case strtolower(ilObjEmployeeTalkGUI::class):
                $gui = new ilObjEmployeeTalkGUI();
                $this->ctrl->redirect($gui, ControlFlowCommand::INDEX);
                break;
            case strtolower(ilFormPropertyDispatchGUI::class):
                $this->ctrl->setReturn($this, ControlFlowCommand::INDEX);
                $table = new ilEmployeeTalkTableGUI($this, ControlFlowCommand::INDEX);
                $table->executeCommand();
                break;
            case strtolower(ilObjEmployeeTalkSeriesGUI::class):
                $gui = new ilObjEmployeeTalkSeriesGUI();
                $this->ctrl->saveParameter($gui, 'template');
                $this->ctrl->saveParameter($gui, 'new_type');
                $this->ctrl->saveParameter($gui, 'usr_id');
                $this->ctrl->redirectByClass([
                    strtolower(ilDashboardGUI::class),
                    strtolower(ilMyStaffGUI::class),
                    strtolower(ilEmployeeTalkMyStaffListGUI::class),
                    strtolower(ilObjEmployeeTalkSeriesGUI::class)
                ], $cmd);
                break;
            default:
                switch ($cmd) {
                    case ControlFlowCommand::INDEX:
                        $this->view();
                        break;
                    case ControlFlowCommand::APPLY_FILTER:
                        $this->applyFilter();
                        break;
                    case ControlFlowCommand::RESET_FILTER:
                        $this->resetFilter();
                        break;
                    default:
                        $this->ctrl->redirectByClass(ilDashboardGUI::class, "");
                        break;
                }
        }
    }

    private function applyFilter(): void
    {
        $table = new ilEmployeeTalkTableGUI($this, ControlFlowCommand::APPLY_FILTER);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->view();
    }


    private function resetFilter(): void
    {
        $table = new ilEmployeeTalkTableGUI($this, ControlFlowCommand::RESET_FILTER);
        $table->resetOffset();
        $table->resetFilter();
        $this->view();
    }

    private function view(): void
    {
        $this->loadActionBar();
        $table = new ilEmployeeTalkTableGUI($this, ControlFlowCommand::INDEX);
        $userId = intval($this->usrId);

        $talks = null;
        if ($this->talkAccess->hasPermissionToReadUnownedTalksOfUser($userId)) {
            $talks = $this->repository->findByEmployee($userId);
        } else {
            $talks = $this->repository->findTalksBetweenEmployeeAndOwner($userId, $this->currentUser->getId());
        }
        $table->setTalkData($talks);
        $this->template->setContent($table->getHTML());
    }

    private function loadActionBar(): void {
        $gl = new ilGroupedListGUI();
        $gl->setAsDropDown(true, false);

        $templates = new CallbackFilterIterator(
            new ArrayIterator(ilObject::_getObjectsByType("talt")),
            function(array $item) {
                return
                    (
                        $item['offline'] === "0" ||
                        $item['offline'] === null
                    ) && ilObjTalkTemplate::_hasUntrashedReference(intval($item['obj_id']));
            }
        );

        foreach ($templates as $item) {
            $type = $item["type"];

            $path = ilObject::_getIcon('', 'tiny', $type);
            $icon = ($path != "")
                ? ilUtil::img($path, "") . " "
                : "";

            $base_url = $this->ctrl->getLinkTargetByClass(strtolower(ilObjEmployeeTalkSeriesGUI::class), ControlFlowCommand::CREATE);
            $url = $this->ctrl->appendRequestTokenParameterString($base_url . "&new_type=" . ilObjEmployeeTalkSeries::TYPE);
            $refId = ilObject::_getAllReferences(intval($item['obj_id']));

            // Templates only have one ref id
            $url .= "&template=" . array_pop($refId);
            $url .= "&ref_id=" . ilObjTalkTemplateAdministration::getRootRefId();
            $url .= '&usr_id=' . $this->usrId;

            $ttip = ilHelp::getObjCreationTooltipText("tals");

            $gl->addEntry(
                $icon . $item["title"],
                $url,
                "_top",
                "",
                "",
                $type,
                $ttip,
                "bottom center",
                "top center",
                false
            );
        }

        $adv = new ilAdvancedSelectionListGUI();
        $adv->setListTitle($this->language->txt("etal_add_new_item"));
        //$gl->getHTML();
        $adv->setGroupedList($gl);
        $adv->setStyle(ilAdvancedSelectionListGUI::STYLE_EMPH);
        //$this->toolbar->addDropDown($this->language->txt("cntr_add_new_item"), $adv->getHTML());
        $this->ui->mainTemplate()->setVariable("SELECT_OBJTYPE_REPOS", $adv->getHTML());
    }
}