$(document).ready(function() {
	String.prototype.padLeft = function padLeft(length, leadingChar) {
		if (leadingChar === undefined) leadingChar = "0";

		return this.length < length ? (leadingChar + this).padLeft(length, leadingChar) : this;
	};

	$(document).on("keyup", function(e) {
		if(e.which == 27) {
			gevHideMailPreview();
		}
	});

	$(document).on('change','select[id^="date"], select[id^="time"], select[id^="start_date"], select[id^="end_date"]', function(){
		if(isCreatingCourseBuildingBlock()) {
			$('.titleCommand a.submit').removeClass("cboxElement");
			$('.titleCommand a.submit').addClass("submitDisabled");
			$('.titleCommand a.submit').removeClass("submit");
			$(document).on("click",'.titleCommand a.submitDisabled',function(){
				return false;
			});

			//$('#form_dct_navi input').attr("disabled",true);
		}
	});

	var colboxSettings = {
			href:"#test",
			inline:true,
			overlayClose: false,
			closeButton: false,
			scrolling: false,
			opacity: 0.5,
			onOpen : gevShowMailPreview
		};

		$('input.submit').each(function(k,v){
			var att = $(v).attr("name");
			if(att == "cmd[]") {
				$(v).colorbox(colboxSettings);
			}
		});

		gevHideMailPreview();
});

//kontrolliert ob eine bestimme klasse auf dem form vorhanden ist
function isCreatingCourseBuildingBlock() {
		return $('#dct-no_form_read').length;
}

function gevShowMailPreview(){
	var crs_data = "";
	var readForm = true;
	var files = [];

	readForm = !isCreatingCourseBuildingBlock();

	var crs_ref_found = $('div[class^="crs_ref_id"]').length;
	var crs_request_found = $('div[class^="crs_request_id"]').length;
	var crs_tpl_found = $('div[class^="crs_template_id"]').length;

	//stellt gest ob eine CRS_REF_ID existiert zum laden von Daten
	if(crs_ref_found) {
		var crs_ref = $('div[class^="crs_ref_id"]').attr("class");
		crs_ref = crs_ref.split("_");
		crs_data = "crs_ref_id="+crs_ref[3];
	}
	//

	//stellt fest ob eine CRS_REQUEST_ID existiert zum laden von daten
	if(crs_request_found) {
		var crs_req = $('div[class^="crs_request_id"]').attr("class");
		crs_req = crs_req.split("_");
		crs_data = "crs_request_id="+crs_req[3];
	}

	//stellt fest ob eine CRS_REQUEST_ID existiert zum laden von daten
	if(crs_tpl_found) {
		var crs_tpl = $('div[class^="crs_template_id"]').attr("class");
		crs_tpl = crs_tpl.split("_");
		crs_data = "crs_template_id="+crs_tpl[3];
	}

	if(readForm) {
		var values = [];

		values["TRAININGSTYP"] = $('input[name=ltype]').val();

		if($('#title').is("span")) {
			values["TRAININGSTITEL"] = $('#title').html();
		} else if($('#title').is("input:text")) {
			values["TRAININGSTITEL"] = $('#title').val();
		} else {
			values["TRAININGSTITEL"] = "";
		}

		values["STARTDATUM"] = $('#date\\[date\\]_d').val().padLeft(2,"0") + "." + $('#date\\[date\\]_m').val().padLeft(2,"0") + "." + $('#date\\[date\\]_y').val();
		values["ZEITPLAN"] = $('#time\\[start\\]\\[time\\]_h').val().padLeft(2,"0") + ":" + $('#time\\[start\\]\\[time\\]_m').val().padLeft(2,"0") + "-" + $('#time\\[end\\]\\[time\\]_h').val().padLeft(2,"0") + ":" + $('#time\\[end\\]\\[time\\]_m').val().padLeft(2,"0");
		
		if(typeof tinyMCE !== 'undefined') {
			var org_info = tinyMCE.activeEditor.getContent();
			org_info = org_info.replace("<p>","").replace("</p>","");
			values["ORGANISATORISCHES"] = org_info;
		} else {
			values["ORGANISATORISCHES"] = $('#orgaInfo').val();
		}

		values["WEBINAR-LINK"] = $('#webinar_link').val();
		values["WEBINAR-PASSWORT"] = $('#webinar_password').val();
		
		if($('#webinar_vc_type').length){
			values["VC-TYPE"] = $('#webinar_vc_type').val();
		}
		
		var target_groups = $('input[name=target_groups\\[\\]]');
		var tg_string = "";
		$.each(target_groups,function(k,v){
			if($(v).attr("checked")) {
				if(tg_string !== "") {
					tg_string += ", ";
				}

				tg_string += $(v).val();
			}
		});
		values["ZIELGRUPPEN"] = tg_string;

		var files_input = $('input[name=attachment_upload\\[\\]');
		$.each(files_input, function(k,v) {
			var str = $(v).val();
			var res = str.split("\\");
			var file = res[$(res).size()-1];

			if(file !== '') {
				files.push(res[$(res).size()-1] + " (wird nach dem Speichern angehangen)");
			}
		});

		var trainer_ids = $('#trainer_ids').val();
		
		var venue = $('#venue').val();

		if(venue !== "0") {
			crs_data += "&venue_id=" + venue;
		} else {
			values["VO-NAME"] = $('#venue_free_text').val();
		}

		if(crs_tpl_found) {
			crs_data += "&trainer_ids=" + trainer_ids;
		}
	}
	
	if(crs_data !== "") {
		$.getJSON(il.mail_data_json_url,crs_data, function(data) {
			if(readForm) {
				$.each(data, function(k,v) {
					if(k in values) {
						data[k] = values[k];
					}
				});
			}
			
			var html = getPlaceholderText();
			var trainers = data["ALLE TRAINER"].split("|");
			data["ALLE TRAINER"] = trainers.join("<br />");
			
			if(html === "") {
				$('#dct-mail_content .mail').html("Es wurde keine Mailvorlage angelegt!");
			} else {
				$.each(data, function(k,v){
					var find = "\\["+k+"\\]";
					var re = new RegExp(find, 'g');
					
					if(v === null) {
						html = html.replace(re, "");
					} else {
						html = html.replace(re, v);
					}
					
				});

				$('#dct-mail_content .mail').html(html);
			
				if("ATTACHMENTS" in data) {
					files = $.merge(data["ATTACHMENTS"], files);
				}

				if($(files).size() > 0) {
					var text = files.join("<br />");
					$('#dct-mail_content .attachment_content').html(text);
				}
			}
		});
	}

	$('#test').hide().show(0);
}

function gevHideMailPreview(){
	$.colorbox.close();
	$('#test').css('display', "none");
	//$('div[id^="dct-mail_template_"]').css('display', "none");
}

function getPlaceholderText() {
	return $('#dct-mail_template_base').html();
}
