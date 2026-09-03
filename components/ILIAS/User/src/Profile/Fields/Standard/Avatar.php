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

use ILIAS\User\Context;
use ILIAS\User\Profile\Fields\NoOverrides;
use ILIAS\User\Profile\Fields\FieldDefinition;
use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Stream\Stream;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;

class Avatar implements FieldDefinition
{
    use NoOverrides;

    private ResourceStakeholder $stakeholder;

    public function __construct(
        private readonly IRSS $irss,
        private readonly FileUpload $uploads,
        private readonly ArrayBasedRequestWrapper $post_wrapper,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery
    ) {
        $this->stakeholder = new \ilUserProfilePictureStakeholder();
    }

    public function getIdentifier(): string
    {
        return 'avatar';
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

    public function getLegacyInput(
        Language $lng,
        Context $context,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $input = new \ilImageFileInputGUI($this->getLabel($lng));
        $input->setAllowCapture(
            $context === Context::User || $context === Context::Registration
        );

        if (($_FILES[$this->getIdentifier()]['tmp_name'] ?? '') !== '') {
            $input->setPending($_FILES[$this->getIdentifier()]['tmp_name']);
            return $input;
        }

        if ($user === null) {
            return $input;
        }

        $picture_path = $user->getPersonalPicturePath('small', true);
        if ($picture_path !== '') {
            $input->setImage($picture_path);
            $input->setAlt($this->getLabel($lng));
        }
        return $input;
    }

    public function addValueToUserObject(
        \ilObjUser $user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        return $this->uploadUserPicture($user, $input, $form);
    }

    public function retrieveValueFromUser(\ilObjUser $user): string
    {

        if ($user->getAvatarRid() === null) {
            return '';
        }
        $define = new \ilUserAvatarResolver($user->getId());
        $define->setSize('xsmall');
        $define->setForcePicture(true);
        return $define->getLegacyPictureURL();
    }

    /**
     * @deprecated since version 11 will be removed asap
     */
    public function tempStorePicture(
        \ilPropertyFormGUI $form
    ): \ilPropertyFormGUI {
        $capture = $this->retrieveCapture();
        if ($capture === '') {
            return $form;
        }
        $form->getItemByPostVar($this->getIdentifier())->setImage($capture);
        $hidden_user_picture_carry = new \ilHiddenInputGUI('user_picture_carry');
        $hidden_user_picture_carry->setValue($capture);
        $form->addItem($hidden_user_picture_carry);
        return $form;
    }

    private function uploadUserPicture(
        \ilObjUser $user,
        array $input,
        ?\ilPropertyFormGUI $form
    ): \ilObjUser {
        $capture = $this->retrieveCapture();
        if ($input['tmp_name'] === '' && $capture === '') {
            if ($form?->getItemByPostVar($this->getIdentifier())->getDeletionFlag()) {
                $user->removeUserPicture();
            }
            return $user;
        }

        // User has uploaded a file of a captured image
        if (!$this->uploads->hasBeenProcessed()) {
            $this->uploads->process();
        }

        $existing_rid = $user->getAvatarRid();
        $revision_title = 'Avatar for user ' . ($input['alias'] ?? $user->getLogin());
        $this->stakeholder->setOwner($user->getId());
        $uploads = $this->uploads->getResults();

        if (is_file($input['tmp_name'])) {
            $rid = $this->moveStreamToStorage(
                $existing_rid,
                $revision_title,
                Streams::ofString(
                    file_get_contents($input['tmp_name'])
                )
            );
            $user->setAvatarRid($rid);
            $this->irss->flavours()->ensure($rid, new \ilUserProfilePictureDefinition());
            return $user;
        }

        if (isset($uploads[$input['tmp_name']])) {
            $rid = $this->moveUploadToStorage(
                $existing_rid,
                $revision_title,
                $uploads[$input['tmp_name']]
            );
            $user->setAvatarRid($rid);
            $this->irss->flavours()->ensure($rid, new \ilUserProfilePictureDefinition());
            return $user;
        }

        if ($capture === '') {
            return $user;
        }

        $data = base64_decode(
            str_replace(
                ['data:image/png;base64,', ' '],
                ['', '+'],
                $capture
            )
        );
        if ($data === false) {
            return $user;
        }
        $rid = $this->moveStreamToStorage(
            $existing_rid,
            $revision_title,
            Streams::ofString($data)
        );
        $user->setAvatarRid($rid);
        $this->irss->flavours()->ensure($rid, new \ilUserProfilePictureDefinition());
        return $user;
    }

    private function moveUploadToStorage(
        ?ResourceIdentification $existing_rid,
        string $revision_title,
        UploadResult $upload_result
    ): ResourceIdentification {
        if ($existing_rid === null) {
            return $this->irss->manage()->upload(
                $upload_result,
                $this->stakeholder,
                $revision_title
            );
        }

        $this->irss->manage()->replaceWithUpload(
            $existing_rid,
            $upload_result,
            $this->stakeholder,
            $revision_title
        );

        return $existing_rid;
    }

    private function moveStreamToStorage(
        ?ResourceIdentification $existing_rid,
        string $revision_title,
        Stream $stream
    ): ResourceIdentification {
        if ($existing_rid === null) {
            return $this->irss->manage()->stream(
                $stream,
                $this->stakeholder,
                $revision_title
            );
        }

        $this->irss->manage()->replaceWithStream(
            $existing_rid,
            $stream,
            $this->stakeholder,
            $revision_title
        );
        return $existing_rid;
    }

    private function retrieveCapture(): ?string
    {
        $from_upload = $this->post_wrapper->retrieve(
            'avatar_capture',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );

        if ($from_upload !== '') {
            return $from_upload;
        }

        return $this->post_wrapper->retrieve(
            'user_picture_carry',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );
    }
}
