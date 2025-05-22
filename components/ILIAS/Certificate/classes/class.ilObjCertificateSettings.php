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

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Certificate\CertificateResourceHandler;
use ILIAS\ResourceStorage\Services as ResourceStorage;
use ILIAS\Certificate\File\ilCertificateTemplateStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Filesystem\Filesystem;

/**
 * Class ilObjCertificateSettings
 * @author  Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ServicesCertificate
 */
class ilObjCertificateSettings extends ilObject
{
    private readonly ilSetting $certificate_settings;
    private readonly ResourceStorage $irss;
    private readonly Filesystem $filesystem;
    private readonly ilCertificateTemplateStakeholder $stakeholder;
    private readonly CertificateResourceHandler $resource_handler;

    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        global $DIC;

        parent::__construct($a_id, $a_reference);
        $this->type = 'cert';
        $this->certificate_settings = new ilSetting('certificate');
        $this->irss = $DIC->resourceStorage();
        $this->filesystem = $DIC->filesystem()->web();
        $this->stakeholder = new ilCertificateTemplateStakeholder();
        $this->resource_handler = new CertificateResourceHandler(
            new ilUserCertificateRepository($DIC->database()),
            new ilCertificateTemplateDatabaseRepository($DIC->database()),
            $this->irss,
            $this,
            $this->stakeholder
        );
    }

    public function getBackgroundImageIdentification(): ResourceIdentification|string|null
    {
        $id = $this->certificate_settings->get('cert_bg_image', '');

        if ($rid = $this->irss->manage()->find($id)) {
            return $rid;
        }
        if ($id !== '') {
            $id = $this->getBackgroundImageDefaultFolder() . $id;
        }
        if ($id !== '' && $this->filesystem->has($id)) {
            return ilWACSignedPath::signFile(ILIAS_HTTP_PATH . '/' . ILIAS_WEB_DIR . '/' . CLIENT_ID . $id);
        }
        return null;
    }

    public function getBackgroundImageDefaultFolder(): string
    {
        return '/certificates/default/';
    }

    /**
     * Uploads a background image for the certificate. Creates a new directory for the
     * certificate if needed. Removes an existing certificate image if necessary
     * @return bool        True on success, otherwise false
     * @throws ilException
     */
    public function uploadBackgroundImage(UploadResult $upload_result): bool
    {
        $identification = $this->irss->manage()->upload($upload_result, $this->stakeholder);
        $this->certificate_settings->set('cert_bg_image', $identification->serialize());

        return $identification->serialize() !== '';
    }

    public function deleteBackgroundImage(): bool
    {
        $rid = $this->getBackgroundImageIdentification();
        if ($rid instanceof ResourceIdentification) {
            $this->certificate_settings->set('cert_bg_image', '');
            $this->resource_handler->handleResourceChange($rid);

            return true;
        }

        return false;
    }

    public function hasBackgroundImage(): bool
    {
        return (bool) $this->certificate_settings->get('cert_bg_image', '');
    }
}
