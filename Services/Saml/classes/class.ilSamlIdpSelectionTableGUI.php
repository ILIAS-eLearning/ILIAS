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
 * Class ilSamlIdpSelectionTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilSamlIdpSelectionTableGUI extends ilTable2GUI
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
