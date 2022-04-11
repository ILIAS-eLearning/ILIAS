<?php

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

namespace ILIAS\Table;

use ILIAS\Repository;

class TableGUIRequest
{
    use Repository\BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    /**
     * @param string $prefix
     */
    public function getExportMode($prefix) : bool
    {
        return (bool) $this->int($prefix . "_xpt");
    }

    /**
     * @param string $prefix
     */
    public function getTemplate($prefix) : string
    {
        return $this->str($prefix . "_tpl");
    }

    /**
     * @param string $prefix
     */
    public function getRows($prefix) : ?int
    {
        $rows = $this->str($prefix . "_trows");
        if ($rows == "") {
            return null;
        }
        return (int) $rows;
    }

    public function getPostVar() : string
    {
        return $this->str("postvar");
    }

    /**
     * @param int $nr
     */
    public function getNavPar(string $np, $nr = 0) : string
    {
        if ($nr > 0) {
            $np .= (string) $nr;
        }
        return $this->str($np);
    }

    /**
     * @param string $id
     */
    public function getFF($id) : array
    {
        return $this->strArray("tblff" . $id);
    }

    /**
     * @param string $id
     */
    public function getFS($id) : array
    {
        return $this->strArray("tblfs" . $id);
    }

    /**
     * @param string $id
     */
    public function getFSH($id) : bool
    {
        return (bool) $this->int("tblfsh" . $id);
    }

    /**
     * @param string $id
     */
    public function getFSF($id) : bool
    {
        return (bool) $this->int("tblfsf" . $id);
    }

    public function getTemplCreate() : string
    {
        return $this->str("tbltplcrt");
    }

    public function getTemplDelete() : string
    {
        return $this->str("tbltpldel");
    }

    public function getTableId() : string
    {
        return $this->str("table_id");
    }

    public function getUserId() : int
    {
        return $this->int("user_id");
    }
}
