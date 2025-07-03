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

use ILIAS\EmployeeTalk\Talk\DAO\EmployeeTalk;
use ILIAS\EmployeeTalk\TalkSeries\Repository\IliasDBEmployeeTalkSeriesRepository;

final class ilObjEmployeeTalkSeries extends ilContainer
{
    public const string TYPE = 'tals';

    private IliasDBEmployeeTalkSeriesRepository $repository;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->setType(self::TYPE);

        parent::__construct($a_id, $a_call_by_reference);

        $this->repository = new IliasDBEmployeeTalkSeriesRepository($this->user, $this->db);
    }

    public function create(): int
    {
        parent::create();

        $this->_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::CUSTOM_METADATA, '1');


        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];

        $container->event()->raise(
            'components/ILIAS/EmployeeTalk',
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
        $ret = parent::update();

        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];

        $container->event()->raise(
            'components/ILIAS/EmployeeTalk',
            'update',
            ['object' => $this,
                  'obj_id' => $this->getId(),
                  'appointments' => []
            ]
        );
        return $ret;
    }

    public static function _exists(int $id, bool $reference = false, ?string $type = null): bool
    {
        return parent::_exists($id, $reference, self::TYPE);
    }

    /**
     * delete orgunit, childs and all related data
     * @return    bool    true if all object data were removed; false if only a references were
     *                       removed
     */
    public function delete(): bool
    {
        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];

        $container->event()->raise(
            'components/ILIAS/EmployeeTalk',
            'delete',
            [
                'object' => $this,
                'obj_id' => $this->getId(),
                'appointments' => []
            ]
        );

        $this->repository->deleteEmployeeTalkSerieSettings($this->getId());
        $node_data = $this->tree->getNodeData($this->getRefId());
        $result = parent::delete();
        $this->tree->deleteNode($node_data['tree'], $this->getRefId());

        return $result;
    }

    public function hasChildren(): bool
    {
        $children = $this->tree->getChildIds($this->getRefId());
        return count($children) > 0;
    }

    /**
     * @return ilObjEmployeeTalk[]
     */
    public function getChildTalks(): array
    {
        $child_ids = $this->tree->getChildIds($this->getRefId());
        $child_talks = [];
        foreach ($child_ids as $id) {
            $child_talks[] = new ilObjEmployeeTalk($id, true);
        }
        return $child_talks;
    }
}
