<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Base class for copying meta data from xml
 * It is possible to overwrite single elements. See handling of identifier tag
 * @package ilias-core
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 */
class ilMDXMLCopier extends ilMDSaxParser
{
    private array $filter = [];
    protected bool $in_meta_data = false;

    public function __construct($content, $a_rbac_id, $a_obj_id, $a_obj_type)
    {
        $this->setMDObject(new ilMD($a_rbac_id, $a_obj_id, $a_obj_type));

        parent::__construct();
        $this->setXMLContent($content);

        // set filter of tags which are handled in this class
        $this->__setFilter();
    }

    /**
     * @param XMLParser|resource $a_xml_parser reference to the xml parser
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs) : void
    {
        if ($this->in_meta_data && !$this->__inFilter($a_name)) {
            parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
            return;
        }

        switch ($a_name) {
            case 'MetaData':
                $this->in_meta_data = true;
                parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;

            case 'Identifier':
                $par = $this->__getParent();
                $this->md_ide = $par->addIdentifier();
                $this->md_ide->setCatalog($a_attribs['Catalog']);
                $this->md_ide->setEntry('il__' . $this->md->getObjType() . '_' . $this->md->getObjId());
                $this->md_ide->save();
                $this->__pushParent($this->md_ide);
                break;
        }
    }

    /**
     * @param XMLParser|resource $a_xml_parser reference to the xml parser
     */
    public function handlerEndTag($a_xml_parser, string $a_name) : void
    {
        if ($this->in_meta_data && !$this->__inFilter($a_name)) {
            parent::handlerEndTag($a_xml_parser, $a_name);
            return;
        }
        switch ($a_name) {
            case 'Identifier':
                $par = $this->__getParent();
                $par->update();
                $this->__popParent();
                break;

            case 'MetaData':
                $this->in_meta_data = false;
                parent::handlerEndTag($a_xml_parser, $a_name);
                break;
        }
    }

    /**
     * @param XMLParser|resource $a_xml_parser reference to the xml parser
     */
    public function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
        if ($this->in_meta_data) {
            parent::handlerCharacterData($a_xml_parser, $a_data);
        }
    }

    public function __setFilter() : void
    {
        $this->filter[] = 'Identifier';
    }

    public function __inFilter(string $a_tag_name) : bool
    {
        return in_array($a_tag_name, $this->filter, true);
    }
}
