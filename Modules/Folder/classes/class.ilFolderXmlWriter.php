<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * XML writer for folders
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilFolderXmlWriter extends ilXmlWriter
{
    private bool $add_header;
    private int $obj_id = 0;
    private ?ilObject $folder = null;

    public function __construct(bool $a_add_header)
    {
        $this->add_header = $a_add_header;
        parent::__construct();
    }
    
    public function setObjId(int $a_obj_id) : void
    {
        $this->obj_id = $a_obj_id;
    }
    
    public function write() : void
    {
        $this->init();
        if ($this->add_header) {
            $this->buildHeader();
        }
        $this->xmlStartTag('Folder', ['Id' => $this->folder->getId()]);
        $this->xmlElement('Title', [], $this->folder->getTitle());
        $this->xmlElement('Description', [], $this->folder->getDescription());
        ilContainerSortingSettings::_exportContainerSortingSettings($this, $this->obj_id);
        $this->xmlEndTag('Folder');
    }
    
    protected function buildHeader() : void
    {
        $this->xmlSetDtdDef("<!DOCTYPE WebLinks PUBLIC \"-//ILIAS//DTD WebLinkAdministration//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_fold_4_5.dtd\">");
        $this->xmlSetGenCmt("Export of a ILIAS Folder");
        $this->xmlHeader();
    }
    
    protected function init() : void
    {
        $this->xmlClear();
        
        if (!$this->obj_id) {
            throw new UnexpectedValueException('No obj_id given: ');
        }
        if (!$this->folder = ilObjectFactory::getInstanceByObjId($this->obj_id, false)) {
            throw new UnexpectedValueException('Invalid obj_id given: ' . $this->obj_id);
        }
        if ($this->folder->getType() !== 'fold') {
            throw new UnexpectedValueException('Invalid obj_id given. Object is not of type folder');
        }
    }
}
