<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event\AbstractConfiguration;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Scoring\AvailableScorings;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\Play\Editor\AvailableEditors;
use ILIAS\AssessmentQuestion\Play\Presenter\AvailablePresenters;

/**
 * Class QuestionPlayConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question
 *
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class QuestionPlayConfiguration extends AbstractValueObject {
	/**
	 * @var AbstractConfiguration
	 */
	protected $presenter_configuration;

	/**
	 * @var AbstractConfiguration
	 */
	protected $editor_configuration;

	/**
	 * @var AbstractConfiguration
	 */
	protected $scoring_configuration;


	/**
	 * @param AbstractConfiguration|null $editor_configuration
	 * @param AbstractConfiguration|null $presenter_configuration
	 * @param AbstractConfiguration|null $scoring_configuration
	 *
	 * @return QuestionPlayConfiguration
	 */
	public static function create(
	    AbstractConfiguration $editor_configuration = null,
		AbstractConfiguration $presenter_configuration = null,
		AbstractConfiguration $scoring_configuration = null
	) : QuestionPlayConfiguration {
		$object = new QuestionPlayConfiguration();
		$object->editor_configuration = $editor_configuration;
		$object->presenter_configuration = $presenter_configuration;
		$object->scoring_configuration = $scoring_configuration;
		return $object;
	}

	public static function getEditorClass(?QuestionPlayConfiguration $conf): string {
		if ($conf->editor_configuration !== null) {
			return $conf->editor_configuration->configurationFor();
		} else {
			return AvailableEditors::getDefaultEditor();
		}
	}

	public static function getPresenterClass(?QuestionPlayConfiguration $conf): string {
		if ($conf->presenter_configuration !== null) {
			return $conf->presenter_configuration->configurationFor();
		} else {
			return AvailablePresenters::getDefaultPresenter();
		}
	}

	public static function getScoringClass(?QuestionPlayConfiguration $conf): string {
		if ($conf->scoring_configuration !== null) {
			return $conf->scoring_configuration->configurationFor();
		} else {
			return AvailableScorings::getDefaultScoring();
		}
	}

	/**
	 * @return AbstractValueObject
	 */
	public function getEditorConfiguration(): ?AbstractConfiguration {
		return $this->editor_configuration;
	}


	/**
	 * @return AbstractValueObject
	 */
	public function getPresenterConfiguration(): ?AbstractConfiguration {
		return $this->presenter_configuration;
	}


	/**
	 * @return AbstractValueObject
	 */
	public function getScoringConfiguration(): ?AbstractConfiguration {
		return $this->scoring_configuration;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject::equals()
	 */
    public function equals(AbstractValueObject $other): bool
    {
        return AbstractValueObject::isNullableEqual(
        	        $this->getEditorConfiguration(),
	                $other->getEditorConfiguration()) &&
               AbstractValueObject::isNullableEqual(
               	    $this->getPresenterConfiguration(),
                    $other->getPresenterConfiguration()) &&
               AbstractValueObject::isNullableEqual(
               	    $this->getScoringConfiguration(),
                    $other->getScoringConfiguration());
    }
}