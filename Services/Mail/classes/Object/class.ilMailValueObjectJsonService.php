<?php declare(strict_types=1);

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
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailValueObjectJsonService
{
    /**
     * @param ilMailValueObject[] $mailValueObjects
     */
    public function convertToJson(array $mailValueObjects) : string
    {
        $mailArray = [];
        foreach ($mailValueObjects as $mailValueObject) {
            $array = [];

            $array['from'] = $mailValueObject->getFrom();
            $array['recipients'] = $mailValueObject->getRecipients();
            $array['recipients_cc'] = $mailValueObject->getRecipientsCC();
            $array['recipients_bcc'] = $mailValueObject->getRecipientsBCC();
            $array['attachments'] = $mailValueObject->getAttachments();
            $array['body'] = $mailValueObject->getBody();
            $array['subject'] = $mailValueObject->getSubject();
            $array['is_using_placholders'] = $mailValueObject->isUsingPlaceholders();
            $array['should_save_in_sent_box'] = $mailValueObject->shouldSaveInSentBox();

            $mailArray[] = $array;
        }

        return json_encode($mailArray, JSON_THROW_ON_ERROR);
    }

    /**
     * @return ilMailValueObject[]
     */
    public function convertFromJson(string $json) : array
    {
        $result = [];
        $array = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        foreach ($array as $objectValues) {
            $result[] = new ilMailValueObject(
                $objectValues['from'],
                $objectValues['recipients'],
                $objectValues['recipients_cc'],
                $objectValues['recipients_bcc'],
                $objectValues['subject'],
                $objectValues['body'],
                $objectValues['attachments'],
                $objectValues['is_using_placholders'],
                $objectValues['should_save_in_sent_box']
            );
        }

        return $result;
    }
}
