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
 * Class ilBPMN2ElementLoader
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilBPMN2ElementLoader
{
    /** @var array $bpmn2_array */
    protected array $bpmn2_array;

    /**
     * ilBPMN2ElementLoader constructor.
     *
     * @param $bpmn2_array
     */
    public function __construct(array $bpmn2_array)
    {
        $this->bpmn2_array = $bpmn2_array;
    }

    public function load(string $element_name) : ilBaseElement
    {
        preg_match('/[A-Z]/', $element_name, $matches, PREG_OFFSET_CAPTURE);
        $type = strtolower(substr($element_name, (int) ($matches[0][1] ?? 0)));
        if ($type === 'basedgateway') {
            // Fixing a violation of the standards naming convention by the standard here.
            $type = 'gateway';
        }

        if ($type === 'objectreference') {
            // Fixing a violation of the standards naming convention by the standard here.
            $type = 'object';
        }

        $classname = 'il' . ucfirst($element_name) . 'Element';
        $instance = new $classname;
        $instance->setBPMN2Array($this->bpmn2_array);

        return $instance;
    }
}
