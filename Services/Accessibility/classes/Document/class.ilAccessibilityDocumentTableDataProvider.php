<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilityDocumentTableDataProvider
 */
class ilAccessibilityDocumentTableDataProvider implements ilAccessibilityTableDataProvider
{
    /**
     * @inheritdoc
     */
    public function getList(array $params, array $filter) : array
    {
        $items = ilAccessibilityDocument::orderBy('sorting')->get();

        return [
            'items' => $items,
            'cnt' => count($items)
        ];
    }
}
