var ilias = {}; //namespace
var icount = 0; //interaction count
ilias.UTILS={};
ilias.questions = {
	answers:Object,
	success: "not attempted"
};

ilias.questions.init = function() {
	ilias.questions.shuffle();
};

ilias.questions.shuffleAll = function() {
	//shuffle questions
	for (var k in questions) {
		if (questions[k].shuffle === true) {
			questions[k].answers.shuffle();
		}
	}	
};

ilias.questions.shuffle = function(a_question) {
	//shuffle questions
	if (!a_question.answers) {return;}
	if (a_question.shuffle === true) {
		a_question.answers.shuffle();
	}
};

ilias.questions.checkAnswers = function(a_id) {
	if (!answers[a_id]) {
		answers[a_id] = new Object();
		answers[a_id].tries = 0;
		answers[a_id].wrong = 0;
		answers[a_id].passed = null;
		answers[a_id].answer = new Array();
		answers[a_id].interactionId=null;
		
	}
	answers[a_id].tries++;
	
	var call = "ilias.questions."+questions[a_id].type+"("+a_id+")";
	
	eval(call);
};


ilias.questions.assSingleChoice = function(a_id) {

	var a_node = jQuery('input[name="answers'+a_id+'"]');
	var tocheck = "points";

	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
			
	for (var i=0;i<a_node.length;i++) {
		if ((!a_node.get(i).checked && questions[a_id].answers[i][tocheck]>0) 
			|| (a_node.get(i).checked && questions[a_id].answers[i][tocheck]<1))
		{
			answers[a_id].passed = false;
			answers[a_id].wrong++;
			answers[a_id].answer[i]=false;
			
		} else {
			answers[a_id].answer[i]=true;
		}
	}		
	
	ilias.questions.showFeedback(a_id);
};

ilias.questions.assMultipleChoice = function(a_id) {
	
	var a_node = jQuery('input[name="answers'+a_id+'"]');
	var tocheck = "points_checked";
	
	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
	
	for (var i=0;i<a_node.length;i++) {
		if ((!a_node.get(i).checked && questions[a_id].answers[i][tocheck]>0) 
			|| (a_node.get(i).checked && questions[a_id].answers[i][tocheck]<1))
		{
			answers[a_id].wrong++;
			answers[a_id].passed = false;
			answers[a_id].answer[i]=false;
			
		} else {
			answers[a_id].answer[i]=true;
		}
	}		
	ilias.questions.showFeedback(a_id);			
};

ilias.questions.assTextQuestion = function(a_id) {
	jQuery('#button'+a_id).attr("disabled", "disabled");
	jQuery('#textarea'+a_id).attr("disabled", "disabled");
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
	
	for (var i=0;i<result.length;i++) {
		if (i+1 != result[i])
		{
			answers[a_id].passed = false;
			answers[a_id].wrong ++;
			answers[a_id].answer[i]=false;
		} else {
			answers[a_id].answer[i]=true;
		}
		
	}
	ilias.questions.showFeedback(a_id);
};

ilias.questions.toggleArea = function(a_id,order) {
	answers[a_id].areas[order]=!answers[a_id].areas[order];
};

