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
 * class representing a glossary term as structure object
 * @author  Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 * @version $Id: class.ilSoapStructureReader.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
 * @package ilias
 */
class ilSoapGLOTermStructureObject extends ilSoapStructureObject
{
    public function getInternalLink(): string
    {
        return "[iln term=\"" . $this->getObjId() . "\"]" . $this->getTitle() . "[/iln]";
    }
}
