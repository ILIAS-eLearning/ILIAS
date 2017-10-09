/**
 * script for managing offline slm experience
 */ 

var sop;

var gui; // compatibility
$( document ).ready( function() {
	sop = (function() {
		var init = function () {
			log("player: init");
			gui = sop;
			initPlayer();
		};
		
		var log = function(txt) {
			console.log(txt);
		};
		
		var getPlayerParams = function(win) { // getPlayerParams fromm window name
			log("player: getPlayerParams");
			var qs = {};
			var queryString;
			queryString = win.name;
			var re = /([^&=]+)=([^&]*)/g;
			var m;
			while (m = re.exec(queryString)) {
				qs[m[1]] = m[2];
			}
			return qs;
		};
		
		// var getData = function(key,data) {
			// log("getData");
			// log("key: " + key);
			// log("data: " + data);
			// var ret = '';
			// switch (key) {
				// case "lmGetAllByClientAndObjIdAtInitOfPlayer" :
					// log("lmGetAllByClientAndObjIdAtInitOfPlayer");
					// ret = '{"data": "data"}';
				// break;
				// default :
					// ret = '{}';
					// log("no data key:" + key);
			// }
			// return ret;
		// };
		
		// var setData = function(key,data) {
			
		// };
		var getData = function(statement, params, asJSONObject=true) {

			var ret = false, rec = null;
			ret = dbData(statement, params);
			if (ret) {
				return (asJSONObject) ? ret : JSON.stringify(ret);
			}
			// utils.err("db error: " + dbHandler.getLastErrorId() + ": " + dbHandler.getLastError()); // ToDo: get db error
			return false;
		}

		var setData = function(statement, params) {
			return dbData(statement, params);
		}


		var dbData = function(statement, params) {
			if (params != null && params !="") {
				for (var i=0; i<params.length; i++) {
					if (typeof params[i] == "object" || typeof params[i] == "array") {
						if (statement!="setCMIData") params[i] = JSON.stringify(params[i]);
					}
				}
			}
			switch (statement) { // ToDo: define propper views and params in sqlite and get data with getView(view,params)

				case "lmGetAllByClientAndObjIdAtInitOfPlayer" :
					var this_id = params[0] + '_' + params[1]; //client + obj_id
					var remoteCouch = false;
					var db = new PouchDB('sahs_user',{auto_compaction:true, revs_limit: 1});
					db.get(this_id).then(function(res){
						//update data for sahs_user
						if (res.package_attempts == null) res.package_attempts = 1;
						else res.package_attempts = parseInt(res.package_attempts)+1;
						// console.log(res.package_attempts);
						if (res.first_access == null) {
								var d_now = new Date();
								res.first_access = d_now.getTime();
						}
						if (res.status == null) res.status = 1;
						db.put(res);
						//get data for tracking
						var cmi = [];
						db = new PouchDB('scorm_tracking_'+this_id);
						db.allDocs({include_docs: true, descending: true}).then(function(trackdata){
							for (var i=0; i<trackdata.total_rows; i++) {
								var left = trackdata.rows[i].doc._id;
								var ileft = left.indexOf('_');
								cmi[i] = [ left.substr(0,ileft) , left.substr(ileft+1) , trackdata.rows[i].doc.rvalue ];
							}
							//get data for lm
							db = new PouchDB('lm');
							db.get(this_id).then(function(result){
								result.module_version = res.module_version;
								result.user_data = res.user_data;
								result.last_visited = res.last_visited;
								result.status = res.status;
								result.cmi = cmi;
								
								console.log('data for statement ' + statement + ' with params '+ JSON.stringify(params) + ': ' +JSON.stringify(result));
								db.close();
								IliasScormVars=result.init_data;
								IliasScormVars.launchId=result.last_visited;
								IliasScormVars.status.saved_global_status=result.status;
								IliasScormVars.dataDirectory='../../../../data/'+params[0]+'/lm_data/lm_'+params[1]+'/';
								IliasScormResources=result.resources;
								if (IliasScormResources.length==1){
									document.getElementById("leftView").style.width="0";
									document.getElementById("dragbar").style.left="0";
									document.getElementById("tdResource").style.left="0";
								}
								if (IliasScormVars.b_debug==true) {
									document.getElementsByName("sahs_content")[0].style.height="70%";
									document.getElementsByName("logframe")[0].style.height="30%";
								}
								IliasScormTree = result.scorm_tree;
								IliasScormData = result.cmi;
								//IliasScormData = JSON.parse(gui.getData("getScormTracking",[params.client,params.obj_id],false));
								API=new iliasApi();
								return true;
							});
						});
					}).catch(function (err) {
						db.close();
						log('error for statement ' + statement + ' with params '+ JSON.stringify(params) + ': ' + err);
						return false;
					});
				break;

				case "setSCORM12data" :

					function retryUntilWritten(doc) {
						return dbtr.get(doc._id).then(function (origDoc) {
							doc._rev = origDoc._rev;
							return dbtr.put(doc);
						}).catch(function (err) {
							if (err.status === 409) {
								return retryUntilWritten(doc);
							} else { // new doc
								return dbtr.put(doc);
							}
						});
					}

					var this_id = params[0] + '_' + params[1]; //client + obj_id
					var data=JSON.parse(params[2]);
					var d_now = new Date();
					var remoteCouch = false;
					//set data for tracking
					var dbtr = new PouchDB('scorm_tracking_'+this_id,{auto_compaction:true, revs_limit: 1, cache : false});
					for(var i=0; i<data.cmi.length; i++) {
						var a_d=data.cmi[i];
						var tmp = retryUntilWritten({_id:a_d[0]+'_'+a_d[1], rvalue:encodeURIComponent(a_d[2]) });
					}
					
					var db = new PouchDB('sahs_user',{auto_compaction:true, revs_limit: 1});
					db.get(this_id).then(function(res){
						//update data for sahs_user
						if (data.now_global_status != data.saved_global_status) {
							res.last_status_change = d_now.getTime();
						}
						if (typeof data.percentageCompleted == "number") {
							res.percentage_completed = Math.round(data.percentageCompleted);
						}
						if (typeof data.totalTimeCentisec == "number") {
							res.sco_total_time_sec = Math.round(data.totalTimeCentisec/100);
						}
						res.last_access = d_now.getTime();
						res.status = data.now_global_status;
						db.put(res).then(function(response) {
							db.close();
						});
					}).catch(function (err) {
						db.close();
						log('error writing sahs_user for statement ' + statement + ' with params '+ JSON.stringify(params) + ': ' + err);
					});
					return "ok";
					// emit(exports, "lmUserDataChanged", client+"_"+obj_id);
				break;

				case "scormPlayerUnload" :
					var this_id = params[0] + '_' + params[1]; //client + obj_id
					var d_now = new Date();
					var remoteCouch = false;
					var db = new PouchDB('sahs_user',{auto_compaction:true, revs_limit: 1});
					db.get(this_id).then(function(res){
						res.last_access = d_now.getTime();
						res.last_visited = params[2];
						db.put(res).then(function(response) {
							db.close();
						});
					}).catch(function (err) {
						db.close();
						log('error writing sahs_user for statement ' + statement + ' with params '+ JSON.stringify(params) + ': ' + err);
					});
					return "ok";
				break;

				default :
					log('no data for statement ' + statement + ' with params '+ JSON.stringify(params));
					return {};
			}
		}
		
		return {
			init 				: init,
			log				: log,
			getPlayerParams			: getPlayerParams,
			getData				: getData,
			setData				: setData
		};
	}());
	sop.init();
});
