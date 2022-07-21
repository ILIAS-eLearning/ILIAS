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

use ILIAS\DI\Container;
use ILIAS\Data\Factory;
use ILIAS\UI\Component\Modal\InterruptiveItem;
use ILIAS\UI\Component\Modal\Interruptive;

/**
 * Class ilADNNotificationTableGUI
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilADNNotificationTableGUI extends ilTable2GUI
{
    protected \ILIAS\Data\Factory $data_factory;
    
    protected \ILIAS\DI\UIServices $ui;
    protected \ilObjAdministrativeNotificationAccess $access;
    /**
     * @var Interruptive[]
     */
    protected array $modals = [];
    
    /**
     * ilADNNotificationTableGUI constructor.
     * @param                      $a_parent_cmd
     */
    public function __construct(ilADNNotificationGUI $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;
        /**
         * @var $DIC Container
         */
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->data_factory = new Factory();
        $this->ui = $DIC->ui();
        $this->access = new ilObjAdministrativeNotificationAccess();
        
        $this->setId('msg_msg_table');
        $this->setRowTemplate('Services/AdministrativeNotification/templates/default/tpl.row.html');
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        //
        // Columns
        $this->addColumn($this->lng->txt('msg_title'));
        $this->addColumn($this->lng->txt('msg_type'));
        $this->addColumn($this->lng->txt('msg_type_during_event'));
        $this->addColumn($this->lng->txt('msg_event_date_start'));
        $this->addColumn($this->lng->txt('msg_event_date_end'));
        $this->addColumn($this->lng->txt('msg_display_date_start'));
        $this->addColumn($this->lng->txt('msg_display_date_end'));
        $this->addColumn($this->lng->txt('common_actions'));
        
        $this->initData();
    }
    
    protected function initData() : void
    {
        $this->setData(ilADNNotification::getArray());
    }
    
    protected function formatDate(DateTimeImmutable $timestamp) : string
    {
        return $timestamp->format($this->data_factory->dateFormat()->germanLong()->toString() . ' - H:i:s') ?? '';
    }
    
    protected function fillRow(array $a_set) : void
    {
        /**
         * @var ilADNNotification $notification
         */
        $notification = ilADNNotification::find($a_set['id']);
        $this->tpl->setVariable('TITLE', $notification->getTitle());
        $this->tpl->setVariable('TYPE', $this->lng->txt('msg_type_' . $notification->getType()));
        
        if (!$notification->isPermanent()) {
            $this->tpl->setVariable(
                'TYPE_DURING_EVENT',
                $this->lng->txt('msg_type_' . $notification->getTypeDuringEvent())
            );
            $this->tpl->setVariable('EVENT_START', $this->formatDate($notification->getEventStart()));
            $this->tpl->setVariable('EVENT_END', $this->formatDate($notification->getEventEnd()));
            $this->tpl->setVariable('DISPLAY_START', $this->formatDate($notification->getDisplayStart()));
            $this->tpl->setVariable('DISPLAY_END', $this->formatDate($notification->getDisplayEnd()));
        }
        // Actions
        if ($this->access->hasUserPermissionTo('write')) {
            $items = [];
            $this->ctrl->setParameter($this->parent_obj, ilADNAbstractGUI::IDENTIFIER, $notification->getId());
            
            $items[] = $this->ui->factory()->button()->shy(
                $this->lng->txt('btn_' . ilADNNotificationGUI::CMD_EDIT),
                $this->ctrl->getLinkTargetByClass(ilADNNotificationGUI::class, ilADNNotificationGUI::CMD_EDIT)
            );
            
            // Modals and actions
            $ditem = $this->ui->factory()->modal()->interruptiveItem((string) $notification->getId(), $notification->getTitle());
            $delete_modal = $this->modal($ditem, ilADNNotificationGUI::CMD_DELETE);
            $items[] = $this->ui->factory()->button()->shy($this->lng->txt('btn_' . ilADNNotificationGUI::CMD_DELETE), "")
                                       ->withOnClick($delete_modal->getShowSignal());
            $this->modals[] = $delete_modal;
            
            $reset_modal = $this->modal($ditem, ilADNNotificationGUI::CMD_RESET);
            $items[] = $this->ui->factory()->button()->shy($this->lng->txt('btn_' . ilADNNotificationGUI::CMD_RESET), "")
                                       ->withOnClick($reset_modal->getShowSignal());
            $this->modals[] = $reset_modal;
            
            $actions = $this->ui->renderer()->render([$this->ui->factory()->dropdown()->standard($items)->withLabel($this->lng->txt('actions'))]);
            
            $this->tpl->setVariable('ACTIONS', $actions);
        } else {
            $this->tpl->setVariable('ACTIONS', "");
        }
    }
    
    protected function modal(InterruptiveItem $i, string $cmd) : Interruptive
    {
        $action = $this->ctrl->getLinkTargetByClass(ilADNNotificationGUI::class, $cmd);
        
        return $this->ui->factory()->modal()
                           ->interruptive(
                               $this->lng->txt('btn_' . $cmd),
                               $this->lng->txt('btn_' . $cmd . '_confirm'),
                               $action
                           )
                           ->withAffectedItems([$i])
                           ->withActionButtonLabel($cmd);
    }
    
    public function getHTML() : string
    {
        return parent::getHTML() . $this->ui->renderer()->render($this->modals);
    }
}
