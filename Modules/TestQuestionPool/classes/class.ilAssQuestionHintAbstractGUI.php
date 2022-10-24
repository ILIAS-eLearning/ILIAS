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
 * abstract parent class for concrete question hint GUI classes
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Grégory Saive <gsaive@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
abstract class ilAssQuestionHintAbstractGUI
{
    protected \ILIAS\TestQuestionPool\InternalRequestService $request;

    /**
     * gui instance of current question
     *
     * @access	protected
     * @var		assQuestionGUI
     */
    protected $questionGUI = null;

    /**
     * object instance of current question
     *
     * @access	protected
     * @var		assQuestion
     */
    protected $questionOBJ = null;

    /**
     * Constructor
     *
     * @access	public
     * @param	assQuestionGUI	$questionGUI
     */
    public function __construct(assQuestionGUI $questionGUI)
    {
        $this->questionGUI = $questionGUI;
        $this->questionOBJ = $questionGUI->object;
        global $DIC;
        $this->request = $DIC->testQuestionPool()->internal()->request();
    }
}
