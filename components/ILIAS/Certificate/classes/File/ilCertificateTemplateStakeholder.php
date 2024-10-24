<?php

declare(strict_types=1);

namespace ILIAS\Certificate\File;

use ilException;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

class ilCertificateTemplateStakeholder extends AbstractResourceStakeholder
{
    public function __construct(protected int $obj_id = 0)
    {
    }

    public function getId(): string
    {
        return 'cert_template';
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->obj_id;
    }
}
