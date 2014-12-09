var ilias = {}; //namespace
var icount = 0; //interaction count
ilias.UTILS={};
ilias.questions = {
	answers: Object,
	success: "not attempted",
	default_feedback: true
};

ilias.questions.txt = {
	wrong_answers: "Incorrect Items",
	wrong_answers_single: "Incorrect Choice.",
	tries_remaining: "Tries Remaining",
	please_try_again: "Please try again!",
	all_answers_correct: "Correct!",
	enough_answers_correct: 'Correct, but not the best solution!',
	nr_of_tries_exceeded: "Number of tries exceeded.",
	correct_answers_shown: "Correct solution see above.",
	correct_answers_also: "Also correct are:",
	correct_answer_also: "Also correct is:",
	ov_all_correct: "You have correctly answered all questions.",
	ov_some_correct: "You have correctly answered [x] out of [y] questions.",
	ov_wrong_answered: "The following questions were not answered or answered wrong",
	please_select: "Please Select"
};

// these question types disable themself in checkAnswers instead of showCorrectAnswers
ilias.questions.enhancedQuestionTypes = [
    "assMatchingQuestion"
];

ilias.questions.questionTypesSupportingPartialScoring = [
	"assKprimChoice"
];

ilias.questions.init = function() {
	ilias.questions.shuffle();
};

ilias.questions.refresh_lang = function() {

	jQuery(".ilc_qinput_ClozeGapSelect").each(function(){
		$(this).prepend("<option id='-1' value='-1' selected='selected'>-- "+
			ilias.questions.txt.please_select+" --</option>");

		$(this).val("");
	});
	
};

ilias.questions.shuffleAll = function() {
	//shuffle questions
	for (var k in questions) {
		if (questions[k].shuffle === true) {
			ilias.questions.swapper(questions[k].answers);
		}
	}	
};

ilias.questions.shuffle = function(a_question) {
	//shuffle questions
	if (!a_question.answers) {return;}
	if (a_question.shuffle === true) {
		ilias.questions.swapper(a_question.answers);
	}
};

ilias.questions.swapper = function(a)
{
	var i,L;
	i = L = a.length;
	while (i--)
	{
		var r = Math.floor(Math.random()*L);
		var x = a[i];
		a[i] = a[r];
		a[r] = x;
	}
};

ilias.questions.initAnswer = function(a_id, tries, passed) {
	if (!answers[a_id]) {	// to keep answers[a_id].areas intact if initialized before
		answers[a_id] = {};
	}
	answers[a_id].tries = tries;
	answers[a_id].wrong = 0;
	answers[a_id].passed = passed;
	answers[a_id].answer = new Array();
	answers[a_id].interactionId=null;
	if (tries > 0 && (answers[a_id].tries >= questions[a_id].nr_of_tries || passed)) {
		answers[a_id].passed = passed;
		ilias.questions.showFeedback(a_id);
	}
};

ilias.questions.checkAnswers = function(a_id) {
	if (!answers[a_id]) {
		ilias.questions.initAnswer(a_id, 0, null);
	}
	answers[a_id].tries++;
	
	var call = "ilias.questions."+questions[a_id].type+"("+a_id+")";
	
	eval(call);


	if (typeof il.LearningModule != "undefined") {
		il.LearningModule.processAnswer(ilias.questions);
	}

	// forward answer to self assessment question handler (non-scorm)
	if (typeof ilCOPageQuestionHandler != "undefined") {
		ilCOPageQuestionHandler.processAnswer(questions[a_id].type, a_id, answers[a_id]);
	}
};

ilias.questions.handleMCImages = function(a_id) {

	if(questions[a_id].path === undefined)
	{
		return;
	}

	jQuery('div#container' + a_id + ' input.order').each(function(key, node){
		for(var i=0;i<questions[a_id].answers.length;i++)
		{
			if(questions[a_id].answers[i].order == node.value)
			{
				var img = questions[a_id].answers[i].image;
				if(img.length)
				{
					var text_node = jQuery(node).next();
					if(questions[a_id].thumb > 0)
					{
						jQuery(text_node).before('<a class="ilc_qimgd_ImageDetailsLink" href="' + questions[a_id].path + img + '" target="_blank">' +
							'<img class="ilc_qimg_QuestionImage" src="' + questions[a_id].path + 'thumb.' + img + '" /></a>');
					}
					else
					{
						jQuery(text_node).before('<img class="ilc_qimg_QuestionImage" src="' + questions[a_id].path + img + '" />');
					}
					
				}
			}
		}

	});
}

ilias.questions.assSingleChoice = function(a_id) {

	var a_node = jQuery('input[name="answers'+a_id+'"]');
	var tocheck = "points";

	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
	answers[a_id].choice = [];
	
	var checked_right = false;
			
	for (var i=0;i<a_node.length;i++) {
		if ((!a_node.get(i).checked && questions[a_id].answers[i][tocheck] > 0) 
			|| (a_node.get(i).checked && questions[a_id].answers[i][tocheck] <= 0))
		{			
			answers[a_id].wrong++;
			answers[a_id].answer[i]=false;			
		} else {
			if (a_node.get(i).checked)
			{
				checked_right = true;
			}
			answers[a_id].answer[i]=true;
		}
		if (a_node.get(i).checked)
		{
			answers[a_id].choice.push(a_node.get(i).value);
		}
	}		

	answers[a_id].passed = checked_right; // #10772
	
	ilias.questions.showFeedback(a_id);
};

ilias.questions.assMultipleChoice = function(a_id) {
	
	var a_node = jQuery('input[name="answers'+a_id+'"]');
	var tocheck = "points_checked";
	
	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
	answers[a_id].choice = [];
	
	for (var i=0;i<a_node.length;i++) {
		if ((!a_node.get(i).checked && questions[a_id].answers[i][tocheck] > 0) 
			|| (a_node.get(i).checked && questions[a_id].answers[i][tocheck] <= 0))
		{
			answers[a_id].wrong++;
			answers[a_id].passed = false;
			answers[a_id].answer[i]=false;
			
		} else {
			answers[a_id].answer[i]=true;
		}
		if (a_node.get(i).checked)
		{
			answers[a_id].choice.push(a_node.get(i).value);
		}

	}		
	ilias.questions.showFeedback(a_id);			
};

