<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Item group active record class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilExAssWikiTeamAR extends ActiveRecord
{
    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return 'exc_ass_wiki_team';
    }

    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     * @con_length     4
     * @con_sequence   false
     */
    protected $id;

    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    4
     * @con_is_notnull false
     */
    protected $template_ref_id = 0;

    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    4
     * @con_is_notnull false
     */
    protected $container_ref_id = 0;


    /**
     * Get ID
     *
     * @return int ID
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set ID
     *
     * @param int $id ID
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * Set template ref id
     *
     * @param int $a_template_ref_id template ref id
     */
    public function setTemplateRefId($a_template_ref_id)
    {
        $this->template_ref_id = $a_template_ref_id;
    }


    /**
     * Get template ref id
     *
     * @return int
     */
    public function getTemplateRefId()
    {
        return $this->template_ref_id;
    }


    /**
     * Set container ref id
     *
     * @param int $a_container_ref_id container ref id
     */
    public function setContainerRefId($a_container_ref_id)
    {
        $this->container_ref_id = $a_container_ref_id;
    }


    /**
     * Get container ref id
     *
     * @return int
     */
    public function getContainerRefId()
    {
        return $this->container_ref_id;
    }
}
