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

namespace ILIAS\Notes;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class Context
{
    protected int $obj_id = 0;
    protected int $sub_obj_id = 0;
    protected int $news_id = 0;
    protected string $type = "";
    protected bool $in_repo = false;

    public function __construct(
        int $obj_id = 0,
        int $sub_obj_id = 0,
        string $type = "",
        int $news_id = 0,
        bool $in_repo = true
    ) {
        $this->obj_id = $obj_id;
        $this->sub_obj_id = $sub_obj_id;
        $this->type = $type;
        $this->news_id = $news_id;
        $this->in_repo = $in_repo;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getSubObjId() : int
    {
        return $this->sub_obj_id;
    }

    public function getNewsId() : int
    {
        return $this->news_id;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getInRepository() : bool
    {
        return $this->in_repo;
    }
}
