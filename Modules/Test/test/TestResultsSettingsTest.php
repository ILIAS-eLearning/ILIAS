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

namespace ILIAS\Modules\Test\test;

use PHPUnit\Framework\TestCase;

class TestResultsSettingsTest extends TestCase
{
    public function testTestResultsSettingsDefaults(): void
    {
        $trs = new \ilTestResultsSettings();
        $this->assertFalse($trs->getShowHiddenQuestions());
        $this->assertFalse($trs->getShowOptionalQuestions());
        $this->assertTrue($trs->getShowBestSolution());
        $this->assertTrue($trs->getShowFeedback());
    }

    public function testTestResultsSettingsBasicProps(): void
    {
        $trs = new \ilTestResultsSettings();
        $this->assertTrue($trs->withShowHiddenQuestions(true)->getShowHiddenQuestions());
        $this->assertFalse($trs->withShowHiddenQuestions(false)->getShowHiddenQuestions());
        $this->assertTrue($trs->withShowOptionalQuestions(true)->getShowOptionalQuestions());
        $this->assertFalse($trs->withShowOptionalQuestions(false)->getShowOptionalQuestions());
        $this->assertTrue($trs->withShowBestSolution(true)->getShowBestSolution());
        $this->assertFalse($trs->withShowBestSolution(false)->getShowBestSolution());
        $this->assertTrue($trs->withShowFeedback(true)->getShowFeedback());
        $this->assertFalse($trs->withShowFeedback(false)->getShowFeedback());
    }
}
