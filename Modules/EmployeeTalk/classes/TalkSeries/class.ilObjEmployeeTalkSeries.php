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

use ILIAS\Modules\EmployeeTalk\Talk\Repository\EmployeeTalkRepository;
use ILIAS\Modules\EmployeeTalk\Talk\DAO\EmployeeTalk;

final class ilObjEmployeeTalkSeries extends ilContainer
{
    public const TYPE = 'tals';

    /**
     * @var EmployeeTalkRepository $repository
     */
    private EmployeeTalkRepository $repository;

    /**
     * @var bool $locked_editing
     */
    private bool $locked_editing = false;

    /**
     * @param int  $a_id
     * @param bool $a_call_by_reference
     */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true, bool $locked_editing = false)
    {
        $this->setType(self::TYPE);
        $this->locked_editing = $locked_editing;

        parent::__construct($a_id, $a_call_by_reference);
    }

    public function read(): void
    {
        parent::read();
    }

    public function create(): int
    {
        $this->setOfflineStatus(true);


        //TODO: Copy metadata from template
        parent::create();

        $this->_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::CUSTOM_METADATA, '1');


        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];

        $container->event()->raise(
            'Modules/EmployeeTalk',
            'create',
            ['object' => $this,
             'obj_id' => $this->getId(),
             'appointments' => []
            ]
        );

        return $this->getId();
    }



    public function update(): bool
    {
        parent::update();

        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];

        $container->event()->raise(
            'Modules/EmployeeTalk',
            'update',
            ['object' => $this,
                  'obj_id' => $this->getId(),
                  'appointments' => []
            ]
        );
    }

    /**
     * @param int         $a_id
     * @param bool        $a_reference
     * @param string|null $type
     * @return bool
     */
    public static function _exists(int $a_id, bool $a_reference = false, ?string $type = null): bool
    {
        return parent::_exists($a_id, $a_reference, self::TYPE);
    }

    /**
     * delete orgunit, childs and all related data
     * @return    boolean    true if all object data were removed; false if only a references were
     *                       removed
     */
    public function delete(): bool
    {
        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];

        $container->event()->raise(
            'Modules/EmployeeTalk',
            'delete',
            [
                'object' => $this,
                'obj_id' => $this->getId(),
                'appointments' => []
            ]
        );

        return parent::delete();
    }

    public function hasChildren(): bool
    {
        $children = $this->tree->getChildIds(intval($this->getRefId()));
        return count($children) > 0;
    }
}
