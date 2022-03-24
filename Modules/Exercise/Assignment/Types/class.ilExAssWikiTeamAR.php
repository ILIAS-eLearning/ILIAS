<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Item group active record class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssWikiTeamAR extends ActiveRecord
{
    public static function returnDbTableName() : string
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
    protected int $id;

    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    4
     * @con_is_notnull false
     */
    protected int $template_ref_id = 0;

    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    4
     * @con_is_notnull false
     */
    protected int $container_ref_id = 0;


    /**
     * Get ID
     *
     * @return int ID
     */
    public function getId() : int
    {
        return $this->id;
    }


    /**
     * Set ID
     *
     * @param int $id ID
     */
    public function setId(int $id) : void
    {
        $this->id = $id;
    }


    /**
     * Set template ref id
     *
     * @param int $a_template_ref_id template ref id
     */
    public function setTemplateRefId(int $a_template_ref_id) : void
    {
        $this->template_ref_id = $a_template_ref_id;
    }


    /**
     * Get template ref id
     */
    public function getTemplateRefId() : int
    {
        return $this->template_ref_id;
    }


    /**
     * Set container ref id
     *
     * @param int $a_container_ref_id container ref id
     */
    public function setContainerRefId(int $a_container_ref_id) : void
    {
        $this->container_ref_id = $a_container_ref_id;
    }


    /**
     * Get container ref id
     */
    public function getContainerRefId() : int
    {
        return $this->container_ref_id;
    }
}
