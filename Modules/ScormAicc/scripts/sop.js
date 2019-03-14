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

		var retryUntilWritten = function(table,id,values) {
			var dbtr = new PouchDB(table,{auto_compaction:true, revs_limit: 1, cache : false});
			var remoteCouch = false;
			return dbtr.get(id).then(function (doc) {
				for (var i=0; i<values.length; i++) {
					doc[values[i][0]] = values[i][1];
				}
				return dbtr.put(doc);
			}).catch(function (err) {
				if (err.status === 409) {
					return retryUntilWritten(table,id,values);
				} else { // new doc
					var doc = {};
					doc._id = id;
					for (var i=0; i<values.length; i++) {
						doc[values[i][0]] = values[i][1];
					}
					return dbtr.put(doc);
				}
			});
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
								IliasScormVars.dataDirectory='./data/'+params[0]+'/lm_data/lm_'+params[1]+'/';
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

				case "lmGetAllByClientAndObjIdAtInitOfPlayer2004" :
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
						db = new PouchDB('cmi_node_'+this_id);
						db.allDocs({include_docs: true, descending: true}).then(function(trackdata){
							for (var i=0; i<trackdata.total_rows; i++) {
								cmi[i] = trackdata.rows[i].doc.value;
							}
							//get data for lm
							db = new PouchDB('lm');
							db.get(this_id).then(function(result){
								result.module_version = res.module_version;
								result.user_data = res.user_data;
								result.last_visited = res.last_visited;
								result.status = res.status;
								result.cmi = cmi;
								
								//console.log('data for statement ' + statement + ' with params '+ JSON.stringify(params) + ': ' +JSON.stringify(result));
								db.close();
								var init_data=result.init_data;
								init_data.sequencing_enabled=false;
								init_data.status.last_visited=result.last_visited;
								init_data.status.saved_global_status=result.status;
								init_data.package_url='data/'+params[0]+'/lm_data/lm_'+params[1]+'/';
								//init_data.specialpage_url='./specialPage.html?message=';
								init_adlact_data=result.adlact_data;
								init_data.langstrings=langstrings;
								init_cp_data=result.resources;
								var ret={
									"schema":{},
									"data":{
										"package":[],
										"node":[],
										"comment":[],
										"correct_response":[],
										"interaction":[],
										"objective":[]
									}
								};
								ret.schema={"package":["user_id","learner_name","slm_id","mode","credit"],"node":["accesscount","accessduration","accessed","activityAbsoluteDuration","activityAttemptCount","activityExperiencedDuration","activityProgressStatus","attemptAbsoluteDuration","attemptCompletionAmount","attemptCompletionStatus","attemptExperiencedDuration","attemptProgressStatus","audio_captioning","audio_level","availableChildren","cmi_node_id","completion","completion_status","completion_threshold","cp_node_id","created","credit","delivery_speed","entry","exit","language","launch_data","learner_name","location","max","min","mode","modified","progress_measure","raw","scaled","scaled_passing_score","session_time","success_status","suspend_data","total_time","user_id"],"comment":["cmi_comment_id","cmi_node_id","comment","timestamp","location","sourceIsLMS"],"correct_response":["cmi_correct_response_id","cmi_interaction_id","pattern"],"interaction":["cmi_interaction_id","cmi_node_id","description","id","latency","learner_response","result","timestamp","type","weighting"],"objective":["cmi_interaction_id","cmi_node_id","cmi_objective_id","completion_status","description","id","max","min","raw","scaled","progress_measure","success_status","scope"]};
								ret.data.node=result.cmi;
								init_cmi_data=ret;
								ret=null;
								init_globalobj_data={};
								// console.log(JSON.stringify(init_data));
								scorm_init(init_data);
								return true;
							});
						});
					}).catch(function (err) {
						db.close();
						log('error for statement ' + statement + ' with params '+ JSON.stringify(params) + ': ' + err);
						return false;
					});
				break;
				
				case "setCMIData" :
					// var client=params[0], obj_id=params[1], userId=params[2], data=JSON.parse(params[3]);
					var client=params[0], obj_id=params[1], userId=params[2], data=params[3];
					var getComments=true, getInteractions=true, getObjectives=true;
					var this_id = params[0] + '_' + params[1];
					var remoteCouch = false;
					var result = {};
					if (!data) return;
					var i_check=data.i_check;
					var i_set=data.i_set;
					var b_node_update=false;
					var cmi_node_id=null;
					var a_map_cmi_interaction_id=[];
					var tables = ['node', 'comment', 'interaction', 'objective', 'correct_response'];
					var table,row,tableData,q,cmi_interaction_id,a_ids,i,new_global_status,saved_global_status;

					for (var tablecounter=0; tablecounter<tables.length; tablecounter++) {
						table=tables[tablecounter];
						if (typeof data[table] == "undefined" || data[table] == []) {
							break;
						}
						tableData=data[table];
						console.log("setCMIData, table: "+table+", tableData: "+tableData);
						for (var rowcounter=0; rowcounter<tableData.length; rowcounter++) {
							row=tableData[rowcounter];
							switch(table)
							{
								case 'node':
								// dat[i]={_id: cmi[i][15], value: cmi[i]}; //id=cmi_node_id
									var tmp = retryUntilWritten(
										'cmi_node_'+this_id,
										""+row[15], //id=cmi_node_id
										[["value",row]]
									);
								break;
							}
						}
					}
					//generate return object
					new_global_status = data.now_global_status;
					saved_global_status=data.saved_global_status;
					result["new_global_status"]=new Object();
					result["new_global_status"]=new_global_status;
					var d_now = new Date();
					//update data for sahs_user
					var a_change = [];
					var db = new PouchDB('sahs_user',{auto_compaction:true, revs_limit: 1, cache : false});
					db.get(this_id).then(function(res){
						if (data.now_global_status != data.saved_global_status) {
							a_change.push([ "last_status_change", d_now.getTime() ]);
						}
						if (typeof data.percentageCompleted == "number") {
							a_change.push([ "percentage_completed", Math.round(data.percentageCompleted) ]);
						}
						if (typeof data.totalTimeCentisec == "number") {
							a_change.push([ "sco_total_time_sec", Math.round(data.totalTimeCentisec/100) ]);
						}
						a_change.push([ "last_access", d_now.getTime() ]);
						a_change.push([ "status", data.now_global_status ]);

						var tmp = retryUntilWritten(
							'sahs_user',
							this_id,
							a_change
						);
					});
					return JSON.stringify(result);

				break;

				case "setSCORM12data" :
					var this_id = params[0] + '_' + params[1]; //client + obj_id
					var data=JSON.parse(params[2]);
					var d_now = new Date();
					var remoteCouch = false;
					//set data for tracking
					for(var i=0; i<data.cmi.length; i++) {
						var a_d=data.cmi[i];
						var tmp = retryUntilWritten(
							'scorm_tracking_'+this_id,
							a_d[0]+'_'+a_d[1],
							[["rvalue",encodeURIComponent(a_d[2])]]
						);
					}
					//update data for sahs_user
					var a_change = [];
					var db = new PouchDB('sahs_user',{auto_compaction:true, revs_limit: 1, cache : false});
					db.get(this_id).then(function(res){
						if (data.now_global_status != data.saved_global_status) {
							a_change.push([ "last_status_change", d_now.getTime() ]);
						}
						if (typeof data.percentageCompleted == "number") {
							a_change.push([ "percentage_completed", Math.round(data.percentageCompleted) ]);
						}
						if (typeof data.totalTimeCentisec == "number") {
							a_change.push([ "sco_total_time_sec", Math.round(data.totalTimeCentisec/100) ]);
						}
						a_change.push([ "last_access", d_now.getTime() ]);
						a_change.push([ "status", data.now_global_status ]);

						var tmp = retryUntilWritten(
							'sahs_user',
							this_id,
							a_change
						);
					});
					return "ok";
				break;

				case "scormPlayerUnload" :
					var this_id = params[0] + '_' + params[1]; //client + obj_id
					var d_now = new Date();
					var tmp = retryUntilWritten(
						'sahs_user',
						this_id,
						[ [ "last_access", d_now.getTime() ], [ "last_visited", params[2] ] ]
					);
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
