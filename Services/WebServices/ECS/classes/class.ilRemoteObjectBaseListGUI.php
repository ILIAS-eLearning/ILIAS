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

use ILIAS\UI\Component\Card\RepositoryObject;

class ilRemoteObjectBaseListGUI extends ilObjectListGUI
{
    private ilDBInterface $db;

    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->db = $DIC->database();
    }

    /**
     * lookup organization
     */
    public function _lookupOrganization(string $table, int $a_obj_id): string
    {
        $query = "SELECT organization FROM " . $this->db->quoteIdentifier(
            $table
        ) .
            " WHERE obj_id = " . $this->db->quote($a_obj_id, 'integer') . " ";
        $res = $this->db->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->organization;
        }
        return '';
    }

    public function insertTitle(): void
    {
        $this->ctrl->setReturnByClass(
            $this->getGUIClassname(),
            'call'
        );
        $classname = $this->getGUIClassname();
        $consent_gui = new ilECSUserConsentModalGUI(
            $this->user->getId(),
            $this->ref_id,
            new $classname()
        );

        $shy_modal = $consent_gui->getTitleLink();
        if ($shy_modal === '') {
            parent::insertTitle();
            return;
        }

        $this->tpl->setCurrentBlock("item_title");
        $this->tpl->setVariable("TXT_TITLE", $consent_gui->getTitleLink());
        $this->tpl->parseCurrentBlock();
    }

    public function getAsCard(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title,
        string $description
    ): ?RepositoryObject {
        $classname = $this->getGUIClassname();
        $consent_gui = new ilECSUserConsentModalGUI(
            $this->user->getId(),
            $ref_id,
            new $classname()
        );
        if ($consent_gui->hasConsented()) {
            return parent::getAsCard($ref_id, $obj_id, $type, $title, $description);
        }

        $this->ctrl->setReturnByClass(
            $this->getGUIClassname(),
            'call'
        );
        $card = parent::getAsCard($ref_id, $obj_id, $type, $title, $description);
        if ($card instanceof RepositoryObject) {
            return $consent_gui->addConsentModalToCard($card);
        }
        return null;
    }

    public function createDefaultCommand(array $command): array
    {
        $classname = $this->getGUIClassname();
        $consent_gui = new ilECSUserConsentModalGUI(
            $this->user->getId(),
            $this->ref_id,
            new $classname()
        );
        $command = parent::createDefaultCommand($command);
        if ($consent_gui->hasConsented()) {
            return $command;
        }
        $command['link'] = '';
        $command['frame'] = '';
        return $command;
    }

    protected function getGUIClassname(): string
    {
        $classname = $this->obj_definition->getClassName($this->type);
        return 'ilObj' . $classname . 'GUI';
    }
}
