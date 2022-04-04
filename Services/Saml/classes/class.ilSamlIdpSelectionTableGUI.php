<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlIdpSelectionTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlIdpSelectionTableGUI extends ilTable2GUI
{
    public function __construct(object $parent_gui, string $parent_cmd)
    {
        $this->setId('saml_idp_selection');
        parent::__construct($parent_gui, $parent_cmd);

        $this->disable('sort');
        $this->disable('header');
        $this->disable('linkbar');
        $this->disable('numinfo');
        $this->setLimit(PHP_INT_MAX);
        $this->setShowRowsSelector(false);
        
        $this->setTitle($this->lng->txt('auth_saml_idp_selection_table_title'));
        $this->setDescription($this->lng->txt('auth_saml_idp_selection_table_desc'));

        $this->setRowTemplate('tpl.saml_idp_selection_row.html', 'Services/Saml');
    }
}
