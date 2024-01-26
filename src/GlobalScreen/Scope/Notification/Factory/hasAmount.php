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
namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

/**
 * Interface hasAmount
 * Items can implicitly contain a various amount of news. E.g.
 * A Mail notification, telling the user, that there are 23 unread mails
 * contains a newAmountOf 23 to be displayed by the novelty counter, even
 * if it this information is listed in only one item.
 * @author Timon Amstutz
 */
interface hasAmount
{
    /**
     * Set the amount of old notes, the notification contains.
     * @param int $amount
     * @return StandardNotification
     */
    public function withOldAmount(int $amount = 0) : StandardNotification;

    /**
     * Set the amount of new notes, the notification contains.
     * @param int $amount
     * @return StandardNotification
     */
    public function withNewAmount(int $amount = 0) : StandardNotification;

    /**
     * Get the amount of new notes, the notification contains.
     * @return int
     */
    public function getOldAmount() : int;

    /**
     * Get the amount of new notes, the notification contains.
     * @return int
     */
    public function getNewAmount() : int;
}
