<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    public function getList(array $params, array $filter) : array;
}
