<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PersonalDesktop/ItemsBlock/interfaces/interface.ilPDSelectedItemsBlockProvider.php';

/**
 * Class ilPDSelectedItemsBlockMembershipsProvider
 */
class ilPDSelectedItemsBlockSelectedItemsProvider implements ilPDSelectedItemsBlockProvider
{
    /**
     * @var ilObjUser
     */
    protected $actor;

    /**
     * ilPDSelectedItemsBlockSelectedItemsProvider constructor.
     * @param ilObjUser $actor
     */
    public function __construct(ilObjUser $actor)
    {
        $this->actor = $actor;
    }

    /**
     * @inheritdoc
     */
    public function getItems($object_type_white_list = array())
    {
        return $this->actor->getDesktopItems(count($object_type_white_list) > 0 ? $object_type_white_list : '');
    }
}
