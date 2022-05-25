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

namespace ILIAS\EmployeeTalk\Service;

final class EmployeeTalkEmailNotification
{
    private string $salutation;
    private string $dateHeader;
    private string $talkTitle;
    private string $appointmentDetails;
    /**
     * @var string[] $dates
     */
    private array $dates;

    /**
     * EmployeeTalkEmailNotification constructor.
     * @param string $salutation
     * @param string $dateHeader
     * @param string $talkTitle
     * @param string $appointmentDetails
     * @param string[] $dates
     */
    public function __construct(
        string $salutation,
        string $dateHeader,
        string $talkTitle,
        string $appointmentDetails,
        array $dates
    ) {
        $this->salutation = $salutation;
        $this->dateHeader = $dateHeader;
        $this->talkTitle = $talkTitle;
        $this->appointmentDetails = $appointmentDetails;
        $this->dates = $dates;
    }

    /**
     * @return string
     */
    public function getSalutation() : string
    {
        return $this->salutation;
    }

    /**
     * @return string
     */
    public function getDateHeader() : string
    {
        return $this->dateHeader;
    }

    /**
     * @return string
     */
    public function getTalkTitle() : string
    {
        return $this->talkTitle;
    }

    /**
     * @return string
     */
    public function getAppointmentDetails() : string
    {
        return $this->appointmentDetails;
    }

    /**
     * @return string[]
     */
    public function getDates() : array
    {
        return $this->dates;
    }

    public function __toString(): string
    {
        $dateList = "";
        foreach ($this->dates as $date) {
            $dateList .= "- $date\r\n";
        }

        return $this->getSalutation() . "\r\n\r\n"
            . $this->getAppointmentDetails() . "\r\n"
            . $this->getTalkTitle() . "\r\n\r\n"
            . $this->getDateHeader() . ":\r\n"
            . $dateList;
    }

}
