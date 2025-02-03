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

declare(strict_types=1);
/**
 * Class ilRepositoryObjectSearchGUI
 * Repository object detail search
 *
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @package ServicesSearch
 *
 */
class ilRepositoryObjectDetailSearchResult
{
    private array $results = array();

    public function __construct()
    {
    }

    /**
     * Get results
     * Db search : array(
     * array(
     *   'obj_id' => 1,
     *   'item_id' => 123)
     * );
     * Lucene search: array(
     * array(
     *   'obj_id' => 1
     *   'item_id' => 123
     *	 'relevance' => '100%'
     *	 'content' => 'this is a <span class="ilSearchHighlight">hit</span>'
     */
    public function getResults(): array
    {
        return $this->results;
    }

    public function addResultSet(array $result_set): void
    {
        $this->results[] = $result_set;
    }
}
