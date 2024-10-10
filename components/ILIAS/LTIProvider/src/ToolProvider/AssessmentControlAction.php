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

namespace ILIAS\LTI\ToolProvider;

/**
 * Class to represent an assessment control action
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class AssessmentControlAction
{
    /**
     * Pause action.
     */
    public const ACTION_PAUSE = 'pause';

    /**
     * Pause action.
     */
    public const ACTION_RESUME = 'resume';

    /**
     * Pause action.
     */
    public const ACTION_TERMINATE = 'terminate';

    /**
     * Pause action.
     */
    public const ACTION_UPDATE = 'update';

    /**
     * Pause action.
     */
    public const ACTION_FLAG = 'flag';

    /**
     * Extra time.
     *
     * @var int|null $extraTime
     */
    public ?int $extraTime = null;

    /**
     * Reason code.
     *
     * @var string|null $code
     */
    public ?string $code = null;

    /**
     * Reason message.
     *
     * @var string|null $message
     */
    public ?string $message = null;

    /**
     * Action.
     *
     * @var string|null $action
     */
    private ?string $action = null;

    /**
     * Incident date value.
     *
     * @var int|null $date //UK: changed DateTime to int
     */
    private ?int $date = null;

    /**
     * Severity.
     *
     * @var float|null $severity
     */
    private ?float $severity = null;

    /**
     * Class constructor.
     * @param string   $action   Action
     * @param int $date     Date/time of incident  //UK: changed DateTime to int
     * @param float    $severity Severity of incident
     */
    public function __construct(string $action, int $date, float $severity)
    {
        $this->action = $action;
        $this->date = $date;
        $this->severity = $severity;
    }

    /**
     * Get the action.
     *
     * @return string Action value
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * Get the incident date.
     *
     * @return int Incident date value  //UK: changed DateTime to int
     */
    public function getDate(): ?int
    {
        return $this->date;
    }

    /**
     * Get the severity.
     *
     * @return float Severity value
     */
    public function getSeverity(): ?float
    {
        return $this->severity;
    }
}
