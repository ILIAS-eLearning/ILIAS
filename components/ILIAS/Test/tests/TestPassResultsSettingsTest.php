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

namespace ILIAS\components\Test\test;

use PHPUnit\Framework\TestCase;

class TestPassResultsSettingsTest extends TestCase
{
    public function testTestResultsSettingsDefaults(): void
    {
        $trs = new \ilTestPassResultsSettings();
        $this->assertFalse($trs->getShowHiddenQuestions());
        $this->assertFalse($trs->getShowOptionalQuestions());
        $this->assertTrue($trs->getShowBestSolution());
        $this->assertTrue($trs->getShowFeedback());
        $this->assertFalse($trs->getQuestionTextOnly());
        $this->assertFalse($trs->getShowRecapitulation());
    }

    public function testTestResultsSettingsBasicProps(): void
    {
        $trs = new \ilTestPassResultsSettings(true, true, true, true, true, true);
        $this->assertTrue($trs->getShowHiddenQuestions());
        $this->assertTrue($trs->getShowOptionalQuestions());
        $this->assertTrue($trs->getShowBestSolution());
        $this->assertTrue($trs->getShowFeedback());
        $this->assertTrue($trs->getQuestionTextOnly());
        $this->assertTrue($trs->getShowRecapitulation());

        $trs = new \ilTestPassResultsSettings(false, false, false, false, false, false);
        $this->assertFalse($trs->getShowHiddenQuestions());
        $this->assertFalse($trs->getShowOptionalQuestions());
        $this->assertFalse($trs->getShowBestSolution());
        $this->assertFalse($trs->getShowFeedback());
        $this->assertFalse($trs->getQuestionTextOnly());
        $this->assertFalse($trs->getShowRecapitulation());
    }
}