ilias.questions.assKprimChoice = function(a_id) {

	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
	answers[a_id].isBestSolution = true;
	answers[a_id].choice = [];

	for (var i = 0; i < questions[a_id].answers.length; i++)
	{
		var answer = questions[a_id].answers[i];
		var input = jQuery('input[name="kprim_choice_'+a_id+'_result_'+answer.order+'"]:checked');

		answers[a_id].answer[i] = true;
		
		if( !input || jQuery(input).val() != questions[a_id].answers[i]['correctness'] )
		{
			answers[a_id].isBestSolution = false;
			answers[a_id].wrong++;
			answers[a_id].answer[i] = false;
		}
		
		if( input )
		{
			answers[a_id].choice.push(answer.order);
		}
	}
	
	if( answers[a_id].wrong > questions[a_id].num_allowed_failures )
	{
		answers[a_id].passed = false;
	}
	
	ilias.questions.showFeedback(a_id);
};

ilias.questions.assTextQuestion = function(a_id) {
	jQuery('#button'+a_id).prop("disabled",true);
	jQuery('#textarea'+a_id).prop("disabled",true);
	jQuery('#feedback'+a_id).addClass("ilc_qfeedr_FeedbackRight");
	jQuery('#feedback'+a_id).html('<b>Answer submitted!</b><br>');
	jQuery('#feedback'+a_id).slideToggle();
	answers[a_id].passed = true;
	ilias.questions.scormHandler(a_id,"neutral",jQuery('#textarea'+a_id).val());
};

ilias.questions.assOrderingQuestion = function(a_id) {

	var result = jQuery('#order'+a_id).sortable('toArray');
	
	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
	answers[a_id].choice = [];
	
	for (var i=0;i<result.length;i++) {
		if (i+1 != result[i])
		{
			answers[a_id].passed = false;
			answers[a_id].wrong ++;
			answers[a_id].answer[i]=false;
		} else {
			answers[a_id].answer[i]=true;
		}
		answers[a_id].choice.push(result[i]);
	}
	ilias.questions.showFeedback(a_id);
};


ilias.questions.handleOrderingImages = function(a_id) {

	if(questions[a_id].path === undefined)
	{
		return;
	}

	jQuery("ul#order" + a_id + " div.answertext").each(function(id, node){
		var src = jQuery(node).html();
		jQuery(node).html('<img class="ilc_qimg_QuestionImage" src="' + questions[a_id].path + "thumb." + src + '" />' /*<br />' +
			'<a class="ilc_qimgd_ImageDetailsLink" href="' + questions[a_id].path + src + '" target="_blank">(+)</a>'*/);
		jQuery(node).parent().height("auto");
		// jQuery(node).parent().width("auto");
	});
};

ilias.questions.assOrderingHorizontal = function(a_id) {

	var result = jQuery('#order'+a_id).sortable('toArray');

	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
	answers[a_id].choice = [];

	for (var i=0;i<result.length;i++) {
		if (i+1 != result[i])
		{
			answers[a_id].passed = false;
			answers[a_id].wrong ++;
			answers[a_id].answer[i]=false;
		} else {
			answers[a_id].answer[i]=true;
		}
		answers[a_id].choice.push(result[i]);
	}
	
	ilias.questions.showFeedback(a_id);
};

ilias.questions.toggleArea = function(a_id,order) {
	answers[a_id].areas[order]=!answers[a_id].areas[order];
};

ilias.questions.initAreas = function(a_id) {
	if (!answers[a_id]) {
		ilias.questions.initAnswer(a_id, 0, null);
	}
	if (!answers[a_id].areas) {
		answers[a_id].areas = new Array(questions[a_id].answers.length);
		for (var i=0;i<questions[a_id].answers.length;i++) {
			answers[a_id].areas[i]=false;
		}
	}
};


ilias.questions.assImagemapQuestion = function(a_id) {
	
	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
	answers[a_id].choice = [];
		
	for (var i=0;i<questions[a_id].answers.length;i++) {
		if ((answers[a_id].areas[i]==false && questions[a_id].answers[i].points > 0) || (answers[a_id].areas[i]==true && questions[a_id].answers[i].points <= 0))
		{
			answers[a_id].passed = false;
			answers[a_id].wrong++;
			answers[a_id].answer[i]=false;
		} else {
			answers[a_id].answer[i]=true;
		}
		if (answers[a_id].areas[i] == true)
		{
			answers[a_id].choice.push(i);
		}
	}		
	ilias.questions.showFeedback(a_id);
};

ilias.questions.assMatchingQuestion = function(a_id) { (function($){

    var answerData = answers[a_id];

    answerData.wrong = 0;
    answerData.passed = true;
    answerData.choice = [];

    var questionData = questions[a_id];

    var selected = 0, foundCorrect = 0, foundWrong = 0;
    
    for( var i = 0; i < questionData.definitions.length; i++ )
    {
        var definition = questionData.definitions[i];
        var dropArea = $('#definition_'+definition.id);
        
        var selectedTerms = dropArea.find('input[type=hidden]');
        
        selected += selectedTerms.length;

        selectedTerms.each( function(key, term)
        {
            answerData.choice.push(definition.id+'-'+$(term).attr('value'));
            
            var found = false;
            
            for( var j = 0; j < questionData.matchingPairs.length; j++ )
            {
                var matching = questionData.matchingPairs[j];
                
                if( definition.id != matching.def_id )
                {
                    continue;
                }

                if( $(term).attr('value') == matching.term_id )
                {
                    found = true;
                    break;
                }
            }
            
            if(found)
            {
                foundCorrect++;
            }
            else
            {
                foundWrong++;
            }
        });
        
    }
    
	if( foundCorrect < questionData.matchingPairs.length || foundWrong )
    {
        answerData.passed = false;
        
        answerData.wrong = questionData.matchingPairs.length - foundCorrect;
        
        if(questionData.matching_mode.toLowerCase() == 'n:n')
        {
            answerData.wrong += foundWrong;
        }
	}

    if( answerData.passed || questionData.nr_of_tries && answerData.tries >= questionData.nr_of_tries )
    {
        questionData.engineInstance.disable();
    }
	
	ilias.questions.showFeedback(a_id);
    
})(jQuery);};

