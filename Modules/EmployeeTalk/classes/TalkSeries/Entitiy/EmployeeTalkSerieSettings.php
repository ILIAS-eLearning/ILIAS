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

namespace ILIAS\Modules\EmployeeTalk\TalkSeries\Entity;

use ActiveRecord;

/**
 * Class EmployeeTalkSerie
 */
final class EmployeeTalkSerieSettings extends ActiveRecord
{

    /** @var string  */
    protected string $connector_container_name = 'etal_serie';
     
    /**
     * @var ?int $id
     * @con_has_field  true
     * @con_is_primary true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_is_notnull true
     */
    protected ?int $id = -1;
    /**
     * @var integer $editing_locked
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected int $editing_locked = 0;


    /**
     * @return int
     */
    public function getId() : int
    {
        return intval($this->id);
    }

    /**
     * @param int $id
     * @return EmployeeTalkSerieSettings
     */
    public function setId(int $id) : EmployeeTalkSerieSettings
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getEditingLocked() : int
    {
        return $this->editing_locked;
    }

    /**
     * @param int $editing_locked
     * @return EmployeeTalkSerieSettings
     */
    public function setEditingLocked(int $editing_locked) : EmployeeTalkSerieSettings
    {
        $this->editing_locked =  $editing_locked;
        return $this;
    }
}
