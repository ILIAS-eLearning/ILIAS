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

/**
 * class representing a repository object as structure object
 * @author  Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 * @version $Id: class.ilSoapStructureReader.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
 * @package ilias
 */
class ilSoapRepositoryStructureObject extends ilSoapStructureObject
{
    protected int $ref_id = 0;

    public function __construct(int $objId, string $type, string $title, string $description, ?int $refId = null)
    {
        parent::__construct($objId, $type, $title, $description);
        $this->setRefId($refId);
    }

    public function setRefId(int $value): void
    {
        $this->ref_id = $value;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function getInternalLink(): string
    {
        return "[iln " . $this->getType() . "=\"" . $this->getRefId() . "\"]" . $this->getTitle() . "[/iln]";
    }

    public function getGotoLink(): string
    {
        return ILIAS_HTTP_PATH . "/" . "goto.php?target=" . $this->getType() . "_" . $this->getRefId() . "&client_id=" . CLIENT_ID;
    }

    /**
     * @return array{type: string, ref_id: int, obj_id: int}
     */
    public function _getXMLAttributes(): array
    {
        return [
            'type' => $this->getType(),
            'obj_id' => $this->getObjId(),
            'ref_id' => $this->getRefId()
        ];
    }

    public function _getTagName(): string
    {
        return "RepositoryObject";
    }
}