ilias.questions.assTextSubset = function(a_id) {
	
	answers[a_id].wrong = 0;
	answers[a_id].passed = false;
	answers[a_id].choice = [];

	var correct_answer_given = false;
	var wrong_answer_given = false;
	var a_node = jQuery('input[name="answers'+a_id+'[]"]');
	for (var i=0;i<a_node.length;i++) {

		var answer = a_node.get(i).value;
		answers[a_id].choice.push(answer);

		if(questions[a_id].matching_method == "ci")
		{
			answer = answer.toLowerCase();
		}

		var found = false;
		for (var c=0;c<questions[a_id].correct_answers.length;c++)
		{
			var correct_answer = questions[a_id].correct_answers[c]["answertext"];
			if(questions[a_id].matching_method == "ci")
			{
				correct_answer = correct_answer.toLowerCase();
			}			
			if(correct_answer == answer && questions[a_id].correct_answers[c]["points"] > 0)
			{
				found = true;				
				
				// check if answer was given multiple times
				for (var j=0;j<i;j++) {
					var old_answer = a_node.get(j).value;
					if(questions[a_id].matching_method == "ci")
					{
						old_answer = old_answer.toLowerCase();
					}
					if(old_answer == answer)
					{
						found = false;
						j = i;
					}
				}					
			}
		}
		if(found === false)
		{
			answers[a_id].wrong++;
			answers[a_id].answer[i] = false;
			wrong_answer_given = true;
		}
		else
		{			
			answers[a_id].answer[i] = true;
			correct_answer_given = true;
		}
	}

	if(correct_answer_given && !wrong_answer_given)
	{
		answers[a_id].passed = true;
	}

	ilias.questions.showFeedback(a_id);
};


ilias.questions.assClozeTest = function(a_id) {
	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
	answers[a_id].choice = [];
	
	for (var i=0;i<questions[a_id].gaps.length;i++)
	{
		var type = questions[a_id].gaps[i].type;
		// select
		if (type==1) {
			var a_node = jQuery('select#'+a_id+"_"+i).get(0);
			var selected = a_node.options[a_node.selectedIndex].id;			
			if (parseInt(selected) < 0 || questions[a_id].gaps[i].item[selected].points <= 0) {
				answers[a_id].passed = false;
				answers[a_id].wrong++;
				answers[a_id].answer[i]=false;
			} else {
				answers[a_id].answer[i]=true;
			}
			if (parseInt(selected) >= 0) {
				answers[a_id].choice.push(questions[a_id].gaps[i].item[selected].order);
			}
		}
		else
		{
			var a_node = jQuery('input#'+a_id+"_"+i).get(0);
			var value_found = false;
			
			// text
			if (type==0) {				
				for(var j=0;j<questions[a_id].gaps[i].item.length;j++)
				{
					if (questions[a_id].gaps[i].item[j].value == a_node.value) {
						value_found=true;
						if (questions[a_id].gaps[i].item[j].points <= 0) {
							answers[a_id].passed = false;
							answers[a_id].wrong++;
							answers[a_id].answer[i]=false;
						} else {
							answers[a_id].answer[i]=true;
						}
					}
				}				
			}
			// numeric
			else if (type==2) {				
				for(var j=0;j<questions[a_id].gaps[i].item.length;j++)
				{				
					if (questions[a_id].gaps[i].item[j].lowerbound <= a_node.value && 
						questions[a_id].gaps[i].item[j].upperbound >= a_node.value) {
						value_found=true;
						if (questions[a_id].gaps[i].item[j].points <= 0) {
							answers[a_id].passed = false;
							answers[a_id].wrong++;
							answers[a_id].answer[i]=false;
						} else {
							answers[a_id].answer[i]=true;
						}
					}
				}
				
			}
			
			answers[a_id].choice.push(a_node.value);
			if (value_found==false) {answers[a_id].passed = false; answers[a_id].wrong++; answers[a_id].answer[i]=false;}
		}
	}
	ilias.questions.showFeedback(a_id);
};

