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
        $pages = [];

        foreach (ilPageObject::getAllPages(self::OBJ_TYPE, $obj_id) as $page) {
            $pages[] = [
                'parent_type' => self::OBJ_TYPE,
                'id' => $page['id'],
                'lang' => $page['lang'],
            ];
        }

        return $pages;
    }
}