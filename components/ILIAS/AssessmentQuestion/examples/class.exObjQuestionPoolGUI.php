<?php

/**
 * When a component consumes the assessment question service for purposes
 * of authoring and managing questions like the current question pool object,
 * it is neccessary to handle the following use cases.
 */
class exObjQuestionPoolGUI
{
    /**
     * The question creation and editing is handled by the the ilAsqQuestionAuthoring interface.
     * It is relevant for the request flow using the ILIAS control structure. This interface
     * will be implemented from the current question GUI classes in the future and provides
     * the executeCommand method that internally performs the different commands.
     *
     * To integrate the workflow of authoring questions to a consumer, the consumer's GUI class
     * must implement a suitable forward case in its own executeCommand method.
     *
     * Since the consumer MUST not know about any concrete question type's ilAsqQuestionAuthoring implementation
     * a dynamic switch-case expression is neccessary. The ilAsqService class provides such an expression
     * with a corresponding method. It returns the lowercase class name for the corresponding
     * question type authoring class or the interface name when the current next class in the control flow
     * does not relate to any question type authoring class.
     * An instance of the service class can be requested using $DIC->question()->service().
     */
    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        switch ($DIC->ctrl()->getNextClass($this)) {
            case $DIC->question()->service()->fetchNextAuthoringCommandClass($DIC->ctrl()->getNextClass()):

                $questionId = 0; // Fetch questionId from Request Parameters
                $backLink = ''; // Initialise with Back Link to Consumers Back-Landing Page

                $questionInstance = $DIC->question()->getQuestionInstance($questionId);
                $questionAuthoringGUI = $DIC->question()->getAuthoringCommandInstance($questionInstance);

                $questionAuthoringGUI->setBackLink($backLink);

                $DIC->ctrl()->forwardCommand($questionAuthoringGUI);
        }
    }

    /**
     * For question listings the ilAsqQuestionFactory provides a factory method to retrieve
     * an array of associative question data arrays. This structure can be simply used as
     * data structure for any ilTable2 implementation.
     */
    public function showQuestions()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $parentObjectId = 0; // init with question pool object id

        $questionDataArray = $DIC->question()->getQuestionDataArray($parentObjectId);

        /**
         * initialise any ilTable2GUI with this data array
         * render initialised ilTable2GUI
         */

        $tableGUI = new exQuestionsTableGUI($this, 'showQuestions', '');
        $tableGUI->setData($questionDataArray);

        $tableHTML = $tableGUI->getHTML(); // render table
    }

    /**
     * When a component provides import functionality for assessment questions, it needs to make use of the
     * ILIAS QTI service to get any qti xml parsed to an QTI object graph provided by the QTI service.
     *
     * To actually import the question as an assessment question the ilAsqQuestion interface method
     * fromQtiItem can be used. To retrieve an empty ilAsqQuestion instance, the question type of the
     * QtiItem needs to be determined.
     *
     * For the question type determination the ilAsqService class provides a corresponding method.
     * An instance of the service class can be requested using $DIC->question()->service().
     */
    public function importQuestions()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $parentObjectId = 0; // init with question pool object id

        /**
         * parse any qti import xml using the QTI Service and retrieve
         * an array containing ilQTIItem instances
         */
        $qtiItems = array(); /* @var ilQTIItem[] $qtiItems */

        foreach ($qtiItems as $qtiItem) {
            $questionType = $DIC->question()->service()->determineQuestionTypeByQtiItem($qtiItem);
            $questionInstance = $DIC->question()->getEmptyQuestionInstance($questionType);

            $questionInstance->fromQtiItem($qtiItem);
            $questionInstance->setParentId($parentObjectId);
            $questionInstance->save();
        }
    }

    /**
     * When a component provides export functionality for assessment questions, it needs the ilAsqQuestion
     * interface method toQtiXML to retrieve an qti item xml string. Since the QTI service does not support
     * to fetch an QTI xml string based on an QTI object graph, the current implementation of returning
     * the xml string itself will be kept within the toQtiXML interface method.
     *
     * To export one or more assessment questions the ilAsqFactory provides factory methods
     * to get single or multiple ilAsqQuestion instances.
     */
    public function exportQuestions()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $parentObjectId = 0; // init with question pool object id

        /**
         * get questions managed by this parent object
         */
        $questions = $DIC->question()->getQuestionInstances($parentObjectId);

        /**
         * build QTI xml string that will be used for any kind of export
         */

        $qtiXML = '';

        foreach ($questions as $questionInstance) {
            $qtiXML .= $questionInstance->toQtiXML();
        }
    }

    /**
     * For the deletion of questions ilAsqQuestion provides the interface method deleteQuestion.
     * The ilAsqFactory is to be used to get the ilAsqQuestion instance for any given questionId.
     * A simple call to deleteQuestion deletes the question and all its data.
     */
    public function deleteQuestion()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $questionId = 0; // init from GET parameters

        /**
         * use the ilAsqFactory to get an ilAsqQuestion instance
         * that supports the deletion process
         */

        $questionInstance = $DIC->question()->getQuestionInstance($questionId);
        $questionInstance->delete();
    }
}