ilias.questions.initClozeTest = function(a_id) {
	var closecounter = 0;
	_initClozeTestCallBack = function (found) {
		var type = questions[a_id].gaps[closecounter].type;
		var input;
		if (type == 0 || type == 2) {
			var size = questions[a_id].gaps[closecounter].size;
			if (typeof size == "undefined") {
				size = (type == 0) ? 20 : 4;
			}

			input = jQuery.create('input', {
				'id': a_id + "_" + closecounter,
				'type':      'text',
				'size':      size,
				'maxlength': questions[a_id].gaps[closecounter].size,
				'class':     'ilc_qinput_TextInput'
			});
		}
		if (type==1) {
			input = jQuery.create('select', {'id': a_id+"_"+closecounter, 'class': 'ilc_qinput_ClozeGapSelect'});			
			for (var i=0;i<questions[a_id].gaps[closecounter].item.length;i++) {
				var option = jQuery.create('option', {'id': i, 'value':i},questions[a_id].gaps[closecounter].item[i].value);
				input.append(option);
			}
		}
		closecounter++;
		return input.outerHTML();
	 };
	var parsed=jQuery("div#"+a_id).get(0).innerHTML.replace(/\[gap\][^\[]+\[\/gap\]/g,function(){return _initClozeTestCallBack();});
	jQuery("div#"+a_id).html(parsed);
};

ilias.questions.selectErrorText = function(a_id, node) {

	if(questions[a_id].selected === undefined)
	{
		questions[a_id].selected = [];
	}

	var id = jQuery(node).parent().attr("id");
	var id_index = jQuery.inArray(id, questions[a_id].selected);
	
	if(id_index > -1)
	{
		jQuery(node).removeClass("ilc_qetitem_ErrorTextSelected");
		questions[a_id].selected.splice(id_index, 1);
	}
	else
	{
		jQuery(node).addClass("ilc_qetitem_ErrorTextSelected");
		questions[a_id].selected.push(id);
	}

	jQuery(node).blur();
};

ilias.questions.assErrorText =function(a_id) {

	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
	answers[a_id].choice = [];
	
	if(questions[a_id].selected === undefined)
	{
		answers[a_id].passed = false;
	}
	else
	{	
		var found = 0;
		for(var i=0;i<questions[a_id].answers.length;i++)
		{
			// is current word a correct answer == wrong word?
			var text_select = questions[a_id].answers[i]["answertext"];
			var is_wrong = false;
			for(var j=0;j<questions[a_id].correct_answers.length;j++)
			{				
				if(text_select == questions[a_id].correct_answers[j]["answertext_wrong"] &&
					questions[a_id].correct_answers[j]["pos"] == questions[a_id].answers[i]["order"]) // #14115
				{
					is_wrong = true;
				}
			}
			
			// word has been selected		
			if(jQuery.inArray(questions[a_id].answers[i]["order"], questions[a_id].selected) > -1)
			{
				// word is not a correct answer		
				if(is_wrong === false)
				{
					answers[a_id].wrong++;
				}
				// found correct answer
				else
				{
					found++;
				}
			}
			// word has not been selected
			else if(is_wrong === true)
			{
				// should have been selected
				answers[a_id].wrong++;
			}
		}					
		if(found < questions[a_id].correct_answers.length ||
			answers[a_id].wrong > 0)
		{
			answers[a_id].passed = false;
		}
	}

	ilias.questions.showFeedback(a_id);
}

ilias.questions.showFeedback =function(a_id) {
	
	jQuery('#feedback'+a_id).hide();

	// "image map as single choice" not supported yet
	if(questions[a_id].type == "assSingleChoice")
	{
		var txt_wrong_answers = ilias.questions.txt.wrong_answers_single;
	}
	else
	{
		var txt_wrong_answers = ilias.questions.txt.wrong_answers + ': ' +
				answers[a_id].wrong ;
	}
	
	if(jQuery.inArray(questions[a_id].type, ilias.questions.questionTypesSupportingPartialScoring) == -1)
	{
		answers[a_id].isBestSolution = answers[a_id].passed;
	}

	jQuery('#feedback'+a_id).removeClass("ilc_qfeedw_FeedbackWrong");
	jQuery('#feedback'+a_id).removeClass("ilc_qfeedr_FeedbackRight");

	var fbtext = "";

	if (answers[a_id].passed===true || (answers[a_id].tries >=questions[a_id].nr_of_tries && questions[a_id].nr_of_tries!=0))
	{
		jQuery('#button'+a_id).prop("disabled",true);

		if (answers[a_id].passed===true)
		{
			jQuery('#feedback'+a_id).addClass("ilc_qfeedr_FeedbackRight");

			if( answers[a_id].isBestSolution )
			{
				if (ilias.questions.default_feedback)
				{
					fbtext = '<b>' + ilias.questions.txt.all_answers_correct + '</b><br />';
				}

				if (questions[a_id].feedback['allcorrect'])
				{
					fbtext += questions[a_id].feedback['allcorrect'];
				}

				if( jQuery.inArray(questions[a_id].type, ilias.questions.enhancedQuestionTypes) == -1 )
				{
					ilias.questions.showCorrectAnswers(a_id);
				}
			}
			else
			{
				if (ilias.questions.default_feedback)
				{
					fbtext = '<b>' + ilias.questions.txt.enough_answers_correct + '</b><br />'
						+ txt_wrong_answers + '<br />' + ilias.questions.txt.correct_answers_shown;
				}
				else if (questions[a_id].feedback['allcorrect'])
				{
					fbtext += questions[a_id].feedback['allcorrect'];
				}
				
				ilias.questions.showCorrectAnswers(a_id);
			}

			ilias.questions.scormHandler(a_id,"correct",ilias.questions.toJSONString(answers[a_id]));
		}
		else
		{
			jQuery('#feedback'+a_id).addClass("ilc_qfeedw_FeedbackWrong");
			
			if (ilias.questions.default_feedback)
			{
				fbtext = '<b>' + ilias.questions.txt.nr_of_tries_exceeded + '</b><br />'
							+ ilias.questions.txt.correct_answers_shown + '<br />';
			}
			
			if (questions[a_id].feedback['onenotcorrect'])
			{
				fbtext += questions[a_id].feedback['onenotcorrect'];
			}

			ilias.questions.showCorrectAnswers(a_id);
			
			ilias.questions.scormHandler(a_id,"incorrect",ilias.questions.toJSONString(answers[a_id]));
		}
	}
	else
	{
		if (questions[a_id].nr_of_tries!=0)
		{
			jQuery('#feedback'+a_id).addClass("ilc_qfeedw_FeedbackWrong");
			
			var rem = questions[a_id].nr_of_tries - answers[a_id].tries;
			
			if (ilias.questions.default_feedback)
			{
				fbtext = txt_wrong_answers + '<br />' + ilias.questions.txt.tries_remaining + ': '+ rem + "<br />";
			}
			
			if (questions[a_id].feedback['onenotcorrect'])
			{
				fbtext += questions[a_id].feedback['onenotcorrect'];
			}
			
			ilias.questions.scormHandler(a_id,"incorrect",ilias.questions.toJSONString(answers[a_id]));
		}
		else
		{
			jQuery('#feedback'+a_id).addClass("ilc_qfeedw_FeedbackWrong");
			
			if (ilias.questions.default_feedback)
			{
				fbtext = txt_wrong_answers + '<br /> ' + ilias.questions.txt.please_try_again + '<br />';
			}
			
			if (questions[a_id].feedback['onenotcorrect'])
			{
				fbtext += questions[a_id].feedback['onenotcorrect'];
			}
			
			ilias.questions.scormHandler(a_id,"incorrect",ilias.questions.toJSONString(answers[a_id]));
		}
	}
	
	jQuery('#feedback'+a_id).html(fbtext);
	jQuery('#feedback'+a_id).slideToggle();
	
	// update question overviews
	if (typeof il.COPagePres != "undefined")
	{
		il.COPagePres.updateQuestionOverviews();
	}

};


ilias.questions.scormHandler = function(a_id,a_state,a_response) {
	var version;

	if (ScormApi==null) {return;}
	var tries = answers[a_id].tries;
	var i_key;
	var s_key;
	switch (ScormApi.version) {
		case '1.2':
			i_key = "cmi.interactions.";
			s_key = "cmi.core.lesson_status";
			break;
		case '1.3':
			i_key = "cmi.interactions.";
			s_key = "cmi.success_status";
			break;
	}
	if (tries==1) {
		//define interaction
		answers[a_id].interactionId=icount;
		setValue(i_key + answers[a_id].interactionId+".id","interaction_"+a_id);
		setValue(i_key + answers[a_id].interactionId+".type","other");
		setValue(i_key + answers[a_id].interactionId+".result",a_state);
		setValue(i_key + answers[a_id].interactionId+".learner_response",a_response);
		setValue(i_key + answers[a_id].interactionId+".description",questions[a_id].question);
		icount++;
	} else {
		setValue(i_key + answers[a_id].interactionId+".learner_response",a_response);
		setValue(i_key + answers[a_id].interactionId+".result",a_state);
	}
	
	ilias.questions.updateSuccessStatus();
	if (pager != null)
	{
		pager.updateNextLink();
	}
};

ilias.questions.updateSuccessStatus = function()
{
	var s_key;
	var status = ilias.questions.determineSuccessStatus();
	
	
	if (ScormApi==null) {return;}
	
	if (status=="passed" || ScormApi.version=="1.3" ) {

		switch (ScormApi.version) {
			case '1.2':
				s_key = "cmi.core.lesson_status";
				break;
			case '1.3':
				// bug #9413
				if (status == "") {
					status = "unknown";
				}	
				s_key = "cmi.success_status";
				break;
		}

		setValue(s_key,status);
	}	
}

ilias.questions.determineSuccessStatus = function()
{
	var status = "";
	var at_least_one = false;
	for (var k in questions) {
		var index=parseInt(k,10);
		if (!isNaN(index)) {
			if (status != "failed")
			{
				status = "passed";
			}
			if (!answers[index]) {
				status = "failed";
			} else {
				if (answers[index].passed!=true) {status="failed";}
			}
		}
	}
	return status;
}

ilias.questions.showCorrectAnswers =function(a_id) {
	
	switch (questions[a_id].type) {
		
		case 'assSingleChoice':				
			var max = 0; // #10772
			for (var i=0;i<questions[a_id].answers.length;i++) {
				if (questions[a_id].answers[i].points > max)
				{
					max = questions[a_id].answers[i].points;
				}				
				jQuery('input[name="answers'+a_id+'"]').eq(i).prop("disabled",true);
				jQuery('input[name="answers'+a_id+'"]').eq(i).prop("checked",false);
			}			
			for (var i=0;i<questions[a_id].answers.length;i++) {
				if (questions[a_id].answers[i].points == max) {
					jQuery('input[name="answers'+a_id+'"]').eq(i).prop("checked",true);
				}
			}
			break;
		   //end assSingleChoice
		
		case 'assMultipleChoice':	
			for (var i=0;i<questions[a_id].answers.length;i++) {
				if (questions[a_id].answers[i].points_checked > 0) {
					jQuery('input[name="answers'+a_id+'"]').eq(i).prop("checked",true);
				} else {
					jQuery('input[name="answers'+a_id+'"]').eq(i).prop("checked",false);
				}
				jQuery('input[name="answers'+a_id+'"]').eq(i).prop("disabled",true);
				
			}
			break;
			//end assMultipleChoice

		case 'assKprimChoice':
			for( var i = 0; i < questions[a_id].answers.length; i++ )
			{
				var correctness = questions[a_id].answers[i].correctness ? 1 : 0;
				
				var inputs = jQuery('input[name="kprim_choice_'+a_id+'_result_'+questions[a_id].answers[i].order+'"]');
				
				inputs.each(
					function(pos, input)
					{
						if( jQuery(input).val() == questions[a_id].answers[i].correctness )
						{
							jQuery(input).prop('checked', true);
						}
						else
						{
							jQuery(input).prop('checked', false);
						}

						jQuery(input).prop('disabled', true);
					}
				);
			}
			break;
		//end assKprimChoice
			
		case 'assImagemapQuestion': 
			//reinit map
			jQuery(function() {
		  		jQuery('.cmap'+a_id).maphilight_mod({fade:true});
			});
			for (var i=0;i<questions[a_id].answers.length;i++) {
				// display correct
				if (questions[a_id].answers[i].points > 0) {
					// is already selected?
					if(!jQuery('#canvas_' + a_id + '_' + i).attr('id')) {
						mouseclick(null,document.getElementById(a_id+"_"+questions[a_id].answers[i].order));
					}
				}
				// remove incorrect
				else {
					jQuery('#canvas_' + a_id + '_' + i).remove();
				}
			}
			break;
		   	//end assImagemapQuestion
		
		case 'assOrderingQuestion':
		case 'assOrderingHorizontal':
			var answers = questions[a_id].answers;
			var answers_sorted = answers.sort(sortBySolutionorder);
			var items=jQuery("#order"+a_id).children();
			for (var i=0;i<items.length;i++) {
				var j=i+1;
				jQuery("#order"+a_id +" li:nth-child("+j+") div").html(answers_sorted[i].answertext);
			}
			jQuery("#order"+a_id).sortable("disable");
			ilias.questions.handleOrderingImages(a_id);
		break;
		//end assOrderingQuestion

		case 'assMatchingQuestion':
            (function($){
                
                // have a look to #10353 anytime (fixen halt auch ohne netz am start)
                
                var matchings = questions[a_id].matchingPairs;
                var engineInstance = questions[a_id].engineInstance
                
                engineInstance.reset();
                
                $(matchings).each(function(pos, matching){
                    engineInstance.addMatching(matching.def_id, matching.term_id);
                });

                engineInstance.reinit();
                                
            })(jQuery);
		break;
		//end assMatchingQuestion
		
		case 'assClozeTest':
			for (var i=0;i<questions[a_id].gaps.length;i++) {
				var type = questions[a_id].gaps[i].type;
				if (type==1) {
					var cid;
					jQuery('select#'+a_id+"_"+i).prop("disabled",true);
					//look for correct solution
					for (var j=0;j<questions[a_id].gaps[i].item.length;j++)
					{
						if (questions[a_id].gaps[i].item[j].points>=1)
						{
							cid=j;
						}
					}
					jQuery('select#'+a_id+"_"+i+" option[id="+cid+"]").attr("selected","selected");
				}
				if (type==0 || type==2) {
					var cvalue;
					jQuery('input#'+a_id+"_"+i).prop("disabled",true);
					//look for correct solution
						for (var j=0;j<questions[a_id].gaps[i].item.length;j++)
						{
							if (questions[a_id].gaps[i].item[j].points > 0)
							{
								cvalue = questions[a_id].gaps[i].item[j].value;
							}
						}
					jQuery('input#'+a_id+"_"+i).attr("value",cvalue);
				}
			}
		break;
		//end assClozeTest

		case 'assTextSubset':
			var a_node = jQuery('input[name="answers'+a_id+'[]"]');
			var choice = [];
			for (var i=0;i<a_node.length;i++) {

				var answer = a_node.get(i).value;
				jQuery(a_node[i]).prop("disabled",true);
				
				if(questions[a_id].matching_method == "ci")
				{
					answer = answer.toLowerCase();
				}

				var found = false;
				for (var c=0;c<questions[a_id].correct_answers.length;c++)
				{
					var correct_answer = questions[a_id].correct_answers[c]["answertext"];
					if(questions[a_id].matching_method == "ci")
					{
						correct_answer = correct_answer.toLowerCase();
					}
					if(correct_answer == answer)
					{
						found = true;
						choice.push(c);
					}
				}
				if(found === false)
				{
					jQuery(a_node[i]).attr("value", "");
				}
			}
			var correct_info = "";
			var correct_count = 0;
			for (var c=0;c<questions[a_id].correct_answers.length;c++)
			{
				if($.inArray(c, choice) == -1)
				{
					if (questions[a_id].correct_answers[c]["points"] > 0) {
						correct_info = correct_info + "<li>" + questions[a_id].correct_answers[c]["answertext"] + "</li>";
						correct_count++;
					}
				}
			}
			if(correct_info.length)
			{
				var elements = jQuery("#container"+a_id+" > .ilc_answers");
				if(correct_count > 1)
				{
					var correct_header = ilias.questions.txt.correct_answers_also;
				}
				else
				{
					var correct_header = ilias.questions.txt.correct_answer_also;
				}
				elements.eq(elements.length -1).after("<br/>" +  correct_header + 
					"<ul>" + correct_info + "</ul>");
			}
			break;
			//end assTextSubset

		case 'assErrorText':
			for(var i=0;i<questions[a_id].answers.length;i++)
			{
				var node = jQuery("div#container" + a_id + " span#" + questions[a_id].answers[i]["order"]);
				if(node.length)
				{
					var is_wrong = false;
					var correct = "";
					for(var j=0;j<questions[a_id].correct_answers.length;j++)
					{
						if(questions[a_id].answers[i]["answertext"] == questions[a_id].correct_answers[j]["answertext_wrong"] &&
							questions[a_id].correct_answers[j]["pos"] == questions[a_id].answers[i]["order"]) // #14115
						{
							is_wrong = true;
							correct = questions[a_id].correct_answers[j]["answertext_correct"];
						}
					}
					if(is_wrong == false)
					{
						jQuery(node).html(questions[a_id].answers[i]["answertext"]);
					}
					else
					{
						jQuery(node).html('<span class="ilc_qetcorr_ErrorTextCorrected">' +
							questions[a_id].answers[i]["answertext"] + '</span>' + correct);
					}
				}
			}
			break;
			//end assErrorText
	}
};

function sortBySolutionorder(thisObject,thatObject) {	
	if (thisObject.order > thatObject.order) {
		return 1;
	}
	else if (thisObject.order < thatObject.order) {
		return -1;
	}
	return 0;
}

ilias.questions.fix_imageurls = function(node) 
{
	var container =jQuery(node).find('img');
	for (var i=0;i<container.length;i++) {
		container[i].src = "objects/" +  getFname(container[i].src);
	}
};

ilias.questions.toJSONString =function(v, tab) 
{
	tab = tab ? tab : "";
	var nl = tab ? "\n" : "";
	function fmt(n) {
		return (n < 10 ? '0' : '') + n;
	}
	function esc(s) {
		var c = {'\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"' : '\\"', '\\': '\\\\'};
		return '"' + s.replace(/[\x00-\x1f\\"]/g, function (m) {
			var r = c[m];
			if (r) {
				return r;
			} else {
				r = m.charAt(0);
				return "\\u00" + (r < 16 ? '0' : '') + r.toString(16);
			}
		}) + '"';
	}
	switch (typeof v) {
	case 'string':
		return esc(v);
	case 'number':
		return isFinite(v) ? String(v) : 'null';			
	case 'boolean':
		return String(v);			
	case 'object':
		if (v===null) {
			return 'null';
		} else if (v instanceof Date) {
			return '"' + v.getValue(v) + '"'; // msec not ISO
		} else if (v instanceof Array) {
			var ra = new Array();
			for (var i=0, ni=v.length; i<ni; i+=1) {
				ra.push(v[i]===undefined ? 'null' : ilias.questions.toJSONString(v[i], tab.charAt(0) + tab));
			}
			return '[' + nl + tab + ra.join(',' + nl + tab) + nl + tab + ']';
		} else {
			var ro = new Array();
			for (var k in v) {	
				if (v.hasOwnProperty && v.hasOwnProperty(k)) {
					ro.push(esc(String(k)) + ':' + ilias.questions.toJSONString(v[k], tab.charAt(0) + tab));
				}
			}
			return '{' + nl + tab + ro.join(',' + nl + tab) + nl + tab + '}';
		}
	}
};


//jquery extensions

jQuery.fn.outerHTML = function() {
    return jQuery('<div>').append( this.eq(0).clone() ).html();
};

jQuery.fn.textLimiter = function(){
	return this.each(function(){
			if(typeof(nr) == "undefined") { nr = 0; }
			var counter_id	 = 'counter' +nr;
			var max			 = this.getAttribute('maxlength');
			var html_counter = '<br>Remaining characters: <span id="' +counter_id + '" class="counter"><span>' +max+ '</span></div>';
			jQuery(this).after(html_counter);
			var jquery_pattern = '#' +counter_id +' > span';
			this.relatedElement = jQuery(jquery_pattern)[0];
			nr++;
			jQuery(this).bind("keyup", function(){
				var maxLength	  = this.getAttribute('maxlength');
				var currentLength = this.value.length;
				if(currentLength >= maxLength) {
					this.relatedElement.className = 'toomuch';
					this.value = this.value.substring(0, maxLength);
				} else {
					this.relatedElement.className = '';
				}
				var left_over = maxLength - currentLength;
				this.relatedElement.firstChild.nodeValue = left_over;
			});
	});
};

(function(jQuery) {

 	// register jQuery extension
	jQuery.extend({
		create: function(element, attributes, children) {

			// create new element
			var elem = jQuery(document.createElement(element));

			// add passed attributes
			if (typeof(attributes) == 'object') {
				for (key in attributes) {
					elem.attr(key, attributes[key]);
				}
			}

			// add passed child elements
			if (typeof(children) == 'object') {
				for (i = 0; i < children.length; i++) {
					elem.append(children[i]);
				}
			} else if (typeof(children) != 'undefined' && children != null) {
				elem.text(children.toString());
			}

			return elem;
		}
	});

})(jQuery);


//add question specific css class
jQuery(document).ready(function() {
	/*
	for (var q in questions) {
		var qType = questions[q].type;
		var cssClass =  qType.substring(3,qType.length);
		jQuery('#container'+questions[q].id).parents('.ilc_question_Standard').addClass("ilc_question_"+cssClass);
	}
	*/
});


(function(jQuery) {
	var has_VML, create_canvas_for, add_shape_to, clear_canvas, shape_from_area,
		canvas_style, fader, hex_to_decimal, css3color, is_image_loaded;

	var counter = 0;

	has_VML = document.namespaces;
	has_canvas = document.createElement('canvas');
	has_canvas = has_canvas && has_canvas.getContext;

	if(!(has_canvas || has_VML)) {
		jQuery.fn.maphilight_mod = function() { return this; };
		return;
	}
	
	// For non IE browsers!!!
	if(has_canvas) {
		
		fader = function(element, opacity, interval) {
			if(opacity <= 1) {
				element.style.opacity = opacity;
				window.setTimeout(fader, 10, element, opacity + 0.1, 10);
			}
		};
		
		hex_to_decimal = function(hex) {
			return Math.max(0, Math.min(parseInt(hex, 16), 255));
		};
		css3color = function(color, opacity) {
			return 'rgba('+hex_to_decimal(color.substr(0,2))+','+hex_to_decimal(color.substr(2,2))+','+hex_to_decimal(color.substr(4,2))+','+opacity+')';
		};
		create_canvas_for = function(img, id) {
			var width = jQuery(img).prop("width");
			var height = jQuery(img).prop("height");
			if(typeof(img.width) == "number")
			{
				width = img.width;
				height = img.height;
			}
			var c = jQuery('<canvas id="canvas_' + id + '" style="width:'+width+'px;height:'+height+'px;"></canvas>').get(0);
			c.width = width;
			c.height = height;
			c.getContext("2d").clearRect(0, 0, c.width, c.height);
			return c;
		};
		
		add_shape_to = function(canvas, shape, coords, options, name)
		{
			var i, context = canvas.getContext('2d');
			context.beginPath();
			
			if(shape == 'rect')
			{
				context.rect(coords[0], coords[1], coords[2] - coords[0], coords[3] - coords[1]);
			} 
			else if(shape == 'poly')
			{
				context.moveTo(coords[0], coords[1]);

				for(i=2; i < coords.length; i+=2)
				{
					context.lineTo(coords[i], coords[i+1]);
				}
				
			} 
			else if(shape == 'circ')
			{
				context.arc(coords[0], coords[1], coords[2], 0, Math.PI * 2, false);
			}
						
			context.closePath();
			
			if(options.fill)
			{
				context.fillStyle = css3color(options.fillColor, options.fillOpacity);
				context.fill();
			}
			if(options.stroke)
			{
				context.strokeStyle = css3color(options.strokeColor, options.strokeOpacity);
				context.lineWidth = options.strokeWidth;
				context.stroke();
			}
			if(options.fade && !jQuery.browser.msie)
			{
				fader(canvas, 0);
			}
		};
			
		clear_canvas = function(canvas, area) {
			canvas.getContext('2d').clearRect(0, 0, canvas.width,canvas.height);
		};
	} 
	// IE!!!
	else 
	{   
		var ie8=false;
		// ie executes this code
		if (document.documentMode) // IE8
		{  
			if (document.documentMode==8) {
				ie8 = true;
			}
		}
			
		if(ie8==true)
		{
			document.writeln('<?import namespace="v" implementation="#default#VML" ?>'); 
			document.namespaces.add('v', 'urn:schemas-microsoft-com:vml', "#default#VML");		
		} else {
			document.createStyleSheet().addRule("v\\:*", "behavior: url(#default#VML); antialias: true;"); 
			document.namespaces.add("v", "urn:schemas-microsoft-com:vml"); 
		}	
		
		create_canvas_for = function(img, id)
		{
			var width = jQuery(img).prop("width");
			var height = jQuery(img).prop("height");
			if(typeof(img.width) == "number")
			{
				width = img.width;
				height = img.height;
			}
			return jQuery('<var id="canvas_' + id + '" style="zoom:1;overflow:hidden;display:block;width:'+width+'px;height:'+height+'px;"></var>').get(0);
		};
		
		add_shape_to = function(canvas, shape, coords, options, name, id)
		{
			var fill, stroke, opacity, e;
					
			fill = '<v:fill color="#'+options.fillColor+'" opacity="'+(options.fill ? options.fillOpacity : 0)+'" />';
			
			stroke = (options.stroke ? 'strokeweight="'+options.strokeWidth+'" stroked="t" strokecolor="#'+options.strokeColor+'"' : 'stroked="f"');
		
			opacity = '<v:stroke opacity="'+options.strokeOpacity+'"/>';
			
			if(shape == 'rect')
			{
				e = jQuery('<v:rect id="canvas_' + id + '" name="'+name+'" filled="t" '+stroke+' style="zoom:1;margin:0;padding:0;display:block;position:absolute;left:'+coords[0]+'px;top:'+coords[1]+'px;width:'+(coords[2] - coords[0])+'px;height:'+(coords[3] - coords[1])+'px;"></v:rect>');
			} 
			else if(shape == 'poly')
			{
				e = jQuery('<v:shape id="canvas_' + id + '" name="'+name+'" filled="t" '+stroke+' coordorigin="0,0" coordsize="'+parseInt(canvas.style.width)+','+parseInt(canvas.style.height)+'" path="m '+coords[0]+','+coords[1]+' l '+coords.join(',')+' x e" style="zoom:1;margin:0;padding:0;display:block;position:absolute;top:0px;left:0px;width:'+canvas.style.width+';height:'+canvas.style.height+';"></v:shape>');
			} 
			else if(shape == 'circ')
			{
				e = jQuery('<v:oval id="canvas_' + id + '" name="'+name+'" filled="t" '+stroke+' style="zoom:1;margin:0;padding:0;display:block;position:absolute;left:'+(coords[0] - coords[2])+'px;top:'+(coords[1] - coords[2])+'px;width:'+(coords[2]*2)+'px;height:'+(coords[2]*2)+'px;"></v:oval>');
			}

			e.get(0).innerHTML = fill+opacity;
			jQuery(canvas).append(e);
		};
		
		clear_canvas = function(canvas)
		{
			jQuery(canvas).find('[name=highlighted]').remove();
		};
	}
	
	shape_from_area = function(area)
	{
		var i, coords = area.getAttribute('coords').split(',');

		for (i=0; i < coords.length; i++) { coords[i] = parseFloat(coords[i]); }
		
		return [area.getAttribute('shape').toLowerCase().substr(0,4), coords];

	};
	
	is_image_loaded = function(img) {
		if(!img.complete) { return false; } // IE
		if(typeof img.naturalWidth != "undefined" && img.naturalWidth == 0) { return false; } // Others
		return true;
	};

	canvas_style = {
		position: 'absolute',
		left: 0,
		top: 0,
		padding: 0,
		border: 0
	};
	
	jQuery.fn.maphilight_mod = function(opts) {
		opts = jQuery.extend({}, jQuery.fn.maphilight_mod.defaults, opts);
		
		return this.each(function() {
			
			var img, wrap, options, map, canvas, canvas_always, mouseover, highlighted_shape, question_id;
			img = jQuery(this);

			if(!is_image_loaded(this)) {
				// If the image isn't fully loaded, this won't work right.  Try again later.
				return window.setTimeout(function() {
					img.maphilight_mod(opts);
				}, 200);
			}

			options = jQuery.metadata ? jQuery.extend({}, opts, img.metadata()) : opts;

			map = jQuery('map[name="'+img.attr('usemap').substr(1)+'"]');

			if(!(img.is('img') && img.attr('usemap') && map.size() > 0)) { return; }

			if(img.hasClass('maphilighted_mod')) {
				// We're redrawing an old map, probably to pick up changes to the options.
				// Just clear out all the old stuff.
				var wrapper = img.parent();
				img.insertBefore(wrapper);
				wrapper.remove();
				// alert('yes');
			}

			wrap = jQuery('<div>').css({display:'block',background:'url("'+this.src+'")',position:'relative',padding:0,width:this.width,height:this.height});
			img.before(wrap).css('opacity', 0).css(canvas_style).remove();
			
			if(jQuery.browser.msie && !has_canvas) { img.css('filter', 'Alpha(opacity=0)'); }
			
			wrap.append(img);

			question_id = img.attr('usemap');			
		    question_id = question_id.substr(4);
				
			canvas = create_canvas_for(this, question_id);
			jQuery(canvas).css(canvas_style);
			
			mouseover = function(e)
			{
				var shape, area_options;
				area_options = jQuery.metadata ? jQuery.extend({}, options, jQuery(this).metadata()) : options;

				if (area_options.linked)
				{
					var thislinked = area_options.linked;
					
					jQuery(map).find('area[coords]').each(function()
					{
						var shape, area_options, object;
						area_options = jQuery.metadata ? jQuery.extend({}, options, jQuery(this).metadata()) : options;		
									
						if (thislinked == area_options.linked) {
							shape = shape_from_area(this);
							add_shape_to(canvas, shape[0], shape[1], area_options, "highlighted", null);												
						}					
																									
					});
				}
				else
				{
					shape = shape_from_area(this);
					add_shape_to(canvas, shape[0], shape[1], area_options, "highlighted", null);
				}
		
			
				//if(!area_options.alwaysOn)
				//{
				// 	shape = shape_from_area(this);
				// 	add_shape_to(canvas, shape[0], shape[1], area_options, "highlighted", null);
				//}
				
			};
			
			
			draw = function(object, target_canvas)
			{				
				var shape, area_options, object;
				area_options = jQuery.metadata ? jQuery.extend({}, options, jQuery(this).metadata()) : options;		
				
				// NON IE
				if(has_canvas)
				{
				    var arr_map = jQuery(object).attr("id").split("_");
					var str_cmap = '.cmap' + arr_map[0];
					canvas_always = create_canvas_for($(str_cmap).get(), jQuery(object).attr("id"));
					jQuery(canvas_always).css(canvas_style);
					
					$(str_cmap).before(canvas_always);
				}
									
				shape = shape_from_area(object);

				// IE!
				if (jQuery.browser.msie && !has_canvas)
				{
					add_shape_to(target_canvas, shape[0], shape[1], area_options, "", jQuery(object).attr("id"));
				} 
				else
				{
					add_shape_to(canvas_always, shape[0], shape[1], area_options, "");
				}
			};
			
			mouseclick = function(e,id)
			{				
				var shape, area_options, object;
				area_options = jQuery.metadata ? jQuery.extend({}, options, jQuery(this).metadata()) : options;	
								
				if (id) {
					object = id;
				}
				else {
					object = this;
				}
				if (!jQuery('#canvas_' + jQuery(object).attr('id')).attr('id'))
				{
					if (area_options.linked)
					{
						var thislinked = area_options.linked;
						
						jQuery(map).find('area[coords]').each(function()
						{
							var shape, area_options, object;
							area_options = jQuery.metadata ? jQuery.extend({}, options, jQuery(this).metadata()) : options;		
										
							if (thislinked == area_options.linked) {
								// alert(jQuery(this).attr('id') + ' ' + area_options.linked);
								draw(this, canvas);
							}																			
																											
						});
					}
					else
					{
						if (!questions[question_id].is_multiple) {
							// remove all areas
							for (var i=0;i<questions[question_id].answers.length;i++) {
								jQuery('#canvas_' + question_id + '_' + i).remove();
								if (jQuery(object).attr('id') != question_id + '_' + i) {
									answers[question_id].areas[i] = false;
								}
							}
							//clear_canvas(canvas);
						}

						draw(object, canvas);
					}
				}
				else
				{
					if (area_options.linked)
					{
						var thislinked = area_options.linked;
						
						jQuery(map).find('area[coords]').each(function()
						{
							var shape, area_options, object;
							area_options = jQuery.metadata ? jQuery.extend({}, options, jQuery(this).metadata()) : options;		
										
							if (thislinked == area_options.linked) {
								jQuery('#canvas_' + jQuery(this).attr('id')).remove();
							}																			
						});
						clear_canvas(canvas);
					}
					else
					{
						jQuery('#canvas_' + jQuery(object).attr('id')).remove();
						clear_canvas(canvas);	
					}
				}
			};
					
			if(options.alwaysOn) {
				jQuery(map).find('area[coords]').each(mouseover);
			} else {
				if(jQuery.metadata) {
					// If the metadata plugin is present, there may be areas with alwaysOn set.
					// We'll add these to a *second* canvas, which will get around flickering during fading.
					jQuery(map).find('area[coords]').each(function() {
												
						var shape, area_options;
						area_options = jQuery.metadata ? jQuery.extend({}, options, jQuery(this).metadata()) : options;
						
						if(area_options.alwaysOn)
						{
							draw(this, canvas);
						}
					});
				}
				jQuery(map).find('area[coords]').unbind();
				jQuery(map).find('area[coords]').mouseover(mouseover).mouseout(function(e) { clear_canvas(canvas); });
				jQuery(map).find('area[coords]').click(mouseclick); 
			}
			
			img.before(canvas); // if we put this after, the mouseover events wouldn't fire.
			img.addClass('maphilighted_mod');

			// if question was not answered correctly yet, "reload" active areas
			if(ilias.questions.answers[question_id] && ilias.questions.answers[question_id].passed != true)
			{
				for (i=0; i < ilias.questions.answers[question_id].areas.length; i++){
					if(ilias.questions.answers[question_id].areas[i] == true)
					{
						var canvas_id = question_id + '_' + i;
						if (!jQuery('#canvas_' + canvas_id).attr('id'))
						{
							var selected_area = jQuery(map).find('area[id="'+ canvas_id + '"]');
							draw(jQuery(selected_area).get(0), canvas);
						}
					}
				};
			}

		});
	};

	jQuery.fn.maphilight_mod.defaults = {
		fill: true,
		fillColor: 'ff6633',
		fillOpacity: 0.4,
		stroke: true,
		strokeColor: 'ff6633',
		strokeOpacity: 1,
		strokeWidth: 2,
		fade: true,
		alwaysOn: false
	};
})(jQuery);


function getFname(yStr){
	var sFileName = "";
	for (nloop=yStr.length-1;nloop>1;nloop--){
		if (yStr.charAt(nloop)=="/"){
			sFileName=yStr.substring(nloop+1,yStr.length);
			break;
		}
		if (yStr.charAt(nloop)=="\\"){  // backslash must be escaped
			sFileName=yStr.substring(nloop+1,yStr.length);
			break;
		}
	}
	return sFileName;
}

answers = ilias.questions.answers;
