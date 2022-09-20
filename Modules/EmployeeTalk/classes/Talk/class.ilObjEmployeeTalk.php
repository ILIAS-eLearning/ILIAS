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
use ILIAS\Modules\EmployeeTalk\Talk\Repository\IliasDBEmployeeTalkRepository;

final class ilObjEmployeeTalk extends ilObject
{
    public const TYPE = 'etal';

    /**
     * @var int
     */
    private static int $root_ref_id = -1;
    /**
     * @var int
     */
    private static int $root_id = -1;

    /**
     * @var EmployeeTalkRepository $repository
     */
    private $repository;

    /**
     * @var EmployeeTalk $data
     */
    private $data;

    /**
     * @param int  $a_id
     * @param bool $a_call_by_reference
     */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->setType(self::TYPE);

        $this->repository = new IliasDBEmployeeTalkRepository($GLOBALS['DIC']->database());
        $datetime = new ilDateTime(1, IL_CAL_UNIX);
        $this->data = new EmployeeTalk(-1, $datetime, $datetime, false, '', '', -1, false, false);

        parent::__construct($a_id, $a_call_by_reference);
    }

    public function read(): void
    {
        parent::read();
        $this->data = $this->repository->findByObjectId($this->getId());
    }

    public function create(): int
    {
        $this->setOfflineStatus(true);
        parent::create();

        $this->data->setObjectId($this->getId());
        $this->repository->create($this->data);

        $app = new ilCalendarAppointmentTemplate($this->getId());
        $app->setTitle($this->getTitle());
        $app->setSubtitle('');
        $app->setTranslationType(IL_CAL_TRANSLATION_NONE);
        $app->setDescription($this->getLongDescription());
        $app->setStart($this->data->getStartDate());
        $app->setEnd($this->data->getEndDate());
        $app->setLocation($this->data->getLocation());
        $apps[] = $app;

        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];

        $container->event()->raise(
            'Modules/EmployeeTalk',
            'create',
            ['object' => $this,
             'obj_id' => $this->getId(),
             'appointments' => $apps
            ]
        );

        return $this->getId();
    }



    public function update(): bool
    {
        parent::update();
        $this->repository->update($this->data);

        $app = new ilCalendarAppointmentTemplate($this->getParent()->getId());
        $app->setTitle($this->getTitle());
        $app->setSubtitle($this->getParent()->getTitle());
        $app->setTranslationType(IL_CAL_TRANSLATION_NONE);
        $app->setDescription($this->getLongDescription());
        $app->setStart($this->data->getStartDate());
        $app->setEnd($this->data->getEndDate());
        $app->setLocation($this->data->getLocation());
        $apps[] = $app;

        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];

        $container->event()->raise(
            'Modules/EmployeeTalk',
            'update',
            ['object' => $this,
                  'obj_id' => $this->getId(),
                  'appointments' => $apps
            ]
        );

        return true;
    }

    /**
     * @return int
     */
    public static function getRootOrgRefId(): int
    {
        self::loadRootOrgRefIdAndId();

        return self::$root_ref_id;
    }

    /**
     * @return int
     */
    public static function getRootOrgId(): int
    {
        self::loadRootOrgRefIdAndId();

        return self::$root_id;
    }

    private static function loadRootOrgRefIdAndId(): void
    {
        if (self::$root_ref_id === -1 || self::$root_id === -1) {
            global $DIC;
            $ilDB = $DIC['ilDB'];
            $q = "SELECT o.obj_id, r.ref_id FROM object_data o
			INNER JOIN object_reference r ON r.obj_id = o.obj_id
			WHERE title = " . $ilDB->quote('__TalkTemplateAdministration', 'text') . "";
            $set = $ilDB->query($q);
            $res = $ilDB->fetchAssoc($set);
            self::$root_id = (int) $res["obj_id"];
            self::$root_ref_id = (int) $res["ref_id"];
        }
    }

    public function getParent(): ilObjEmployeeTalkSeries
    {
        return new ilObjEmployeeTalkSeries($this->tree->getParentId($this->getRefId()), true);
    }

    /**
     * @param int         $a_id
     * @param bool        $a_reference
     * @param string|null $type
     * @return bool
     */
    public static function _exists(int $a_id, bool $a_reference = false, ?string $type = null): bool
    {
        return parent::_exists($a_id, $a_reference, "etal");
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

        $this->repository->delete($this->getData());
        $nodeData = $this->tree->getNodeData($this->getRefId());
        $result = parent::delete();
        $this->tree->deleteNode(intval($nodeData['tree']), intval($nodeData['child']));

        return $result;
    }

    /**
     * @return EmployeeTalk
     */
    public function getData(): EmployeeTalk
    {
        return clone $this->data;
    }

    /**
     * @param EmployeeTalk $data
     * @return ilObjEmployeeTalk
     */
    public function setData(EmployeeTalk $data): ilObjEmployeeTalk
    {
        $this->data = clone $data;
        return $this;
    }

    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ilObjEmployeeTalk
    {
        /**
         * @var ilObjEmployeeTalk $talkClone
         */
        $talkClone = parent::cloneObject($target_id, $copy_id, $omit_tree);
        $data = $this->getData()->setObjectId($talkClone->getId());
        $this->repository->update($data);
        $talkClone->setData($data);

        return $talkClone;
    }
}
