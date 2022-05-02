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
 * Class ilForumNotificationEventsFormGUI
 */
class ilForumNotificationEventsFormGUI extends ilPropertyFormGUI
{
    protected object $parent_object;
    protected int $ref_id;
    protected int $thread_id = 0;

    public function __construct(object $parent_object, int $ref_id, int $thread_id = 0)
    {
        $this->parent_object = $parent_object;
        $this->ref_id = $ref_id;
        $this->thread_id = $thread_id;

        parent::__construct();

        $this->initForm();
    }

    private function initForm() : void
    {
        $this->setId(uniqid('frm_ntf_set_' . $this->ref_id, true));

        if ($this->thread_id > 0) {
            $this->ctrl->setParameter($this->parent_object, 'thr_pk', $this->thread_id);
        }

        $this->setFormAction($this->ctrl->getFormAction($this->parent_object, 'saveUserNotificationSettings'));

        $notify_modified = new ilCheckboxInputGUI($this->lng->txt('notify_modified'), 'notify_modified');
        $notify_modified->setValue((string) ilForumNotificationEvents::UPDATED);
        $this->addItem($notify_modified);

        $notify_censored = new ilCheckboxInputGUI($this->lng->txt('notify_censored'), 'notify_censored');
        $notify_censored->setValue((string) ilForumNotificationEvents::CENSORED);
        $this->addItem($notify_censored);

        $notify_uncensored = new ilCheckboxInputGUI($this->lng->txt('notify_uncensored'), 'notify_uncensored');
        $notify_uncensored->setValue((string) ilForumNotificationEvents::UNCENSORED);
        $this->addItem($notify_uncensored);

        $notify_post_deleted = new ilCheckboxInputGUI(
            $this->lng->txt('notify_post_deleted'),
            'notify_post_deleted'
        );
        $notify_post_deleted->setValue((string) ilForumNotificationEvents::POST_DELETED);
        $this->addItem($notify_post_deleted);

        $notify_thread_deleted = new ilCheckboxInputGUI(
            $this->lng->txt('notify_thread_deleted'),
            'notify_thread_deleted'
        );
        $notify_thread_deleted->setValue((string) ilForumNotificationEvents::THREAD_DELETED);
        $this->addItem($notify_thread_deleted);

        $hidden_value = new ilHiddenInputGUI('hidden_value');
        $this->addItem($hidden_value);
    }
}
