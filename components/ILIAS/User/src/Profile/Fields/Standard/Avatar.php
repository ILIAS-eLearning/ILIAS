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

namespace ILIAS\User\Profile\Fields\Standard;

use ILIAS\User\Profile\Fields\NoOverrides;
use ILIAS\User\Profile\Fields\FieldDefinition;
use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\Language\Language;

class Avatar implements FieldDefinition
{
    use NoOverrides;

    public function getIdentifier(): string
    {
        return 'upload';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('personal_picture');
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::PersonalData;
    }

    public function hiddenInLists(): bool
    {
        return true;
    }

    public function visibleInCoursesForcedTo(): ?bool
    {
        return false;
    }

    public function visibleInGroupsForcedTo(): ?bool
    {
        return false;
    }

    public function visibleInStudyProgrammesForcedTo(): ?bool
    {
        return false;
    }

    public function requiredForcedTo(): ?bool
    {
        return false;
    }

    public function searchableForcedTo(): ?bool
    {
        return false;
    }

    public function availableInCertificatesForcedTo(): ?bool
    {
        return false;
    }

    public function getInput(
        Language $lng,
        ?\ilObjUser $current_user = null
    ): \ilFormPropertyGUI {
        $image_input = new \ilImageFileInputGUI($this->getLabel($lng));
        $image_input->setAllowCapture(true);

        if ($file_upload['name'] ?? false) {
            $image_input->setPending($file_upload['name']);
        } else {
            $picture_path = $this->retrieveValueFromUser($current_user);
            if ($picture_path !== '') {
                $image_input->setImage($picture_path);
                $image_input->setAlt($this->getLabel($lng));
            }
        }
        return $image_input;
    }

    public function addValueToUserObject(
        \ilObjUser $current_user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        $this->uploadUserPicture($current_user, $form);
        return $current_user;
    }

    public function retrieveValueFromUser(\ilObjUser $current_user): string
    {
        return \ilObjUser::_getPersonalPicturePath(
            $current_user->getId(),
            'small',
            true,
            true
        );
    }

    private function uploadUserPicture(
        \ilObjUser $current_user,
        \ilPropertyFormGUI $form
    ): void {
        if (!$form->hasFileUpload('userfile')
            && $this->profile_request->getUserFileCapture() === '') {
            if ($form->getItemByPostVar('userfile')->getDeletionFlag()) {
                $current_user->removeUserPicture();
            }
            return;
        }

        // User has uploaded a file of a captured image
        if (!$this->uploads->hasBeenProcessed()) {
            $this->uploads->process();
        }
        $existing_rid = $this->user->getAvatarRid();
        $revision_title = 'Avatar for user ' . $this->user->getLogin();

        // move uploaded file
        if ($form->hasFileUpload('userfile') && $this->uploads->hasBeenProcessed()) {
            $stream = Streams::ofResource(
                fopen(
                    $form->getFileUpload('userfile')['tmp_name'],
                    'r'
                )
            );

            if ($existing_rid === null) {
                $rid = $this->irss->manage()->stream(
                    $stream,
                    $this->stakeholder,
                    $revision_title
                );
            } else {
                $rid = $existing_rid;
                $this->irss->manage()->replaceWithStream(
                    $existing_rid,
                    $stream,
                    $this->stakeholder,
                    $revision_title
                );
            }

            if (!isset($rid)) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('upload_error_file_not_found'), true);
                $this->ctrl->redirect($this, 'showProfile');
            }
            $this->user->setAvatarRid($rid);
            $this->irss->flavours()->ensure($rid, new \ilUserProfilePictureDefinition()); // Create different sizes
            $current_user->update();
            return;
        }

        $capture = $this->profile_request->getUserFileCapture();
        if ($capture === null) {
            return;
        }

        $img = str_replace(
            ['data:image/png;base64,', ' '],
            ['', '+'],
            $capture
        );
        $data = base64_decode($img);
        if ($data === false) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('upload_error_file_not_found'), true);
            $this->ctrl->redirect($this, 'showProfile');
        }
        $stream = Streams::ofString($data);

        if ($existing_rid === null) {
            $rid = $this->irss->manage()->stream(
                $stream,
                $this->stakeholder,
                $revision_title
            );
        } else {
            $rid = $existing_rid;
            $this->irss->manage()->replaceWithStream(
                $rid,
                $stream,
                $this->stakeholder,
                $revision_title
            );
        }
        $current_user->setAvatarRid($rid);
        $this->irss->flavours()->ensure($rid, new \ilUserProfilePictureDefinition()); // Create different sizes
        $current_user->update();
    }

    private function tempStorePicture(): void
    {
        $capture = $this->profile_request->getUserFileCapture();

        if ($capture !== '') {
            $this->form->getItemByPostVar('userfile')->setImage($capture);
            $hidden_user_picture_carry = new \ilHiddenInputGUI('user_picture_carry');
            $hidden_user_picture_carry->setValue($capture);
            $this->form->addItem($hidden_user_picture_carry);
        }
    }
}
