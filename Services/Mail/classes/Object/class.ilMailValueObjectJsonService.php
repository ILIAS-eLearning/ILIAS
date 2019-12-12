<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailValueObjectJsonService
{

    /**
     * @param array $mailValueObjects
     * @return string
     */
    public function convertToJson(array $mailValueObjects)
    {
        $mailArray = array();
        foreach ($mailValueObjects as $mailValueObject) {
            $array = array();

            $array['from']                    = $mailValueObject->getFrom();
            $array['recipients']              = $mailValueObject->getRecipients();
            $array['recipients_cc']           = $mailValueObject->getRecipientsCC();
            $array['recipients_bcc']          = $mailValueObject->getRecipientsBCC();
            $array['attachments']             = $mailValueObject->getAttachment();
            $array['body']                    = $mailValueObject->getBody();
            $array['subject']                 = $mailValueObject->getSubject();
            $array['is_using_placholders']    = $mailValueObject->isUsingPlaceholders();
            $array['should_save_in_sent_box'] = $mailValueObject->shouldSaveInSentBox();

            $mailArray[] = $array;
        }

        return json_encode($mailArray);
    }

    /**
     * @param string $json
     * @return ilMailValueObject[]
     */
    public function convertFromJson(string $json)
    {
        $result = array();
        $array = json_decode($json, true);

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
