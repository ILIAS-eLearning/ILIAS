var lms = {};

var lmstatus = {
	0 : "not_attempted",
	1 : "in_progress",
	2 : "completed",
	3 : "failed"
};

var som;


Number.prototype.toHHMMSS = function () {
	var 	seconds = Math.floor(this),
		hours = Math.floor(seconds / 3600);
		seconds -= hours*3600;
    
	var minutes = Math.floor(seconds / 60);
	seconds -= minutes*60;

	if (hours   < 10) {hours   = "0"+hours;}
	if (minutes < 10) {minutes = "0"+minutes;}
	if (seconds < 10) {seconds = "0"+seconds;}
	return hours+':'+minutes+':'+seconds;
}

$( document ).ready( function() {
	som = (function() {
		var _ = function(txt) { // ToDo: language support
			return txt;
		};
		
		var mts = {
				lms : '<div class="accordion-group" id="acgLm"><div class="accordion-heading"><img id="status___#VAR_CRSID__" class="status" src="__#VAR_STATUSICON__" title="__#VAR_STATUSTITLE__"/><a href="#" onclick="som.openLm(this.id);return false" id="__#VAR_CRSID__" class="somCourseTitle" target="_blank">__#VAR_TITLE__</a><button id="lp___#VAR_CRSID__" style="display:__#VAR_SHOWLP__" type="button" class="btn btn-small" data-toggle="collapse" data-target="#__#VAR_CRSDETAILS__"><span>__#LNG_learningprogress__</span>&nbsp;<b class="caret"></b></button><br /><span id="somCourseDescription">__#VAR_DESCRIPTION__</span></div><div id="__#VAR_CRSDETAILS__" class="accordion-body collapse"><div class="accordion-inner" id="aciLm">__#VAR_LM__</div></div></div>',
				lm : '<table><thead></thead><tbody><tr><th>__#LNG_totaltime__</th><td>__#VAR_TOTALTIME__</td></tr><tr><th>__#LNG_attempts__</th><td>__#VAR_ATTEMPTS__</td></tr><tr><th>__#LNG_firstaccess__</th><td>__#VAR_FIRSTACCESS__</td></tr><tr><th>__#LNG_lastaccess__</th><td>__#VAR_LASTACCESS__</td></tr><tr><th>__#LNG_percentagecompleted__</th><td>__#VAR_PERCENTAGECOMPLETED__</td></tr><tr><th>__#LNG_status__</th><td>__#VAR_STATUS__</td></tr></tbody></table>'
			};
		
		var init = function() {
			log("som init");
			getLms();
		};
		
		var getLms = function() {
			log("getLms");
			lms = {};

			var lmDb = new PouchDB('lm',{auto_compaction:true, revs_limit: 1});
			var sahsUserDb = new PouchDB('sahs_user',{auto_compaction:true, revs_limit: 1});
			var remoteCouch = false;
			lmDb.allDocs({include_docs: true, descending: true}).then( function(doc) 
			{
				for (var i=0; i<doc.total_rows; i++) 
				{
					// console.log("lmrow"+i+": "+doc.rows[i].doc.title);
					// var lm_id = doc.rows[i].doc._id;
					var lm = {};
					var row = doc.rows[i].doc;
					var lm_id = row.client+'_'+row.obj_id;
					lm['obj_id'] = row.obj_id;
					lm['client'] = row.client;
					lm['title'] = row.title;
					lm['description'] = row.description;
					lm['scorm_version'] = row.scorm_version;
					log(JSON.stringify(lm));
					lms[lm_id] = lm;
				}
			}).then( function() 
			{
				lmDb.close();
				sahsUserDb.allDocs({include_docs: true, descending: true}, function(err, doc) {
					for (var i=0; i<doc.total_rows; i++) {
						var row = doc.rows[i].doc;
						console.log(JSON.stringify(row));
						var checkid = row.client+"_"+row.obj_id;
						lms[checkid]['first_access'] = row.first_access;
						lms[checkid]['last_access'] = row.last_access;
						lms[checkid]['total_time_sec'] = row.total_time_sec;
						lms[checkid]['sco_total_time_sec'] = row.sco_total_time_sec;
						lms[checkid]['status'] = row.status;
						lms[checkid]['package_attempts'] = row.package_attempts;
						lms[checkid]['percentage_completed'] = row.percentage_completed;
					}
				}).then(function(response) {
					sahsUserDb.close();
					log(JSON.stringify(lms));
					return renderAllLm();
				}).catch( function(err) 
				{
					console.error(err);
				});
					
			}).catch( function(err) 
			{
				console.error(err);
			});
			
			return true;

		};
		
		
		var openLm = function(lm) {
			lmtmp = lm.split('_');
			log("openLm: "+lm);
			var player = (lms[lm].scorm_version == "1.2") ? somGlobals.player12_url : somGlobals.player2004_url;
			open(player,"client="+lmtmp[0]+"&obj_id="+lmtmp[1]);
		};
		
		var renderAllLm = function () {
			var str = "";
			var tmp = mts.lms;
			
			for (var _lm in lms) {
				var lm = lms[_lm];
				var lm_id = lm.client + "_" + lm.obj_id; 
				// var id = lm.obj_id;
				
				// var player = (lm.scorm_version == "1.2") ? "player12.html" : "player2004.html"
				// var url = "http://localhost:50012/" + player + "?client=" + lm.client + "&obj_id=" + lm.obj_id;
				
				var stat = (undefined === lm.status || lm.status===null || lm.status == "") ? 0 : lm.status;
				var st = (undefined !== lmstatus[stat]) ? lmstatus[stat] :  lmstatus[1];
				var showlp = "inline";//(lm.learning_progress_enabled == 1) ? "inline" : "none"; 
				//utils.log(JSON.stringify(lm));
				
				var data = {
					CRSID 		: lm_id,
					SHOWLP		: showlp,
					TITLE 		: lm.title,
					DESCRIPTION	: lm.description,
					CRSDETAILS 	: "detail_" + lm_id,
					STATUSICON	: "./templates/default/images/scorm/" + st + ".png",
					STATUSTITLE	: st
				}
				str += getTemplateContent(tmp,data);
				str = renderLm(lm_id,str);
			}
			
			if (str == "") {
				str = _("no_data");
			}
			//log(str);
			$('#acLm').html(str);
			return true;
		};
		
		var renderLm = function renderLm(id,str=false) {
			var lm = lms[id];
			var tmp = mts.lm;
			var time = (lm.sco_total_time_sec > 0) ? secondsToTime(lm.sco_total_time_sec) : "not set";
			var attempts = (lm.status == 0) ? "" : lm.package_attempts;
			var firstaccess = lm.first_access;
			var lastaccess =  lm.last_access;
			if (typeof firstaccess == "number") {
				var dl = new Date(firstaccess);
				firstaccess = dl.toLocaleString(); // ToDo: Safari Compat
			}
			if (typeof lastaccess == "number") {
				var dl = new Date(lastaccess);
				lastaccess = dl.toLocaleString(); // ToDo: Safari Compat
			}
			
			var status = (undefined !== lmstatus[lm.status]) ? _(lmstatus[lm.status]) : _(lmstatus[1]);
			var data = {
				TOTALTIME 			: time + " (hh:mm:ss)",
				ATTEMPTS			: attempts,
				FIRSTACCESS			: firstaccess,
				LASTACCESS			: lastaccess,
				PERCENTAGECOMPLETED		: lm.percentage_completed+"%",
				STATUS				: status
			};
			tmp = getTemplateContent(tmp,data);
			log()
			if (str) { // comming from renderAllLm
				data = { LM : tmp };
				return getTemplateContent(str,data);
			}
			else {
				try {
					$("#aciLm").html(tmp);
				}
				catch(e) {
					log(e);
				}
			}
		};
		
		// utils
		var getTemplateContent = function (str,data) {
			return str.replace(/__\#VAR_([a-zA-Z]+)__/g, function(a,b){return(undefined===data[b])?a:data[b]}).replace(/__\#LNG_([a-zA-Z]+)__/g,function(a,b){return _(b);});
		};
		
		var log = function(txt) {
			console.log(txt);
		};
		
		var secondsToTime = function (seconds) {
			return parseInt(seconds).toHHMMSS();
		};
		
		var toLocaleStringSupportsOptions = function () {
			return !!(typeof Intl == 'object' && Intl && typeof Intl.NumberFormat == 'function');
		};
		
		return {
			init : init,
			openLm : openLm,
			renderAllLm : renderAllLm,
			log : log
		};
	}());
	som.init();
});
