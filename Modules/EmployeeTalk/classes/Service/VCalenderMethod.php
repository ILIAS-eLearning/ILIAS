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

/**
 * Interface VCalenderMethod
 *
 * RFC 5546
 *
 *  +----------------+--------------------------------------------------+
 *  | Method         | Description                                      |
 *  +----------------+--------------------------------------------------+
 *  | PUBLISH        | Used to publish an iCalendar object to one or    |
 *  |                | more "Calendar Users".  There is no              |
 *  |                | interactivity between the publisher and any      |
 *  |                | other "Calendar User".  An example might include |
 *  |                | a baseball team publishing its schedule to the   |
 *  |                | public.                                          |
 *  |                |                                                  |
 *  | REQUEST        | Used to schedule an iCalendar object with other  |
 *  |                | "Calendar Users".  Requests are interactive in   |
 *  |                | that they require the receiver to respond using  |
 *  |                | the reply methods.  Meeting requests, busy-time  |
 *  |                | requests, and the assignment of tasks to other   |
 *  |                | "Calendar Users" are all examples.  Requests are |
 *  |                | also used by the Organizer to update the status  |
 *  |                | of an iCalendar object.                          |
 *  |                |                                                  |
 *  | REPLY          | A reply is used in response to a request to      |
 *  |                | convey Attendee status to the Organizer.         |
 *  |                | Replies are commonly used to respond to meeting  |
 *  |                | and task requests.                               |
 *  |                |                                                  |
 *  | ADD            | Add one or more new instances to an existing     |
 *  |                | recurring iCalendar object.                      |
 *  |                |                                                  |
 *  | CANCEL         | Cancel one or more instances of an existing      |
 *  |                | iCalendar object.                                |
 *  |                |                                                  |
 *  | REFRESH        | Used by an Attendee to request the latest        |
 *  |                | version of an iCalendar object.                  |
 *  |                |                                                  |
 *  | COUNTER        | Used by an Attendee to negotiate a change in an  |
 *  |                | iCalendar object.  Examples include the request  |
 *  |                | to change a proposed event time or change the    |
 *  |                | due date for a task.                             |
 *  |                |                                                  |
 *  | DECLINECOUNTER | Used by the Organizer to decline the proposed    |
 *  |                | counter proposal.                                |
 *  +----------------+--------------------------------------------------+
 *
 * @package ILIAS\EmployeeTalk\Service
 */
interface VCalenderMethod
{
    const PUBLISH = 'PUBLISH';
    const REQUEST = 'REQUEST';
    const REPLY = 'REPLY';
    const ADD = 'ADD';
    const CANCEL = 'CANCEL';
    const COUNTER = 'COUNTER';
    const DECLINECOUNTER = 'DECLINECOUNTER';
}