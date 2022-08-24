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

/**
 * Interface ilTermsOfServiceTableDataProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceTableDataProvider
{
    /**
     * @param array $params Table parameters like limit or order
     * @param array $filter Filter settings provided by a ilTable2GUI instance
     * @return array An associative array with keys 'items' (array of items) and 'cnt' (number of total items)
     */
    public function getList(array $params, array $filter): array;
}
