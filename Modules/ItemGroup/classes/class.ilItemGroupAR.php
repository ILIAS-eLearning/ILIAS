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
 * Item group active record class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilItemGroupAR extends ActiveRecord
{
    public static function returnDbTableName() : string
    {
        return 'itgr_data';
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
    protected ?int $id;

    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    1
     * @con_is_notnull false
     */
    protected ?string $hide_title = '';

    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    1
     * @con_is_notnull false
     */
    protected ?int $behaviour = 0;

    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    10
     * @con_is_notnull false
     */
    protected ?string $list_presentation = "";

    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    1
     * @con_is_notnull false
     */
    protected ?int $tile_size = 0;

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    public function setHideTitle(bool $a_hide_title) : void
    {
        $this->hide_title = $a_hide_title;
    }

    public function getHideTitle() : bool
    {
        return $this->hide_title;
    }

    public function setBehaviour(int $a_val) : void
    {
        $this->behaviour = $a_val;
    }

    public function getBehaviour() : int
    {
        return $this->behaviour;
    }

    public function getListPresentation() : string
    {
        return (string) $this->list_presentation;
    }

    public function setListPresentation(string $a_val) : void
    {
        $this->list_presentation = $a_val;
    }

    public function getTileSize() : int
    {
        return (int) $this->tile_size;
    }

    public function setTileSize(int $a_val) : void
    {
        $this->tile_size = $a_val;
    }
}
