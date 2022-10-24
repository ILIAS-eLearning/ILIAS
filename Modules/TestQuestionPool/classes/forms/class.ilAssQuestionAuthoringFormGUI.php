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

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssQuestionAuthoringFormGUI extends ilPropertyFormGUI
{
    /**
     * ilAssQuestionAuthoringFormGUI constructor.
     */
    public function __construct()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $this->lng = $DIC['lng'];

        parent::__construct();
    }

    /**
     * @param assQuestion $questionOBJ
     */
    public function addGenericAssessmentQuestionCommandButtons(assQuestion $questionOBJ): void
    {
        //if( !$this->object->getSelfAssessmentEditingMode() && !$_GET["calling_test"] )
        //	$this->addCommandButton("saveEdit", $this->lng->txt("save_edit"));

        if (!$questionOBJ->getSelfAssessmentEditingMode()) {
            $this->addCommandButton("saveReturn", $this->lng->txt("save_return"));
        }

        $this->addCommandButton("save", $this->lng->txt("save"));
    }

    /**
     * @param ilFormPropertyGUI $replacingItem
     * @return bool
     */
    public function replaceFormItemByPostVar(ilFormPropertyGUI $replacingItem): bool
    {
        $itemWasReplaced = false;

        $preparedItems = array();

        foreach ($this->getItems() as $dodgingItem) {
            /* @var ilFormPropertyGUI $dodgingItem */

            if ($dodgingItem->getPostVar() == $replacingItem->getPostVar()) {
                $preparedItems[] = $replacingItem;
                $itemWasReplaced = true;
                continue;
            }

            $preparedItems[] = $dodgingItem;
        }

        $this->setItems($preparedItems);

        return $itemWasReplaced;
    }
}
