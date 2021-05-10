<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailValueObjectJsonService
{

    /**
     * @param ilMailValueObject[] $mailValueObjects
     * @return string
     */
    public function convertToJson(array $mailValueObjects) : string
    {
        $mailArray = [];
        foreach ($mailValueObjects as $mailValueObject) {
            $array = [];

            $array['actor_usr_id'] = $mailValueObject->getActorUsrId();
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

        return json_encode($mailArray);
    }

    /**
     * @param string $json
     * @return ilMailValueObject[]
     */
    public function convertFromJson(string $json) : array
    {
        $result = [];
        $array = json_decode($json, true);

        foreach ($array as $objectValues) {
            $actorId = 0;
            if (isset($objectValues['actor_usr_id'])) {
                $actorId = (int) $objectValues['actor_usr_id'];
            }

            $result[] = new ilMailValueObject(
                $actorId,
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
