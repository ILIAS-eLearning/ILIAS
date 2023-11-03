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
 */

declare(strict_types=1);

/**
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilECSServerTableGUI extends ilTable2GUI
{
    private ilAccessHandler $access;

    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;

    public function __construct(object $a_parent_obj, string $a_parent_cmd = "")
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setId('ecs_server_list');

        $this->access = $DIC->access();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
    }

    /**
     * Init Table
     */
    public function initTable(): void
    {
        $this->setTitle($this->lng->txt('ecs_available_ecs'));
        $this->setRowTemplate('tpl.ecs_server_row.html', 'components/ILIAS/WebServices/ECS');

        $this->addColumn($this->lng->txt('ecs_tbl_active'), '', '1%');
        $this->addColumn($this->lng->txt('title'), '', '80%');

        if ($this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
            $this->addColumn($this->lng->txt('actions'), '', '19%');
        }
    }

    /**
     * Fill row
 * @param array $a_set
     */
    protected function fillRow(array $a_set): void
    {
        $this->ctrl->setParameter($this->getParentObject(), 'server_id', $a_set['server_id']);
        $this->ctrl->setParameterByClass('ilecsmappingsettingsgui', 'server_id', $a_set['server_id']);

        if ($a_set['active']) {
            $this->tpl->setVariable('IMAGE_OK', ilUtil::getImagePath('standard/icon_ok.svg'));
            $this->tpl->setVariable('TXT_OK', $this->lng->txt('ecs_activated'));
        } else {
            $this->tpl->setVariable('IMAGE_OK', ilUtil::getImagePath('standard/icon_not_ok.svg'));
            $this->tpl->setVariable('TXT_OK', $this->lng->txt('ecs_inactivated'));
        }


        $this->tpl->setVariable('VAL_TITLE', ilECSSetting::getInstanceByServerId($a_set['server_id'])->getTitle());
        $this->tpl->setVariable('LINK_EDIT', $this->ctrl->getLinkTarget($this->getParentObject(), 'edit'));
        $this->tpl->setVariable('TXT_SRV_ADDR', $this->lng->txt('ecs_server_addr'));

        if (ilECSSetting::getInstanceByServerId($a_set['server_id'])->getServer()) {
            $this->tpl->setVariable('VAL_DESC', ilECSSetting::getInstanceByServerId($a_set['server_id'])->getServer());
        } else {
            $this->tpl->setVariable('VAL_DESC', $this->lng->txt('ecs_not_configured'));
        }

        $dt = ilECSSetting::getInstanceByServerId($a_set['server_id'])->fetchCertificateExpiration();
        if ($dt !== null) {
            $this->tpl->setVariable('TXT_CERT_VALID', $this->lng->txt('ecs_cert_valid_until'));

            $now = new ilDateTime(time(), IL_CAL_UNIX);
            $now->increment(IL_CAL_MONTH, 2);

            if (ilDateTime::_before($dt, $now)) {
                $this->tpl->setCurrentBlock('invalid');
                $this->tpl->setVariable('VAL_ICERT', ilDatePresentation::formatDate($dt));
            } else {
                $this->tpl->setCurrentBlock('valid');
                $this->tpl->setVariable('VAL_VCERT', ilDatePresentation::formatDate($dt));
            }
            $this->tpl->parseCurrentBlock();
        }

        if ($this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
            // Actions
            $items = [];

            if (ilECSSetting::getInstanceByServerId($a_set['server_id'])->isEnabled()) {
                $items[] = [$this->lng->txt('ecs_deactivate'), $this->ctrl->getLinkTarget($this->getParentObject(), 'deactivate')];
            } else {
                $items[] = [$this->lng->txt('ecs_activate'), $this->ctrl->getLinkTarget($this->getParentObject(), 'activate')];
            }

            $items[] = [$this->lng->txt('edit'), $this->ctrl->getLinkTarget($this->getParentObject(), 'edit')];
            $items[] = [$this->lng->txt('copy'), $this->ctrl->getLinkTarget($this->getParentObject(), 'cp')];
            $items[] = [$this->lng->txt('delete'), $this->ctrl->getLinkTarget($this->getParentObject(), 'delete')];

            $this->tpl->setCurrentBlock("actions");
            $render_items = [];
            foreach ($items as $item) {
                $render_items[] = $this->ui_factory->button()->shy(...$item);
            }
            $this->tpl->setVariable(
                'ACTIONS',
                $this->ui_renderer->render($this->ui_factory->dropdown()->standard($render_items)->withLabel($this->lng->txt('actions')))
            );
            $this->tpl->parseCurrentBlock();
        }
        $this->ctrl->clearParameters($this->getParentObject());
    }

    /**
     * Parse available servers
     */
    public function parse(ilECSServerSettings $servers): void
    {
        $rows = [];
        foreach ($servers->getServers(ilECSServerSettings::ALL_SERVER) as $server) {
            $tmp['server_id'] = $server->getServerId();
            $tmp['active'] = $server->isEnabled();

            $rows[] = $tmp;
        }
        $this->setData($rows);
    }
}
