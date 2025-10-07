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

/**
 * Class ilLTIConsumerGradeSynchronizationGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilLTIConsumerGradeSynchronizationGUI
{
    protected ilObjLTIConsumer $object;

    protected ilLTIConsumerAccess $access;

    public function __construct(ilObjLTIConsumer $object)
    {
        $this->object = $object;

        $this->access = ilLTIConsumerAccess::getInstance($this->object);
    }

    /**
     * @throws ilLtiConsumerException|ilCtrlException
     */
    public function executeCommand(): bool
    {
        global $DIC;

        if (!$this->object->getProvider()->isGradeSynchronization()) {
            throw new ilLtiConsumerException('access denied!');
        }

        switch ($DIC->ctrl()->getNextClass($this)) {
            default:
                $cmd = $DIC->ctrl()->getCmd('show') . 'Cmd';
                $this->{$cmd}();
        }
        return true;
    }

    protected function showCmd(): void
    {
        global $DIC;

        $isMultiActorReport = $this->access->hasOutcomesAccess();

        $table = new ilLTIConsumerGradeSynchronizationTableGUI($isMultiActorReport);

        $cUser = null;
        if (!$this->access->hasOutcomesAccess()) {
            $cUser = $DIC->user()->getId();
        }

        $data = ilLTIConsumerGradeSynchronization::getGradesForObject(
            $this->object->getId(),
            $cUser
        );

        $table->setRecords($data);

        $DIC->ui()->mainTemplate()->setContent($table->getHTML());
    }
}
