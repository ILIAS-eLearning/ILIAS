<?php declare(strict_types=1);

/**
 * Class ilContentPagePageCollector
 */
class ilContentPagePageCollector implements ilCOPageCollectorInterface, ilContentPageObjectConstants
{
    /**
     * @inheritDoc
     */
    public function getAllPageIds($obj_id)
    {
        return [
            [
                'parent_type' => self::OBJ_TYPE,
                'id' => $obj_id,
                'lang' => '-'
            ],
        ];
    }
}