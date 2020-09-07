<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentTableDataProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentTableDataProvider implements \ilTermsOfServiceTableDataProvider
{
    /**
     * @inheritdoc
     */
    public function getList(array $params, array $filter) : array
    {
        $items = \ilTermsOfServiceDocument::orderBy('sorting')->get();

        return [
            'items' => $items,
            'cnt' => count($items)
        ];
    }
}
