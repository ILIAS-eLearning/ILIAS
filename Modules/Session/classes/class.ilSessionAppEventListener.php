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
 ********************************************************************
 */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilSessionAppEventListener implements ilAppEventListener
{
    protected ilDBInterface $database;
    protected ilObjectDataCache $objectDataCache;
    protected ilLogger $logger;
    protected string $component = "";
    protected string $event = "";
    protected array $parameters = [];

    public function __construct(
        \ilDBInterface $db,
        \ilObjectDataCache $objectDataCache,
        \ilLogger $logger
    ) {
        $this->database = $db;
        $this->objectDataCache = $objectDataCache;
        $this->logger = $logger;
    }

    public function withComponent(string $component) : ilSessionAppEventListener
    {
        $clone = clone $this;

        $clone->component = $component;

        return $clone;
    }

    public function withEvent(string $event) : ilSessionAppEventListener
    {
        $clone = clone $this;

        $clone->event = $event;

        return $clone;
    }

    public function withParameters(array $parameters) : ilSessionAppEventListener
    {
        $clone = clone $this;

        $clone->parameters = $parameters;

        return $clone;
    }

    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        global $DIC;

        $listener = new static(
            $DIC->database(),
            $DIC['ilObjDataCache'],
            $DIC->logger()->root()
        );

        $listener
            ->withComponent($a_component)
            ->withEvent($a_event)
            ->withParameters($a_parameter)
            ->handle();
    }

    public function handle() : void
    {
        if ('Modules/Session' !== $this->component) {
            return;
        }

        try {
            if ('register' === $this->event) {
                $this->handleRegisterEvent();
            } elseif ('enter' === $this->event) {
                $this->handleEnteredEvent();
            } elseif ('unregister' === $this->event) {
                $this->handleUnregisterEvent();
            }
        } catch (\ilException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function handleRegisterEvent() : void
    {
        $type = ilSessionMembershipMailNotification::TYPE_REGISTER_NOTIFICATION;

        $this->sendMail($type);
    }

    private function handleEnteredEvent() : void
    {
        $type = ilSessionMembershipMailNotification::TYPE_ENTER_NOTIFICATION;

        $this->sendMail($type);
    }

    private function handleUnregisterEvent() : void
    {
        $type = ilSessionMembershipMailNotification::TYPE_UNREGISTER_NOTIFICATION;

        $this->sendMail($type);
    }

    private function fetchRecipientParticipants() : array
    {
        $object = new ilEventParticipants((int) $this->parameters['obj_id']);

        $recipients = [];
        $participants = $object->getParticipants();
        foreach ($participants as $id => $participant) {
            if ($participant['notification_enabled'] === true) {
                $recipients[] = $id;
            }
        }

        return $recipients;
    }

    private function sendMail(int $type) : void
    {
        $recipients = $this->fetchRecipientParticipants();
        if (!empty($recipients)) {
            $notification = new ilSessionMembershipMailNotification();
            $notification->setRecipients($recipients);
            $notification->setType($type);
            $notification->setRefId($this->parameters['ref_id']);

            $notification->send((int) $this->parameters['usr_id']);
        }
    }
}
