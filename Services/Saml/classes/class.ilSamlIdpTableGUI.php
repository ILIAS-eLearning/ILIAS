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

/**
 * Class ilSamlIdpTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilSamlIdpTableGUI extends ilTable2GUI
{
    public function __construct(
        ilSamlSettingsGUI $parent_gui,
        private readonly \ILIAS\UI\Factory $ui_factory,
        private readonly \ILIAS\UI\Renderer $ui_renderer,
        string $parent_cmd,
        private readonly bool $hasWriteAccess
    ) {
        $this->setId('saml_idp_list');
        parent::__construct($parent_gui, $parent_cmd);

        $this->setTitle($this->lng->txt('auth_saml_idps'));

        $federationMdUrl = rtrim(ILIAS_HTTP_PATH, '/') . '/Services/Saml/lib/metadata.php?client_id=' . CLIENT_ID;

        $this->setDescription(sprintf(
            $this->lng->txt('auth_saml_idps_info'),
            'auth/saml/config/config.php',
            'auth/saml/config/authsources.php',
            $this->ui_renderer->render($this->ui_factory->link()->standard(
                'https://simplesamlphp.org/docs/stable/simplesamlphp-sp',
                'https://simplesamlphp.org/docs/stable/simplesamlphp-sp'
            )),
            $this->ui_renderer->render($this->ui_factory->link()->standard($federationMdUrl, $federationMdUrl))
        ));
        $this->setRowTemplate('tpl.saml_idp_row.html', 'Services/Saml');

        $this->addColumn($this->lng->txt('saml_tab_head_idp'), '', '80%');
        $this->addColumn($this->lng->txt('active'), '', '5%');
        $this->addColumn($this->lng->txt('actions'), '', '15%');

        $this->getItems();
    }

    private function getItems(): void
    {
        $idp_data = [];

        foreach (ilSamlIdp::getAllIdps() as $idp) {
            $idp_data[] = $idp->toArray();
        }

        $this->setData($idp_data);
    }

    protected function fillRow(array $a_set): void
    {
        if ($a_set['is_active']) {
            $this->tpl->setVariable('IMAGE_OK', ilUtil::getImagePath('icon_ok.svg'));
            $this->tpl->setVariable('TXT_OK', $this->lng->txt('active'));
        } else {
            $this->tpl->setVariable('IMAGE_OK', ilUtil::getImagePath('icon_not_ok.svg'));
            $this->tpl->setVariable('TXT_OK', $this->lng->txt('inactive'));
        }

        $this->tpl->setVariable('NAME', $a_set['entity_id']);

        if ($this->hasWriteAccess) {
            $buttons = [];

            $this->ctrl->setParameter($this->getParentObject(), 'saml_idp_id', $a_set['idp_id']);
            $buttons[] = $this->ui_factory
                ->button()
                ->shy(
                    $this->lng->txt('edit'),
                    $this->ctrl->getLinkTarget($this->getParentObject(), 'showIdpSettings')
                );
            if ($a_set['is_active']) {
                $buttons[] = $this->ui_factory
                    ->button()
                    ->shy(
                        $this->lng->txt('deactivate'),
                        $this->ctrl->getLinkTarget($this->getParentObject(), 'deactivateIdp')
                    );
            } else {
                $buttons[] = $this->ui_factory
                    ->button()
                    ->shy(
                        $this->lng->txt('activate'),
                        $this->ctrl->getLinkTarget($this->getParentObject(), 'activateIdp')
                    );
            }
            $buttons[] = $this->ui_factory
                ->button()
                ->shy(
                    $this->lng->txt('delete'),
                    $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDeleteIdp')
                );
            $this->ctrl->setParameter($this->getParentObject(), 'saml_idp_id', '');

            $drop_down = $this->ui_factory
                ->dropdown()
                ->standard($buttons)
                ->withLabel($this->lng->txt('actions'));

            $this->tpl->setVariable('ACTIONS', $this->ui_renderer->render($drop_down));
        }
    }
}