ilias.questions.initAreas = function(a_id) {
	if (!answers[a_id]) {
		answers[a_id] = new Object();
		answers[a_id].tries = 0;
		answers[a_id].wrong = 0;
		answers[a_id].passed = null;
		answers[a_id].answer = new Array();
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
		
	for (var i=0;i<questions[a_id].answers.length;i++) {
		if ((answers[a_id].areas[i]==false && questions[a_id].answers[i].points>=1) || (answers[a_id].areas[i]==true && questions[a_id].answers[i].points==0))
		{
			answers[a_id].passed = false;
			answers[a_id].wrong++;
			answers[a_id].answer[i]=false;
		} else {
			answers[a_id].answer[i]=true;
		}
	}		
	ilias.questions.showFeedback(a_id);
};

ilias.questions.assMatchingQuestion = function(a_id) {
	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
	
	for (var i=0;i<questions[a_id].pairs.length;i++)
	{
		var a_node = jQuery('select#'+questions[a_id].pairs[i].definition_id).get(0);
		if (a_node.options[a_node.selectedIndex].id!=questions[a_id].pairs[i].term_id) {
			answers[a_id].passed = false;
			answers[a_id].wrong++;
		}
	}
	ilias.questions.showFeedback(a_id);
};


ilias.questions.assClozeTest = function(a_id) {
	answers[a_id].wrong = 0;
	answers[a_id].passed = true;
	
	for (var i=0;i<questions[a_id].gaps.length;i++)
	{
		var type = questions[a_id].gaps[i].type;
		if (type==1) {
			var a_node = jQuery('select#'+a_id+"_"+i).get(0);
			var selected = a_node.options[a_node.selectedIndex].id;
			if (questions[a_id].gaps[i].item[selected].points<1) {
				answers[a_id].passed = false;
				answers[a_id].wrong++;
				answers[a_id].answer[i]=false;
			} else {
				answers[a_id].answer[i]=true;	
			}
		}
		if (type==0 || type==2) {
			var a_node = jQuery('input#'+a_id+"_"+i).get(0);
			var value_found = false;
			for(var j=0;j<questions[a_id].gaps[i].item.length;j++)
			{
				if (questions[a_id].gaps[i].item[j].value == a_node.value) {
					value_found=true;
					if (questions[a_id].gaps[i].item[j].points<1) {
						answers[a_id].passed = false;
						answers[a_id].wrong++;
						answers[a_id].answer[i]=false;
					} else {
						answers[a_id].answer[i]=true;
					}
				}
			}
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
		if (type==0 || type==2) {
			var size = (type==0) ? 20 : 4;
		 	input = jQuery.create('input', {'id': a_id+"_"+closecounter, 'type':'text', 'size':size});
		}
		if (type==1) {
			input = jQuery.create('select', {'id': a_id+"_"+closecounter});
			for (var i=0;i<questions[a_id].gaps[closecounter].item.length;i++) {
				var option = jQuery.create('option', {'id': i, 'value':i},questions[a_id].gaps[closecounter].item[i].value);
				input.append(option);
			}
		}
		closecounter++;
		return input.outerHTML();
	 };
	var parsed=jQuery("div#"+a_id).get(0).innerHTML.replace(/\[gap\][^\/]+\[\/gap\]/g,function(){return _initClozeTestCallBack();});
	jQuery("div#"+a_id).html(parsed);
};

ilias.questions.showFeedback =function(a_id) {
	
	jQuery('#feedback'+a_id).hide();
	
	if (answers[a_id].passed===true || (answers[a_id].tries >=questions[a_id].nr_of_tries && questions[a_id].nr_of_tries!=0)) {
		jQuery('#button'+a_id).attr("disabled", "true");
		if (answers[a_id].passed===true) {
			jQuery('#feedback'+a_id).removeClass("ilc_qfeedw_FeedbackWrong");				
			jQuery('#feedback'+a_id).addClass("ilc_qfeedr_FeedbackRight");	
            jQuery('#feedback'+a_id).html('<b>All answers correct!</b><br>'+questions[a_id].feedback['allcorrect']);
			ilias.questions.showCorrectAnswers(a_id);
			ilias.questions.scormHandler(a_id,"correct",ilias.questions.toJSONString(answers[a_id]));
		} else {
			jQuery('#feedback'+a_id).removeClass("ilc_qfeedr_FeedbackRight");	
			jQuery('#feedback'+a_id).addClass("ilc_qfeedw_FeedbackWrong");	
            jQuery('#feedback'+a_id).html('<b>Number of tries exceeded!</b><br> Correct answers are shown above.<br>'+questions[a_id].feedback['allcorrect']);   
			ilias.questions.showCorrectAnswers(a_id);
			ilias.questions.scormHandler(a_id,"incorrect",ilias.questions.toJSONString(answers[a_id]));
		}
	} else {
		if (questions[a_id].nr_of_tries!=0) {
			jQuery('#feedback'+a_id).removeClass("ilc_qfeedr_FeedbackRight");	
			jQuery('#feedback'+a_id).addClass("ilc_qfeedw_FeedbackWrong");	
			var rem = questions[a_id].nr_of_tries - answers[a_id].tries;
			jQuery('#feedback'+a_id).html(answers[a_id].wrong+' answer(s) not correct.<br>'+rem+" tries remaining.<br>"+questions[a_id].feedback['onenotcorrect']);
			ilias.questions.scormHandler(a_id,"incorrect",ilias.questions.toJSONString(answers[a_id]));
		} else {
			jQuery('#feedback'+a_id).removeClass("ilc_qfeedr_FeedbackRight");	
			jQuery('#feedback'+a_id).addClass("ilc_qfeedw_FeedbackWrong");
            jQuery('#feedback'+a_id).html(answers[a_id].wrong+' answer(s) not correct.<br> Please try again'+questions[a_id].feedback['onenotcorrect']);
			ilias.questions.scormHandler(a_id,"incorrect",ilias.questions.toJSONString(answers[a_id]));
		}	
	}
	jQuery('#feedback'+a_id).slideToggle();
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
	//set course success status
	var status="passed";
	for (var k in questions) {
		var index=parseInt(k,10);
		if (!isNaN(index)) {
			if (!answers[index]) {
				status = "failed";
			} else {
				if (answers[index].passed!=true) {status="failed";}
			}
		}
	}
	if (status=="passed" || ScormApi.version=="1.3" ) {
		setValue(s_key,status);
	}	
};

ilias.questions.showCorrectAnswers =function(a_id) {
	
	switch (questions[a_id].type) {
		case 'assSingleChoice':	
			for (var i=0;i<questions[a_id].answers.length;i++) {
				if (questions[a_id].answers[i].points>=1) {
					jQuery('input[name="answers'+a_id+'"]:nth('+i+')').attr("checked",true);
				} else {
					jQuery('input[name="answers'+a_id+'"]:nth('+i+')').attr("checked",false);
				}
				jQuery('input[name="answers'+a_id+'"]:nth('+i+')').attr("disabled",true);
			}
			break;
		   //end assSingleChoice
		
		case 'assMultipleChoice':	
			for (var i=0;i<questions[a_id].answers.length;i++) {
				if (questions[a_id].answers[i].points_checked>=1) {
					jQuery('input[name="answers'+a_id+'"]:nth('+i+')').attr("checked",true);
				} else {
					jQuery('input[name="answers'+a_id+'"]:nth('+i+')').attr("checked",false);
				}
				jQuery('input[name="answers'+a_id+'"]:nth('+i+')').attr("disabled",true);
				
			}
			break;
			//end assSingleChoice
			
		case 'assImagemapQuestion': 
			//reinit map
			jQuery(function() {
		  		jQuery('.cmap'+a_id).maphilight({fade:true});
			});
			for (var i=0;i<questions[a_id].answers.length;i++) {
				if (questions[a_id].answers[i].points>=1) {
					mouseclick(null,document.getElementById(a_id+"_"+questions[a_id].answers[i].order));
				}
			}
			break;
		   	//end assImagemapQuestion
		
		case 'assOrderingQuestion':
			var answers = questions[a_id].answers;
			var answers_sorted = answers.sort(sortBySolutionorder);
			var items=jQuery("#order"+a_id).children();
			for (var i=0;i<items.length;i++) {
				var j=i+1;
				jQuery("#order"+a_id +" li:nth-child("+j+") div").html(answers_sorted[i].answertext);
			}
			jQuery("#order"+a_id).sortable("disable");
		break;
		//end assOrderingQuestion

		case 'assMatchingQuestion':
			for (var i=0;i<questions[a_id].pairs.length;i++) {
				jQuery('select#'+questions[a_id].pairs[i].definition_id).removeAttr("selected");
				jQuery('select#'+questions[a_id].pairs[i].definition_id+" option[id="+questions[a_id].pairs[i].term_id+"]").attr("selected","selected");
				jQuery('select#'+questions[a_id].pairs[i].definition_id).attr("disabled",true);
			}
		break;
		//end assMatchingQuestion
		case 'assClozeTest':
			for (var i=0;i<questions[a_id].gaps.length;i++) {
				var type = questions[a_id].gaps[i].type;
				if (type==1) {
					var cid;
					jQuery('select#'+a_id+"_"+i).attr("disabled",true);
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
					jQuery('input#'+a_id+"_"+i).attr("disabled",true);
					//look for correct solution
						for (var j=0;j<questions[a_id].gaps[i].item.length;j++)
						{
							if (questions[a_id].gaps[i].item[j].points>=1)
							{
								cvalue = questions[a_id].gaps[i].item[j].value;
							}
						}
					jQuery('input#'+a_id+"_"+i).attr("value",cvalue);
				}
			}
		break;
		//end assMatchingQuestion
	}
};

function sortBySolutionorder(thisObject,thatObject) {	
	if (thisObject.solutionorder > thatObject.solutionorder) {
		return 1;
	}
	else if (thisObject.solutionorder < thatObject.solutionorder) {
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

//Array additions
(function () {
	var swapper =
		function (a,L,e) {
			var r = Math.floor(Math.random()*L);
			var x = a[e];
			a[e] = a[r];
			a[r] = x;
		};
	Array.prototype.shuffle =
		function () {
			var i,L;
			i = L = this.length;
			while (i--) swapper(this,L,i);
		};
})();

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
		jQuery.fn.maphilight = function() { return this; };
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
		create_canvas_for = function(img) {
			var c = jQuery('<canvas id="canvas_' + jQuery(this).attr("id") + ' style="width:'+img.width+'px;height:'+img.height+'px;"></canvas>').get(0);
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
			if(options.fade)
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
		// ie executes this code
		document.createStyleSheet().addRule("v\\:*", "behavior: url(#default#VML); antialias: true;"); 
		document.namespaces.add("v", "urn:schemas-microsoft-com:vml"); 
			
		create_canvas_for = function(img)
		{
			return jQuery('<var id="iemainvmlcontainer" style="zoom:1;overflow:hidden;display:block;width:'+img.width+'px;height:'+img.height+'px;"></var>').get(0);
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
   			e = jQuery('<v:shape id="canvas_' + id + '" name="'+name+'" filled="t" '+stroke+' coordorigin="0,0" coordsize="'+canvas.width+','+canvas.height+'" path="m '+coords[0]+','+coords[1]+' l '+coords.join(',')+' x e" style="zoom:1;margin:0;padding:0;display:block;position:absolute;top:0px;left:0px;width:'+canvas.width+'px;height:'+canvas.height+'px;"></v:shape>');
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
	
	jQuery.fn.maphilight = function(opts) {
		opts = jQuery.extend({}, jQuery.fn.maphilight.defaults, opts);
		
		return this.each(function() {
			
			var img, wrap, options, map, canvas, canvas_always, mouseover, highlighted_shape;
			img = jQuery(this);
		
			if(!is_image_loaded(this)) {
				// If the image isn't fully loaded, this won't work right.  Try again later.
				return window.setTimeout(function() {
					img.maphilight(opts);
				}, 200);
			}

			options = jQuery.metadata ? jQuery.extend({}, opts, img.metadata()) : opts;

			map = jQuery('map[name="'+img.attr('usemap').substr(1)+'"]');

			if(!(img.is('img') && img.attr('usemap') && map.size() > 0)) { return; }

			if(img.hasClass('maphilighted')) {
				// We're redrawing an old map, probably to pick up changes to the options.
				// Just clear out all the old stuff.
				var wrapper = img.parent();
				img.insertBefore(wrapper);
				wrapper.remove();
				//alert('yes');
			}

			wrap = jQuery('<div>').css({display:'block',background:'url('+this.src+')',position:'relative',padding:0,width:this.width,height:this.height});
			img.before(wrap).css('opacity', 0).css(canvas_style).remove();
			
			if(jQuery.browser.msie) { img.css('filter', 'Alpha(opacity=0)'); }
			
			wrap.append(img);
				
			canvas = create_canvas_for(this);
			jQuery(canvas).css(canvas_style);
			canvas.height = this.height;
			canvas.width = this.width;
			canvas.id = this.id;
					
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
			
			
			draw = function(object)
			{
				var shape, area_options, object;
				area_options = jQuery.metadata ? jQuery.extend({}, options, jQuery(this).metadata()) : options;		
				
				// NON IE
				if(has_canvas)
				{
					canvas_always = create_canvas_for(img.get());
				
					jQuery(canvas_always).css(canvas_style);
					canvas_always.width = img.width();
					canvas_always.height = img.height();
					canvas_always.id = 'canvas_' + jQuery(object).attr("id");
														
					img.before(canvas_always);
				}
									
				shape = shape_from_area(object);
				
				// IE!
				if (jQuery.browser.msie)
				{
					add_shape_to(canvas, shape[0], shape[1], area_options, "", jQuery(object).attr("id"));
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
								draw(this);				
							}																			
																											
						});
					}
					else
					{
						draw(object);
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
							draw(this);							
						}
					});
				}
				jQuery(map).find('area[coords]').mouseover(mouseover).mouseout(function(e) { clear_canvas(canvas); });
				jQuery(map).find('area[coords]').click(mouseclick); 
			}
			
			img.before(canvas); // if we put this after, the mouseover events wouldn't fire.
			img.addClass('maphilighted');
		});
	};

	jQuery.fn.maphilight.defaults = {
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
