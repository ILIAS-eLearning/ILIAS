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

class ilMailValueObjectJsonService
{
    /**
     * @param list<ilMailValueObject> $mail_value_objects
     */
    public function convertToJson(array $mail_value_objects): string
    {
        $records = [];
        foreach ($mail_value_objects as $mail_value_object) {
            $mail_data = [];
            $mail_data['from'] = $mail_value_object->getFrom();
            $mail_data['recipients'] = $mail_value_object->getRecipients();
            $mail_data['recipients_cc'] = $mail_value_object->getRecipientsCC();
            $mail_data['recipients_bcc'] = $mail_value_object->getRecipientsBCC();
            $mail_data['attachments'] = $mail_value_object->getAttachments();
            $mail_data['body'] = $mail_value_object->getBody();
            $mail_data['subject'] = $mail_value_object->getSubject();
            $mail_data['is_using_placholders'] = $mail_value_object->isUsingPlaceholders();
            $mail_data['should_save_in_sent_box'] = $mail_value_object->shouldSaveInSentBox();

            $records[] = $mail_data;
        }

        return json_encode($records, JSON_THROW_ON_ERROR);
    }

    /**
     * @return list<ilMailValueObject>
     */
    public function convertFromJson(string $json): array
    {
        $result = [];
        $array = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        foreach ($array as $object_values) {
            $result[] = new ilMailValueObject(
                $object_values['from'],
                $object_values['recipients'],
                $object_values['recipients_cc'],
                $object_values['recipients_bcc'],
                ilStr::strLen($object_values['subject']) > 255 ? ilStr::substr($object_values['subject'], 0, 255) : $object_values['subject'],
                $object_values['body'],
                $object_values['attachments'],
                $object_values['is_using_placholders'],
                $object_values['should_save_in_sent_box']
            );
        }

        return $result;
    }
}
