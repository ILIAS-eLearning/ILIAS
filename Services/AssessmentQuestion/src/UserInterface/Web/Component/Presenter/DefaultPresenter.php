<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Presenter;

use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\AbstractEditor;
use ilTemplate;

/**
 * Class DefaultPresenter
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class DefaultPresenter extends AbstractPresenter {

    /**
     * @return string
     * @throws \ilTemplateException
     */
	public function generateHtml(AbstractEditor $editor): string {
		$tpl = new ilTemplate("tpl.DefaultPresenter.html", true, true, "Services/AssessmentQuestion");

		$tpl->setCurrentBlock('question');
		$tpl->setVariable('QUESTIONTEXT', $this->question->getData()->getQuestionText());
		$tpl->setVariable('EDITOR', $editor->generateHtml());
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}
}