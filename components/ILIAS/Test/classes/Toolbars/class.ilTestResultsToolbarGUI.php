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

/**
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 */
class ilTestResultsToolbarGUI extends ilToolbarGUI
{
    public ilCtrl $ctrl;
    public ilGlobalTemplateInterface $tpl;

    private ?string $certificateLinkTarget = null;
    private ?string $showBestSolutionsLinkTarget = null;
    private ?string $hideBestSolutionsLinkTarget = null;
    private array $participantSelectorOptions = [];

    public function __construct(ilCtrl $ctrl, ilGlobalTemplateInterface $tpl, ilLanguage $lng)
    {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        parent::__construct();
    }

    public function build(): void
    {
        $this->setId('tst_results_toolbar');

        $print_button = $this->ui->factory()->button()->standard($this->lng->txt('print'), '')
            ->withOnLoadCode(fn($id) => "$('#$id').on('click', ()=>{window.print();})");
        $this->addComponent($print_button);

        if ($this->getCertificateLinkTarget() !== null
            && $this->getCertificateLinkTarget() !== '') {
            $this->addButton($this->lng->txt('certificate'), $this->getCertificateLinkTarget());
        }

        if ($this->getShowBestSolutionsLinkTarget() !== null
            && $this->getShowBestSolutionsLinkTarget() !== '') {
            $this->addSeparator();
            $this->addButton(
                $this->lng->txt('tst_btn_show_best_solutions'),
                $this->getShowBestSolutionsLinkTarget()
            );
        } elseif ($this->getHideBestSolutionsLinkTarget() !== null
            && $this->getHideBestSolutionsLinkTarget() !== '') {
            $this->addSeparator();
            $this->addButton(
                $this->lng->txt('tst_btn_hide_best_solutions'),
                $this->getHideBestSolutionsLinkTarget()
            );
        }

        if (count($this->getParticipantSelectorOptions())) {
            $this->addSeparator();

            $dropdown = $this->ui->factory()->dropdown()
                ->standard($this->getParticipantSelectorLinksArray())
                ->withLabel($this->lng->txt('tst_res_jump_to_participant_hint_opt'));
            $this->addComponent($dropdown);
        }
    }

    public function setCertificateLinkTarget(string $certificateLinkTarget): void
    {
        $this->certificateLinkTarget = $certificateLinkTarget;
    }

    public function getCertificateLinkTarget(): ?string
    {
        return $this->certificateLinkTarget;
    }

    public function setShowBestSolutionsLinkTarget(string $showBestSolutionsLinkTarget): void
    {
        $this->showBestSolutionsLinkTarget = $showBestSolutionsLinkTarget;
    }

    public function getShowBestSolutionsLinkTarget(): ?string
    {
        return $this->showBestSolutionsLinkTarget;
    }

    public function setHideBestSolutionsLinkTarget(string $hideBestSolutionsLinkTarget): void
    {
        $this->hideBestSolutionsLinkTarget = $hideBestSolutionsLinkTarget;
    }

    public function getHideBestSolutionsLinkTarget(): ?string
    {
        return $this->hideBestSolutionsLinkTarget;
    }

    public function setParticipantSelectorOptions(array $participantSelectorOptions): void
    {
        $this->participantSelectorOptions = $participantSelectorOptions;
    }

    public function getParticipantSelectorOptions(): array
    {
        return $this->participantSelectorOptions;
    }

    public function getParticipantSelectorLinksArray(): array
    {
        $options = [];
        foreach ($this->getParticipantSelectorOptions() as $key => $val) {
            $options[] = $this->ui->factory()->link()->standard($val, "#participant_active_{$key}");
        }

        return $options;
    }
}
