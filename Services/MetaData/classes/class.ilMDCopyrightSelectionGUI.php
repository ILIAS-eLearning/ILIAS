<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesMetaData
 */
class ilMDCopyrightSelectionGUI
{
    public const MODE_QUICKEDIT = 1;
    public const MODE_EDIT = 2;

    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilMDSettings $settings;

    private int $rbac_id;
    private int $obj_id;

    public function __construct(int $a_mode, int $a_rbac_id, int $a_obj_id)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();

        $this->rbac_id = $a_rbac_id;
        $this->obj_id = $a_obj_id;

        $this->settings = ilMDSettings::_getInstance();
    }

    public function fillTemplate() : bool
    {
        $desc = ilMDRights::_lookupDescription($this->rbac_id, $this->obj_id);

        if (!$this->settings->isCopyrightSelectionActive() or
            !count($entries = ilMDCopyrightSelectionEntry::_getEntries())) {
            $this->tpl->setVariable("TXT_COPYRIGHT", $this->lng->txt('meta_copyright'));
            $this->tpl->setVariable(
                'COPYRIGHT_VAL',
                ilLegacyFormElementsUtil::prepareFormOutput($desc)
            );
            return true;
        }

        $default_id = ilMDCopyrightSelectionEntry::_extractEntryId($desc);

        $found = false;
        foreach ($entries as $entry) {
            $this->tpl->setCurrentBlock('copyright_selection');

            if ($entry->getEntryId() === $default_id) {
                $found = true;
                $this->tpl->setVariable('COPYRIGHT_CHECKED', 'checked="checked"');
            }
            $this->tpl->setVariable('COPYRIGHT_ID', $entry->getEntryId());
            $this->tpl->setVariable('COPYRIGHT_TITLE', $entry->getTitle());
            $this->tpl->setVariable('COPYRIGHT_DESCRIPTION', $entry->getDescription());
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock('copyright_selection');
        if (!$found) {
            $this->tpl->setVariable('COPYRIGHT_CHECKED', 'checked="checked"');
        }
        $this->tpl->setVariable('COPYRIGHT_ID', 0);
        $this->tpl->setVariable('COPYRIGHT_TITLE', $this->lng->txt('meta_cp_own'));

        $this->tpl->setVariable("TXT_COPYRIGHT", $this->lng->txt('meta_copyright'));
        if (!$found) {
            $this->tpl->setVariable('COPYRIGHT_VAL', $desc);
        }
        return false;
    }
}
