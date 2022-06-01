<?php declare(strict_types=1);

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
 * Class ilStudyProgrammeAutoCategory
 *
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilStudyProgrammeAutoCategory
{
    protected int $prg_obj_id;
    protected int $category_ref_id;
    protected string $title;
    protected int $last_edited_usr_id;
    protected DateTimeImmutable $last_edited;

    public function __construct(
        int $prg_obj_id,
        int $category_ref_id,
        int $last_edited_usr_id,
        DateTimeImmutable $last_edited
    ) {
        $this->prg_obj_id = $prg_obj_id;
        $this->category_ref_id = $category_ref_id;
        $this->last_edited_usr_id = $last_edited_usr_id;
        $this->last_edited = $last_edited;
    }

    public function getPrgObjId() : int
    {
        return $this->prg_obj_id;
    }

    public function getCategoryRefId() : int
    {
        return $this->category_ref_id;
    }

    public function getLastEditorId() : int
    {
        return $this->last_edited_usr_id;
    }

    public function getLastEdited() : DateTimeImmutable
    {
        return $this->last_edited;
    }
}
