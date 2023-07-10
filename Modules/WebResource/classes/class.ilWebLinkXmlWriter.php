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
 * XML writer for weblinks
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesWebResource
 */
class ilWebLinkXmlWriter extends ilXmlWriter
{
    private bool $add_header = true;

    private int $obj_id = 0;
    private ?ilObjLinkResource $weblink = null;

    public function __construct(bool $a_add_header)
    {
        $this->add_header = $a_add_header;
        parent::__construct();
    }

    public function setObjId(int $a_obj_id): void
    {
        $this->obj_id = $a_obj_id;
    }

    /**
     * @throws UnexpectedValueException Thrown if obj_id is not of type webr or no obj_id is given
     */
    public function write(): void
    {
        $this->init();
        if ($this->add_header) {
            $this->buildHeader();
        }
        $this->weblink->toXML($this);
    }

    /**
     */
    protected function buildHeader(): bool
    {
        $this->xmlSetDtdDef(
            "<!DOCTYPE WebLinks PUBLIC \"-//ILIAS//DTD WebLinkAdministration//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_weblinks_5_1.dtd\">"
        );
        $this->xmlSetGenCmt("WebLink Object");
        $this->xmlHeader();
        return true;
    }

    /**
     * @throws UnexpectedValueException Thrown if obj_id is not of type webr
     */
    protected function init(): void
    {
        $this->xmlClear();

        if (!$this->obj_id) {
            throw new UnexpectedValueException('No obj_id given: ');
        }
        if (!$this->weblink = ilObjectFactory::getInstanceByObjId(
            $this->obj_id,
            false
        )) {
            throw new UnexpectedValueException(
                'Invalid obj_id given: ' . $this->obj_id
            );
        }
        if ($this->weblink->getType() != 'webr') {
            throw new UnexpectedValueException(
                'Invalid obj_id given. Object is not of type webr'
            );
        }
    }
}
