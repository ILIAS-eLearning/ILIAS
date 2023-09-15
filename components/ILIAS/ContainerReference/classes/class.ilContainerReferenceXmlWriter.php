<?php

declare(strict_types=1);

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
 * Class for container reference export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilContainerReferenceXmlWriter extends ilXmlWriter
{
    public const MODE_SOAP = 1;
    public const MODE_EXPORT = 2;

    protected ilSetting $settings;

    private int $mode = self::MODE_SOAP;
    private ?ilContainerReference $ref;

    public function __construct(ilContainerReference $ref = null)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        parent::__construct();
        $this->ref = $ref;
    }

    public function setMode(int $a_mode): void
    {
        $this->mode = $a_mode;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function getReference(): ?ilContainerReference
    {
        return $this->ref;
    }

    public function export(bool $a_with_header = true): void
    {
        if ($this->getMode() === self::MODE_EXPORT) {
            if ($a_with_header) {
                $this->buildHeader();
            }
            $this->buildReference();
            $this->buildTarget();
            $this->buildTitle();
            $this->buildFooter();
        }
    }

    public function getXml(): string
    {
        return $this->xmlDumpMem(false);
    }

    protected function buildHeader(): void
    {
        $ilSetting = $this->settings;

        $this->xmlSetDtdDef("<!DOCTYPE container reference PUBLIC \"-//ILIAS//DTD Group//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_container_reference_4_3.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS container reference " . $this->getReference()->getId() . " of installation " . $ilSetting->get('inst_id') . ".");
        $this->xmlHeader();
    }

    protected function buildTarget(): void
    {
        $this->xmlElement('Target', ['id' => $this->getReference()->getTargetId()]);
    }

    protected function buildTitle(): void
    {
        $title = '';
        if ($this->getReference()->getTitleType() === ilContainerReference::TITLE_TYPE_CUSTOM) {
            $title = $this->getReference()->getTitle();
        }

        $this->xmlElement(
            'Title',
            [
                    'type' => $this->getReference()->getTitleType()
            ],
            $title
        );
    }

    protected function buildReference(): void
    {
        $this->xmlStartTag('ContainerReference');
    }

    protected function buildFooter(): void
    {
        $this->xmlEndTag('ContainerReference');
    }
}
