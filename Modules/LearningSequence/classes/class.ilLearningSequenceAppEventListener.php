<?php

declare(strict_types=1);

/**
 * EventListener for LSO
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */

class ilLearningSequenceAppEventListener
{
    public static function handleEvent($component, $event, $parameter)
    {
        switch ($component) {
            case "Services/Tracking":
                switch ($event) {
                    case "updateStatus":
                        self::onServiceTrackingUpdateStatus($parameter);
                        break;
                }
                break;
            case "Services/Object":
                switch ($event) {
                    case "beforeDeletion":
                        self::onObjectDeletion($parameter);
                        break;
                    case "toTrash":
                        self::onObjectToTrash($parameter);
                        break;
                }
                break;

            case "Modules/LearningSequence":
                switch ($event) {
                    case "deleteParticipant":
                        self::onParticipantDeletion($parameter);
                        break;
                    case "addParticipant":
                    default:
                        break;
                }
                break;

            default:
                throw new ilException(
                    "ilLearningSequenceAppEventListener::handleEvent: " .
                    "Won't handle events of '$component'."
                );
        }
    }

    private static function onServiceTrackingUpdateStatus(array $parameter)
    {
        $handler = new ilLSLPEventHandler(self::getIlTree(), self::getIlLPStatusWrapper());
        $handler->updateLPForChildEvent($parameter);
    }

    private static function onObjectDeletion(array $parameter)
    {
        $handler = self::getLSEventHandler();
        $handler->handleObjectDeletion($parameter);
    }

    private static function onObjectToTrash(array $parameter)
    {
        $handler = self::getLSEventHandler();
        $handler->handleObjectToTrash($parameter);
    }

    private static function onParticipantDeletion(array $parameter)
    {
        $handler = self::getLSEventHandler();
        $obj_id = (int) $parameter['obj_id'];
        $usr_id = (int) $parameter['usr_id'];

        $handler->handleParticipantDeletion($obj_id, $usr_id);
    }

    protected static function getLSEventHandler() : ilLSEventHandler
    {
        return new ilLSEventHandler(self::getIlTree());
    }

    protected static function getIlTree() : ilTree
    {
        global $DIC;
        return $DIC['tree'];
    }

    protected static function getIlLPStatusWrapper() : ilLPStatusWrapper
    {
        return new ilLPStatusWrapper();
    }
}
