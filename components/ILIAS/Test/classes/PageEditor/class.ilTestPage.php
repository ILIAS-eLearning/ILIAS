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

declare(strict_types=1);

/**
 *
 * @ilCtrl_Calls ilTestEditPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilTestEditPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilTestEditPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 */
class ilTestPage extends ilPageObject
{
    public function getParentType(): string
    {
        return 'tst';
    }

    public function createPageWithNextId(): int
    {
        $query = $this->db->query('SELECT max(page_id) as last_id FROM page_object WHERE parent_type="'
            . $this->getParentType() . '"');
        try {
            $assoc = $this->db->fetchAssoc($query);
            $this->setId(
                $assoc['last_id'] + 1
            );
            $this->createFromXML();
        } catch (Exception $e) {
            $this->createPageWithNextId();
        }

        return $this->getId();
    }
}
