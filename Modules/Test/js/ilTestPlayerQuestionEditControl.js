/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/* fau: testNav - new script for test player control of editable questions. */

/**
 * Test Player Control for Editable Questions
 * added and initialized by ilTestPlayerAbstractGUI::populateQuestionEditControl()
 */
il.TestPlayerQuestionEditControl = new function() {

    /**
     * self reference for inner functions
     */
    var self = this;


    /**
     * @const   string                  jquery selector for the question form
     */
    var FORM_SELECTOR = '#taForm';

    /**
     * @const   integer                 period (ms) for detecting form changes
     */
    var FORM_DETECTOR_PERIOD = 100;

    /**
     * @const   integer                 period (ms) for detecting background changes
     */
    var BACKGROUND_DETECTOR_PERIOD = 1000;


    /**
     * @const                           delay (ms) for starting the timers (form detection, auto save etc.)
     *                                  should be long enough for question initialisation
     *                                  should be shorter than a possible manual interaction
     */
    var START_TIMERS_DELAY = 100;

    /**
     * @var object config               initial configuration
     */
    var config = {
        isAnswered: false,                      // question is already answered
        isAnswerChanged: false,                 // question is already changed, e.g. after marking
        saveOnTimeReachedUrl: '',               // url for save at nd of working time
        autosaveUrl: '',                        // url for saving of intermediate solutions
        autosaveInterval: 0,                    // interval for saving of intermediate solutions
        withFormChangeDetection: true,          // form changes should be detected
        withBackgroundChangeDetection: false,   // background changes should be polled from ILIAS
        backgroundDetectorUrl: '',              // url called by the background detector
        forcedInstantFeedback: false,            // forced feedback will change the submit command
        nextQuestionLocks: false
    };

    /**
     * @var boolean answered            the displayed question is answered
     */
    var answered = false;

    /**
     * @var boolean answerChanged       the displayed answer is changed
     */
    var answerChanged = false;

    /**
     * @var boolean stickyChanged       the changed status is sticky
     */
    var stickyChanged = false;

    /**
     * @var string  origData            original form data
     */
    var origData = '';

    /**
     * @var string  autoSavedData       form data of last autosave
     */
    var autoSavedData = '';

    /**
     * @var int     formDetector        timer id of the form changes detector
     */
    var formDetector = 0;

    /**
     * @var int     backgroundDetector  timer id of the background changes detector
     */
    var backgroundDetector = 0;

    /**
     * @var int     autoSaver           timer id of the auto saver
     */
    var autoSaver = 0;

    /**
     * @var string  revertUrl           url to revert the answer changes
     */
    var revertUrl = '';

    /**
     * @var string  navUrl              url of the last clicked navigation link
     */
    var navUrl = '';


    /**
	 * Initialize the Control
     * @public
	 */
	this.init = function(configParam) {

	    // make sure users do not change answers until we are set up properly
        $('body').css('pointer-events', 'none');
        $(document).keydown(function(e) {
            if (origData === '') { // not initialized yet?
                e.preventDefault();
            }
        });

        // save the configuration parameters provided by ILIAS
        config = configParam;

        // set the initial answered status of the question
        answered = config.isAnswered;

        // keep the changed status of a question after file upload, marking etc.
        if (config.isAnswerChanged) {
            answerChanged = true;
            stickyChanged = true;
        }

        // adjust the display of status dependent elements
        refreshAnswerStatusView();

        // check for changed answer when user wants to navigate
        // this creates a form submit with hidden redirection url
        $('a').click(checkNavigation);

        // add the current answering status when form is submitted
        // this is needed for marking questions and requesting hints
        $(FORM_SELECTOR).submit(handleFormSubmit);

        // handle the buttons in the navigation confirmation modal
        $('#tst_save_on_navigation_button').click(saveWithNavigation);
        $('#tst_cancel_on_navigation_button').click(hideNavigationModal);

        // handle the actions for the discard solution modal
        // the final action is done by a submit button in the modal
        $('#tst_discard_solution_action').click(showDiscardSolutionModal);
        $('#tst_cancel_discard_button').click(hideDiscardSolutionModal);
        
        if( config.nextQuestionLocks )
        {
            // handle the buttons in next locks current answer confirmation modal
            $('#tst_nav_next_changed_answer_button').click(saveWithNavigation);
            $('#tst_cancel_next_changed_answer_button').click(hideFollowupQuestionLocksCurrentAnswerModal);
            
            // handle the buttons in next locks empty answer confirmation modal
            $('#tst_nav_next_empty_answer_button').click(saveWithNavigationEmptyAnswer);
            $('#tst_cancel_next_empty_answer_button').click(hideFollowupQuestionLocksEmptyAnswerModal);
        }

        // the checkbox 'use unchanged answer' is only needed for initial empty answers
        // it exists for few question types only
        if (config.isAnswered || config.isAnswerChanged) {
            $('#ilQuestionForceFormDiffInput').hide();
        }

        // Delayed start of timer functions
        // This gives question scripts some time to initialize
        setTimeout(startTimers, START_TIMERS_DELAY);

    };

    /**
     * Set the answer being changed and stick this
     * This is a public function for plugin question types
     * @public
     */
    this.stickAnswerChanged = function() {
        stopDetection();
        answerChanged = true;
        stickyChanged = true;
        refreshAnswerStatusView();
    };

    /**
     * Set the answer being unchanged and stick this
     * This is a public function for plugin question types
     * @public
     */
    this.stickAnswerUnchanged = function() {
        stopDetection();
        answerChanged = false;
        stickyChanged = true;
        refreshAnswerStatusView();
    };

    /**
     * Save the form when the working time of the test is reached
     * @public
     */
    this.saveOnTimeReached = function () {

        // change status will be added by handleFormSubmit()
        $(FORM_SELECTOR).attr('action', config.saveOnTimeReachedUrl).submit();
    };

    /**
     * Delayed start of timer functions (change detection, auto save)
     * This gives question scripts some time for their initialisation
     */
    function startTimers() {

        // restore tinyMCE reformated content to its textarea
        // before remembering origina formdata
        if (typeof tinyMCE != 'undefined') {
            tinyMCE.triggerSave();
        }

        // save the initial form status
        origData = $(FORM_SELECTOR).serialize();
        autoSavedData = origData;

        // Start the periodic detection of form changes
        if (config.withFormChangeDetection) {
            formDetector = setInterval(detectFormChange, FORM_DETECTOR_PERIOD);
        }

        // Start the periodic detection of form changes
        if (config.withBackgroundChangeDetection) {
            backgroundDetector = setInterval(detectBackgroundChanges, BACKGROUND_DETECTOR_PERIOD);
        }

        // activate the autosave function if required
        if (config.autosaveUrl != '' && config.autosaveInterval > 0) {
            autoSaver = setInterval(autoSave, config.autosaveInterval);
        }

        // activate the handler for the save button in rich text areas
        if (typeof tinyMCE != 'undefined') {
            activateMceSaveHandler();
        }

        // we are set up now, user may change answer now.
        $('body').css('pointer-events', '');
    }

    /**
     * Stop the detection of changes
     */
    function stopDetection() {
        if (formDetector) {
            clearInterval(formDetector);
            formDetector = 0;
        }

        if (backgroundDetector) {
            clearInterval(backgroundDetector);
            backgroundDetector = 0;
        }
    }

    /**
     * Stop the autosave function
     */
    function stopAutoSave() {
        if (autoSaver) {
            clearInterval(autoSaver);
            autoSaver = 0;
        }
    }

    /**
     * Dectect changes in the question form
     * This is done periodically by comparing the serialized form data
     * It is independent from the way in which answers are changed
     */
    function detectFormChange() {

        // don't detect if status is sticky
        if (stickyChanged || !config.withFormChangeDetection) {
            return;
        }

        // force a copy of edited richtext to its textarea
        if (typeof tinyMCE != 'undefined') {
            tinyMCE.triggerSave();
        }

        // get and compare the current form data
        var newData = $(FORM_SELECTOR).serialize();
        answerChanged = (newData != origData);
        refreshAnswerStatusView();

        // check for changes without the 'use unchanged answer' checkbox
        var pureData = newData.replace(/&?tst_force_form_diff_input=1/g,'');
        if (pureData != origData) {
            disableUseUnchangedAnswer();
        }
        else {
            enableUseUnchangedAnswer();
        }
    }

    /**
     * Dectect changes sent in the background to ILIAS
     * This is done periodically by polling them from ILIAS
     * It is needed for Java and Flash questions and eventually question plugins
     */
    function detectBackgroundChanges() {

        if (!config.withBackgroundChangeDetection) {
            return;
        }

        $.ajax({
                type: 'GET',
                url: config.backgroundDetectorUrl,
                dataType: 'json',
                timeout: BACKGROUND_DETECTOR_PERIOD
            })
            .done(detectBackgroundChangesSuccess)
            .fail(detectBackgroundChangesFailure);
    }

    /**
     * Handle successful detection of background changes
     * @param  response
     */
    function detectBackgroundChangesSuccess(response) {
        answered = response.isAnswered;
        answerChanged = response.isAnswerChanged;
        refreshAnswerStatusView();
    }

    /**
     * Handle failed detection of background changes
     * @param jqXHR
     */
    function detectBackgroundChangesFailure(jqXHR) {
        $('#autosavemessage').text(jqXHR.responseText)
            .fadeIn(500, function(){
                $('#autosavemessage').fadeOut(5000)
            });
    }

    /**
     * Refresh the answer status dependent display of elements
     */
    function refreshAnswerStatusView() {

        // save the revert changes url to allow a tweaking with '#'
        // '#' is needed to close the action popup when clicked
        if ( revertUrl == '') {
            revertUrl = $('#tst_revert_changes_action').attr('href');
        }

        if (answered) {
            $('.ilTestAnswerStatusAnswered').removeClass('hidden').show();
            $('.ilTestAnswerStatusNotAnswered').hide();
            $('.ilTestDiscardSolutionAction').removeClass('disabled');
        }
        else {
            $('.ilTestAnswerStatusAnswered').hide();
            $('.ilTestAnswerStatusNotAnswered').removeClass('hidden').show();
            $('.ilTestDiscardSolutionAction').addClass('disabled');
         }

        if(answerChanged) {
            $('.ilTestAnswerStatusEditing').removeClass('hidden').show();
            $('.ilTestRevertChangesAction').removeClass('disabled');
            $('#tst_revert_changes_action').attr('href', revertUrl);
        }
        else {
            $('.ilTestAnswerStatusEditing').hide();
            $('.ilTestRevertChangesAction').addClass('disabled');
            $('#tst_revert_changes_action').attr('href','#');
        }
    }

    /**
     * Event handler for clicked links on the test page
     * @returns {boolean}
     */
    function checkNavigation() {

        // attributes of the clicked link
        var id = $(this).attr('id');
        var href = $(this).attr('href');
        var target = $(this).attr('target');

        // keep default behavior for links that open in another window
        // (fullscreen view of media objects)
        if (target && target !== '_self' && target !== '_parent' && target !== '_top')
        {
           return true;
        }

        // ignore JavaScript links
        if (href.indexOf("javascript:") === 0) {
           return true;
        }

        // check explictly again at navigation
       detectFormChange();

        if (id == 'tst_mark_question_action')           // marking the question is always possible
        {
            navUrl = href;
            toggleQuestionMark();
            return false;
        }
        else if( config.nextQuestionLocks && $(this).attr('data-nextcmd') == 'nextQuestion' )
        {
            // remember the url for saveWithNavigation()
            navUrl = href;
            
            if( !answerChanged && !answered )
            {
                showFollowupQuestionLocksEmptyAnswerModal();
            }
            else if( $('#tst_next_locks_changed_modal').length > 0 )
            {
                showFollowupQuestionLocksCurrentAnswerModal();
            }
            else
            {
                saveWithNavigation();
            }

            return false; // prevent the default event handler
        }
        
        if (answerChanged                               // answer has been changed
            && href                                     // link is not an anchor
            && href.charAt(0) != '#'                    // link is not a fragment
            && id != 'tst_discard_answer_action'        // link is not the 'discard answer' button

            && id != 'tst_revert_changes_action'        // link is not the 'revert changes' action
            && id != 'tst_discard_solution_action'      // link is not the 'discard solution' action
        ) {
            // remember the url for saveWithNavigation()
            navUrl = href;

            if ($('#tst_save_on_navigation_modal').length > 0) {
                showNavigationModal();
            }
            else {
                saveWithNavigation();
            }

            // prevent the default event handler
            return false;
        }
        else
        {
            // apply the default event handler (go to href)
            return true;
        }
    }

    /**
     * Show the navigation modal
     */
    function showNavigationModal() {
        $('#tst_save_on_navigation_modal').modal('show');
        $('#save_on_navigation_prevent_confirmation').attr('checked',false);
    }

    /**
     * Hide the navigation modal
     */
    function hideNavigationModal() {
        $('#tst_save_on_navigation_modal').modal('hide');
    }

    /**
     * Show the discard solution modal
     */
    function showDiscardSolutionModal() {
        if (answered) {
            $('#tst_discard_solution_modal').modal('show');
        }
    }

    /**
     * Hide the discard solution modal
     */
    function hideDiscardSolutionModal() {
        $('#tst_discard_solution_modal').modal('hide');
    }
    
    function showFollowupQuestionLocksCurrentAnswerModal()
    {
        $('#tst_next_locks_changed_modal').modal('show');
        $('#followup_qst_locks_prevent_confirmation').attr('checked',false);
    }
    
    function hideFollowupQuestionLocksCurrentAnswerModal()
    {
        $('#tst_next_locks_changed_modal').modal('hide');
    }
    
    function showFollowupQuestionLocksEmptyAnswerModal()
    {
        $('#tst_next_locks_unchanged_modal').modal('show');
    }
    
    function hideFollowupQuestionLocksEmptyAnswerModal()
    {
        $('#tst_next_locks_unchanged_modal').modal('hide');
    }

    /**
     * Disable and uncheck the 'use unchanged answer' checkbox
     */
    function disableUseUnchangedAnswer() {
        $('#tst_force_form_diff_input').attr('disabled','disabled').removeAttr('checked');
        $('#ilQuestionForceFormDiffInputLabel').addClass('text-muted');
    }

    /**
     * Enable the 'use unchanged answer' checkbox but don't check
     */
    function enableUseUnchangedAnswer() {
        $('#tst_force_form_diff_input').removeAttr('disabled');
        $('#ilQuestionForceFormDiffInputLabel').removeClass('text-muted');
    }

    /**
     * Save the form with additional redirection parameter
     */
    function saveWithNavigation() {

        // prevent status change by added form element
        stopDetection();
        stopAutoSave();

        // determine the command to be shown
        var command;
        if (config.forcedInstantFeedback) {
            command = 'cmd[showInstantResponse]';
        }
        else {
            command = 'cmd[submitSolution]';
        }

        // add the navigation href as POST parameter
        // this will be used for the redirect after saving
        $('<input>').attr({
            type: 'hidden',
            name: 'test_player_navigation_url',
            value: navUrl
        }).appendTo(FORM_SELECTOR);

        $('<input>').attr({
            type: 'hidden',
            name: command,
            value: 'Save'
        }).appendTo(FORM_SELECTOR);

        // submit the solution
        // the answering status will be appended by handleFormSubmit()
        $(FORM_SELECTOR).submit();
    }

    function saveWithNavigationEmptyAnswer() {
        $(FORM_SELECTOR).find('input[name=orderresult]').remove();
        $(FORM_SELECTOR).find('input[name*=order_elems]').remove();
        saveWithNavigation();
    }

    /**
     * Toggle the question mark
     * This keeps the editing status
     * It saves an intermediate solution if the question is edited
     */
    function toggleQuestionMark() {

        // set the mark link target as form action and submit
        /// the answering status will be appended by handleFormSubmit()
        $(FORM_SELECTOR).attr('action', navUrl).submit();
    }

    /**
     * Activate the handler for the save button of rich text areas
     */
    function activateMceSaveHandler() {

        // check if the save button is already added by tinyMCE
        if ($('.mce_save').length > 0) {

            // set the handler on the inner span to fire before the default handler of the save button
            $('.mce_save span').mousedown(handleMceSave);
        }
        else {
            // check again in 100 ms
            setTimeout(activateMceSaveHandler, 100);
        }
    }

    /**
     * Handle save button of rich text areas
     */
    function handleMceSave() {

        // Do a last detection and prevent status change by added element
        detectFormChange();
        stopDetection();
        stopAutoSave();

        // add the save command to the forl
        $('<input>').attr({
            type: 'hidden',
            name: 'cmd[submitSolution]',
            value: 'Save'
        }).appendTo(FORM_SELECTOR);

        // submit the solution
        $(FORM_SELECTOR).submit();

        // prevent the default handler
        return false;
    }

    /**
     * Handle the form submission
     */
    function handleFormSubmit() {
        //var submitBtn = $(this).find("input[type=submit]:focus"); // perhaps neccessary anytime
        
        // add the 'answer changed' parameter to the url
        // this keeps the answering status for mark and feedback functions
        if (answerChanged) {
            $(FORM_SELECTOR).attr('action', $(FORM_SELECTOR).attr('action') + '&test_answer_changed=1');
        }

        // let the backend decide where a focus element is using the html id focus
        $(FORM_SELECTOR).attr('action', $(FORM_SELECTOR).attr('action') + '#focus');

        // now the form can be submitted
        return true;
    }

    /**
     * Automatically save the form data if they are changed since the last save
     * Add the changed status to the autosave
     */
    function autoSave() {

        // add the changed status for autosaving
        var url;
        if (answerChanged) {
           url = config.autosaveUrl + '&test_answer_changed=1';
        }
        else {
           url = config.autosaveUrl + '&test_answer_changed=0';
        }

        // force a copy of edited richtext to its textarea
        if (typeof tinyMCE != 'undefined') {
            tinyMCE.triggerSave();
        }

        // get and compare the current form data
        var newData = $(FORM_SELECTOR).serialize();
        if (autoSavedData != newData) {

            $.ajax({
                    type: 'POST',
                    url: url,
                    data: newData,
                    dataType: 'text',
                    timeout: config.autosaveInterval
                })
            .done(autoSaveSuccess)
            .fail(autoSaveFailure);

            autoSavedData = newData;

            // fix mantis #2506:
            // the question must stay at changed status, once an unauthorized solution exists
            // otherwise nothing will be saved at navigation and the auto saved solution remains
            // going back to the question would show the auto saved solution as "editing"
            self.stickAnswerChanged();
        }
    }

    /**
     * Handle auto saving success
     * @param  responseText
     */
    function autoSaveSuccess(responseText) {

        if (typeof responseText !== 'undefined' && responseText != '-IGNORE-') {
            $('#autosavemessage').text(responseText)
                .fadeIn(500, function(){
                    $('#autosavemessage').fadeOut(5000)
            });
        }
    }

    /**
     * Handle auto saving failure
     * @param jqXHR
     */
    function autoSaveFailure(jqXHR) {

        $('#autosavemessage').text(jqXHR.responseText)
            .fadeIn(500, function(){
                $('#autosavemessage').fadeOut(5000)
        });
        autoSavedData = '';
    }
};
