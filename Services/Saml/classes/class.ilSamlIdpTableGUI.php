<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
require_once 'Services/Saml/classes/class.ilSamlIdp.php';

/**
 * Class ilSamlIdpTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlIdpTableGUI extends ilTable2GUI
{
    /** @var bool */
    protected $mayEdit = true;

    /**
     * ilSamlIdpTableGUI constructor.
     * @param        $a_parent_obj
     * @param string $a_parent_cmd
     * @param string $a_template_context
     * @param bool   $mayEdit
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "", $mayEdit = true)
    {
        global $DIC;

        $f        = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $this->mayEdit = $mayEdit;

        $this->setId('saml_idp_list');
        parent::__construct($a_parent_obj, $a_parent_cmd, '');

        $this->ctrl = $GLOBALS['DIC']->ctrl();

        $this->setTitle($this->lng->txt('auth_saml_idps'));

        $federationMdUrl = rtrim(ILIAS_HTTP_PATH, '/') . '/Services/Saml/lib/metadata.php?client_id=' . CLIENT_ID;

        $this->setDescription(sprintf(
            $this->lng->txt('auth_saml_idps_info'),
            'auth/saml/config/config.php',
            'auth/saml/config/authsources.php',
            $renderer->render($f->link()->standard('https://simplesamlphp.org/docs/stable/simplesamlphp-sp', 'https://simplesamlphp.org/docs/stable/simplesamlphp-sp')),
            $renderer->render($f->link()->standard($federationMdUrl, $federationMdUrl))
        ));
        $this->setRowTemplate('tpl.saml_idp_row.html', 'Services/Saml');

        $this->addColumn($this->lng->txt('saml_tab_head_idp'), '', '80%');
        $this->addColumn($this->lng->txt('active'), '', '5%');
        $this->addColumn($this->lng->txt('actions'), '', '15%');

        $this->getItems();
    }

    /**
     *
     */
    protected function getItems()
    {
        $idp_data = array();

        foreach (ilSamlIdp::getAllIdps() as $idp) {
            $idp_data[] = $idp->toArray();
        }

        $this->setData($idp_data);
    }

    /**
     * @param array $a_set
     */
    protected function fillRow($a_set)
    {
        if ($a_set['is_active']) {
            $this->tpl->setVariable('IMAGE_OK', ilUtil::getImagePath('icon_ok.svg'));
            $this->tpl->setVariable('TXT_OK', $this->lng->txt('active'));
        } else {
            $this->tpl->setVariable('IMAGE_OK', ilUtil::getImagePath('icon_not_ok.svg'));
            $this->tpl->setVariable('TXT_OK', $this->lng->txt('inactive'));
        }

        $this->tpl->setVariable('NAME', $a_set['entity_id']);

        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('small');
        $list->setItemLinkClass('small');
        $list->setId('actl_' . $a_set['idp_id']);
        $list->setListTitle($this->lng->txt('actions'));

        $this->ctrl->setParameter($this->getParentObject(), 'saml_idp_id', $a_set['idp_id']);

        $list->addItem(
            $this->lng->txt('edit'),
            '',
            $this->ctrl->getLinkTarget($this->getParentObject(), 'showIdpSettings')
        );

        if ($a_set['is_active']) {
            $list->addItem(
                $this->lng->txt('deactivate'),
                '',
                $this->ctrl->getLinkTarget($this->getParentObject(), 'deactivateIdp')
            );
        } else {
            $list->addItem(
                $this->lng->txt('activate'),
                '',
                $this->ctrl->getLinkTarget($this->getParentObject(), 'activateIdp')
            );
        }

        $list->addItem(
            $this->lng->txt('delete'),
            '',
            $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDeleteIdp')
        );
        $this->ctrl->setParameter($this->getParentObject(), 'saml_idp_id', '');

        if ($this->mayEdit) {
            $this->tpl->setVariable('ACTIONS', $list->getHTML());
        }
    }
}
