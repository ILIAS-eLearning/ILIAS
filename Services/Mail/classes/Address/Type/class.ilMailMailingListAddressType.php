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

/**
 * Class ilMailMailingListAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMailingListAddressType extends ilBaseMailAddressType
{
    private ilMailingLists $lists;

    public function __construct(
        ilMailAddressTypeHelper $typeHelper,
        ilMailAddress $address,
        ilLogger $logger,
        ilMailingLists $lists
    ) {
        parent::__construct($typeHelper, $address, $logger);

        $this->lists = $lists;
    }

    protected function isValid(int $senderId): bool
    {
        $valid = $this->lists->mailingListExists($this->address->getMailbox());

        if (!$valid) {
            $this->logger->debug(sprintf(
                "Mailing list not  valid: '%s'",
                $this->address->getMailbox()
            ));
            $this->pushError('mail_no_valid_mailing_list', [$this->address->getMailbox()]);
        }

        return $valid;
    }

    public function resolve(): array
    {
        $usrIds = [];

        if ($this->lists->mailingListExists($this->address->getMailbox())) {
            foreach ($this->lists->getCurrentMailingList()->getAssignedEntries() as $entry) {
                $usrIds[] = $entry['usr_id'];
            }

            $this->logger->debug(sprintf(
                "Found the following user ids for address (mailing list title) '%s': %s",
                $this->address->getMailbox(),
                implode(', ', array_unique($usrIds))
            ));
        } else {
            $this->logger->debug(sprintf(
                "Did not find any user ids for address (mailing list title) '%s'",
                $this->address->getMailbox()
            ));
        }

        return array_unique($usrIds);
    }
}
