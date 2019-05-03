/**
 * script for managing the export and import from within ILIAS
 */ 

var sop;

$( document ).ready( function() {
	sop = (function() {
		var sopAppCache;
		var lmAppCache;
		var sopFrame = "";
		var lmFrame = "";
		var lmExists = false;
		var msgs;
		var progress;
		var progressTime;
		var progressInterval = 1000;
		var progressMaxtime = 300000;
		var isPurgeCookieRegEx;
		
		const CLIENT_SUCCESS = 'Successfully posted to client_data!';
		const USER_SUCCESS = 'Successfully posted to user_data!';
		const LM_SUCCESS = 'Successfully posted to lm!';
		const SAHS_SUCCESS = 'Successfully posted to sahs_user!';
		const SCORM_SUCCESS = 'Successfully posted to scorm_tracking! ';
		
		/**
		 * init sop
		 */ 
		var init = function () {
			log("sop: init");
			//removeAllTables();
			msgs = [];
			progress = false;
			sopFrame = '<iframe id="sopAppCacheDownloadFrame" src="' + sopGlobals.sop_frm_url + '" onload="sop.createSopAppCacheEventHandler(this);"></iframe>';
			lmFrame = '<iframe id="lmAppCacheDownloadFrame" src="' + sopGlobals.lm_frm_url  + '" onload="parent.sop.createLmAppCacheEventHandler(this);"></iframe>';
			$('#onlineForm').hide();
			$('#offlineForm').hide();
			isPurgeCookieRegEx = new RegExp(sopGlobals.sop_purge_cookie_1);
			document.cookie = sopGlobals.sop_purge_cookie_0;
			checkSystem();
		};
		
		/**
		 * checks HTML5 features and sop is already in appcache
		 */ 
		var checkSystem = function() {
			log("sop: checkSystem");
			msg(sopGlobals.sop_check_system_requirements,true,true);
			if (typeof window.applicationCache !== 'object') {
				if (location.protocol === 'http:') {
					msg(sopGlobals.sop_system_check_https,true);
				} else {
					msg(sopGlobals.sop_system_check_error,true);
				}
			}
			inProgress();
			if (sopGlobals.mode == 'offline') { // check if lm exists in browser
				checkLmExists().then(function(res) {
					lmExists = true;
					$('#iliasOfflineManager').after(sopFrame); // catch the appcache events
				//
				}).catch(function(err) {
					lmExists = false;
					$('#iliasOfflineManager').after(sopFrame); // catch the appcache events
				});
			}
			else {
				$('#iliasOfflineManager').after(sopFrame); // catch the appcache events
			}
			
		};
		
		var checkLmExists = function() {
			var db = new PouchDB('sahs_user');
			return db.get(sopGlobals.ilClient+'_'+sopGlobals.lmId);
		};
		
		var showForm = function() {
			log("show form: " + sopGlobals.mode);
			switch (sopGlobals.mode) {
				case "online" :
					showOnlineForm();
				break;
				case "offline" :
					showOfflineForm();
				break;
			}
		};
		
		var showOnlineForm = function() {
			$('#offlineForm').hide();
			$('#onlineForm').show();
		};
		
		var showOfflineForm = function() {
			// first hide all and load and check the offline lm without purge cookie
			$('#offlineForm').hide();
			$('#onlineForm').hide();
			msg("check offline slm");
			if (lmExists) {
				inProgress();
				$('#iliasOfflineManager').after(lmFrame);
			}
			else {
				$('#lmNotExists').show();
			}
		};
		
		var _showOfflineForm = function() {
			$('#offlineForm').show();
		}
		
		var loadOnlineMode = function () {
			window.setTimeout(function() {
				location.replace(sopGlobals.lm_cmd_url+"offlineMode_sop2ilOk");
			}, 2000);
		};
		
		var loadOfflineMode = function () {
			window.setTimeout(function() {
				location.replace(sopGlobals.lm_cmd_url+"offlineMode_il2sopOk");
			}, 2000);
		};
		
		var exportLm = function () {
			log("sop: exportLm");
			inProgress();
			$.getJSON( sopGlobals.tracking_url, function( data ) { // trigger trackingdata
				if (typeof data == 'object') {
					if (tracking2sop(data) != false) {
						$('#iliasOfflineManager').after(lmFrame); // trigger appcache download
					}
				}
				else {
					log('fetching trackingdata failed!');
					outProgress();
				}
			});
		};
		
		var getScormVersion = function () {
			var version;
			db = new PouchDB('lm');
			db.get(sopGlobals.ilClient+'_'+sopGlobals.lmId).then(function(result){
				version = result.scorm_version;
				return version;
			}).catch(function (err) {
				console.log(err);
			});
			console.log(version);
			//return version;
		};
		
		
		var startSop = function () {
			db = new PouchDB('lm');
			db.get(sopGlobals.ilClient+'_'+sopGlobals.lmId).then(function(result){
				if (result.scorm_version == "1.2") {
					log("startSop: " +sopGlobals.player12_url);
					open(sopGlobals.player12_url,"client="+sopGlobals.ilClient+"&obj_id="+sopGlobals.lmId);
				} else if (result.scorm_version == "2004"){
					log("startSop: " +sopGlobals.player2004_url);
					open(sopGlobals.player2004_url,"client="+sopGlobals.ilClient+"&obj_id="+sopGlobals.lmId);
				} else {
					alert("scorm_version not found");
				}
			}).catch(function (err) {
				console.log(err);
			});
		};
		
		var startSom = function () {
			log("startSom");
			open(sopGlobals.som_url,"client="+sopGlobals.ilClient);
		};
		
		var pushTracking = function () {
			log("sop: pushTracking");
			msg("push tracking data", true);
			sop2il(); 
		};
		
		var purgeAppCache = function () {
			msg("purge application cache for slm",true);
			log("add " + sopGlobals.sop_purge_cookie_1);
			document.cookie = sopGlobals.sop_purge_cookie_1;
			log("cookies: " + document.cookie);
			lmAppCache.update();
		}
		
		/*********************
		 * sop appcache events
		 *********************/ 
		
		
		var sopAppCacheChecking = function() {
			//log("sop appcache on checking...");
		};
		
		/**
		 * sop is already in appcache and the appcache manifest did not changed
		 */ 
		var sopAppCacheNoupdate = function() {
			log("sop appcache on noupdate...");
			showForm();
			outProgress();
		};
		
		var sopAppCacheDownloading = function() {
			log("sop appcache on downloading");
		};
		
		var sopAppCacheProgress = function(evt) {
			log("sop appcache on progress...");
		};
		
		var sopAppCacheCached = function() {
			log("sop appcache on cached...");
			showForm();
			outProgress();
		};
		
		var sopAppCacheUpdateready = function() { //ToDo: prevent multiple progress endings (4x events)
			log("sop appcache on updateready...");
			showForm();
			outProgress();
		};
		
		var sopAppCacheObsolete = function() {
			log("sop appcache on obsolete...");
		};
		
		var sopAppCacheError = function(evt) {
			log("sop appcache on error: " + evt);
			msg("sop appcache on error: ",true,true);
			outProgress();
		};
		
		
		/*********************
		 * lm appcache events
		 *********************/ 
		var lmAppCacheChecking = function() {
			log("lm appcache on checking...");
		};
		
		var lmAppCacheNoupdate = function() {
			log("lm appcache on noupdate...");
			if (sopGlobals.mode == "online") {
				outProgress();
				loadOfflineMode();
			}
			else {
				if (isPurgeCookieRegEx.test(document.cookie)) { //purge: should not occure
					log("noupdate purge"); // ready
				}
				else { // initial standard 
					log("noupdate initial");
					outProgress();
					_showOfflineForm();
				}
			}
		};
		
		var lmAppCacheDownloading = function() {
			log("lm appcache on downloading");
		};
		
		var lmAppCacheProgress = function(evt) {
			log("lm appcache on progress...");
		};
		
		var lmAppCacheCached = function() {
			log("lm appcache on cached...");
			if (sopGlobals.mode == "online") {
				outProgress();
				loadOfflineMode();
			}
			else { 
				if (isPurgeCookieRegEx.test(document.cookie)) { //purge
					log("oncached purge");
					outProgress();
					loadOnlineMode();
				}
				else { // initial check
					log("oncached initial");
					outProgress();
					_showOfflineForm();
				}
			}
		};
		
		var lmAppCacheUpdateready = function() { //ToDo: prevent multiple progress endings (4x events)
			log("lm appcache on updateready...");
			if (sopGlobals.mode == "online") {
				outProgress();
				try {
					lmAppCache.swapCache();
				}
				catch(e) {
					//
				}
				loadOfflineMode();
			}
			else { 
				if (isPurgeCookieRegEx.test(document.cookie)) { //purge
					log("updateready purge");
					outProgress();
					try {
						lmAppCache.swapCache(); // don't think its nessessary because a new site is loaded
					}
					catch(e) {
						//
					}
					//log("remove purgeCookie");
					//removeCookie("purgeCookie");
					//log("cookies: " + document.cookie);
					log("add " + sopGlobals.sop_purge_cookie_0);
					document.cookie = sopGlobals.sop_purge_cookie_0;
					log("cookies: " + document.cookie);
					loadOnlineMode();
				}
				else {
					log("updateready initial");
					outProgress();
					lmAppCache = document.getElementById('lmAppCacheDownloadFrame').contentWindow.applicationCache;
					try {
						lmAppCache.swapCache(); // don't think its nessessary because a new site is loaded
					}
					catch(e) {
						//
					}
					_showOfflineForm();
				}
			}
		};
		
		var lmAppCacheObsolete = function() {
			log("lm appcache on obsolete...");
		};
		
		var lmAppCacheError = function(evt) {
			log("lm appcache on error: " + evt);
			outProgress();
			msg("lm appcache on error: ",true,true);
		};
		
		var createSopAppCacheEventHandler = function(iframe) {
			log("sop: createSopAppCacheEventHandler: " + iframe);
			sopAppCache = iframe.contentWindow.applicationCache;
			sopAppCache.addEventListener('checking', sopAppCacheChecking, false);
			sopAppCache.addEventListener('noupdate', sopAppCacheNoupdate, false);
			sopAppCache.addEventListener('downloading', sopAppCacheDownloading, false);
			sopAppCache.addEventListener('progress', sopAppCacheProgress, false);
			sopAppCache.addEventListener('cached', sopAppCacheCached, false);
			sopAppCache.addEventListener('updateready', sopAppCacheUpdateready, false);
			sopAppCache.addEventListener('obsolete', sopAppCacheObsolete, false);
			sopAppCache.addEventListener('error', sopAppCacheError, false);
			/* Problems with this since Firefox 54
			$(sopAppCache).on('checking', sopAppCacheChecking);
			$(sopAppCache).on('noupdate', sopAppCacheNoupdate);
			$(sopAppCache).on('downloading', sopAppCacheDownloading);
			$(sopAppCache).on('progress', sopAppCacheProgress);
			$(sopAppCache).on('cached', sopAppCacheCached);
			$(sopAppCache).on('updateready', sopAppCacheUpdateready);
			$(sopAppCache).on('obsolete', sopAppCacheObsolete);
			$(sopAppCache).on('error', sopAppCacheError);
			*/ 
		};
		
		var createLmAppCacheEventHandler = function(iframe) {
			log("sop: createLmAppCacheEventHandler: " + iframe);
			lmAppCache = iframe.contentWindow.applicationCache;
			lmAppCache.addEventListener('checking', lmAppCacheChecking, false);
			lmAppCache.addEventListener('noupdate', lmAppCacheNoupdate, false);
			lmAppCache.addEventListener('downloading', lmAppCacheDownloading, false);
			lmAppCache.addEventListener('progress', lmAppCacheProgress, false);
			lmAppCache.addEventListener('cached', lmAppCacheCached, false);
			lmAppCache.addEventListener('updateready', lmAppCacheUpdateready, false);
			lmAppCache.addEventListener('obsolete', lmAppCacheObsolete, false);
			lmAppCache.addEventListener('error', lmAppCacheError, false);
			/* Problems with this since Firefox 54
			$(lmAppCache).on('checking', lmAppCacheChecking);
			$(lmAppCache).on('noupdate', lmAppCacheNoupdate);
			$(lmAppCache).on('downloading', lmAppCacheDownloading);
			$(lmAppCache).on('progress', lmAppCacheProgress);
			$(lmAppCache).on('cached', lmAppCacheCached);
			$(lmAppCache).on('updateready', lmAppCacheUpdateready);
			$(lmAppCache).on('obsolete', lmAppCacheObsolete);
			$(lmAppCache).on('error', lmAppCacheError);
			*/ 
		};
		
		/**
		 * utils
		 */
		var remove2004TablesForLM = function(table, resolve) {
			// if (resolve) { // Resolving itself
				// return new PouchDB(table).destroy().then(function(res){ log("destroyed: " + table) }).catch( function(err) { log(err); });
			// }
			// else {  // returns a chainable Promise
				// return new PouchDB(table).destroy();
			// }
			
			/*
			var db = new PouchDB(table);
			db.destroy().then(function(response) {
				console.log("destroyed: "+table);
				return true;
			}).catch(function(err){
				log(err);
				// db.close();
				return false;
			});
			*/ 
		}
		
		var removeTable = function(table, resolve) {
			if (resolve) { // Resolving itself
				return new PouchDB(table).destroy().then(function(res){ log("destroyed: " + table) }).catch( function(err) { log(err); });
			}
			else {  // returns a chainable Promise
				return new PouchDB(table).destroy();
			}
			
			/*
			var db = new PouchDB(table);
			db.destroy().then(function(response) {
				console.log("destroyed: "+table);
				return true;
			}).catch(function(err){
				log(err);
				// db.close();
				return false;
			});
			*/ 
		}
		
		var removeAllTables = function() {
			//except scorm_tracking
			removeTable('client_data', true);
			removeTable('user_data', true);
			removeTable('lm', true);
			removeTable('sahs_user', true);
			/*
			removeTable('client_data');
			removeTable('user_data');
			removeTable('lm');
			removeTable('sahs_user');
			*/ 
		}

		var removeTableRow = function(table,id) {
			var dbrt = new PouchDB(table,{auto_compaction:true, revs_limit: 1});
			var remoteCouch = false;
			dbrt.get(id).then(function(doc){
				return dbrt.remove(doc._id, doc._rev);
			}).then(function (result) {
				log("table removed: " + result);
				//dbrt.close();
			}).catch(function (err) {
				log(err);
				//dbrt.close();
			});
			/*
			var dbrt = new PouchDB(table,{auto_compaction:true, revs_limit: 1});
			var remoteCouch = false;
			dbrt.get(id).then(function(doc){
				dbrt.remove(doc._id, doc._rev);
			}).then(function (result) {
				dbrt.close();
			}).catch(function (err) {
				dbrt.close();
			});
			*/ 
		}

		var data2console = function() {

			var remoteCouch = false;
			var db = new PouchDB('client_data',{auto_compaction:true, revs_limit: 1});
			db.allDocs({include_docs: true, descending: true}).then(function(result){
				console.log('client_data'+': '+JSON.stringify(result));
			}).catch(function (err) {
				console.log(err);
			});

			db = new PouchDB('user_data',{auto_compaction:true, revs_limit: 1});
			db.allDocs({include_docs: true, descending: true}).then(function(result){
				console.log('user_data'+': '+JSON.stringify(result));
			}).catch(function (err) {
				console.log(err);
			});

			db = new PouchDB('lm',{auto_compaction:true, revs_limit: 1});
			db.allDocs({include_docs: true, descending: true}).then(function(result){
				console.log('lm'+': '+JSON.stringify(result));
			}).catch(function (err) {
				console.log(err);
			});

			db = new PouchDB('sahs_user',{auto_compaction:true, revs_limit: 1});
			db.allDocs({include_docs: true, descending: true}).then(function(result){
				console.log('sahs_user'+': '+JSON.stringify(result));
			}).catch(function (err) {
				console.log(err);
			});

			var dbname = 'scorm_tracking_'+sopGlobals.ilClient+'_'+sopGlobals.lmId;
			db = new PouchDB(dbname,{auto_compaction:true, revs_limit: 1});
			db.allDocs({include_docs: true, descending: true}).then(function(result){
				console.log(dbname+': '+JSON.stringify(result));
			}).catch(function (err) {
				console.log(err);
			});
			db.close();
		}
		
		var sop2il = function() {
			inProgress();
			var remoteCouch = false;
			var cmi = "";
			var sop2il_data = {};
			//var purgeCache = $('#chkPurgeCache').is(':checked'); // default remove appcache assets, checkbox is hidden by display: none 
			var purgeCache = true;
			db = new PouchDB('lm');
			db.get(sopGlobals.ilClient+'_'+sopGlobals.lmId).then(function(result){
				var scorm_version = result.scorm_version;

				var db = new PouchDB('sahs_user');
				db.get(sopGlobals.ilClient+'_'+sopGlobals.lmId).then(function(res){
					sop2il_data = {
						// "cmi":[],
						"adl_seq_utilities":{},
						"changed_seq_utilities":0,
						"saved_global_status":0,
						"now_global_status":res.status,
						"percentageCompleted":res.percentage_completed,
						"lp_mode":6,
						"hash":0,
						"p":res.user_id,
						"totalTimeCentisec":(res.sco_total_time_sec*100),
						"packageAttempts":res.package_attempts,
						"first_access":res.first_access,
						"last_access":res.last_access,
						"last_status_change":res.last_status_change,
						"last_visited":res.last_visited,
						"total_time_sec":null,
						"module_version":res.module_version
					};
					if (scorm_version == "1.2") {
						dbtri = new PouchDB('scorm_tracking_'+sopGlobals.ilClient+'_'+sopGlobals.lmId);
						dbtri.allDocs({include_docs: true, descending: true}).then(function(result){
							for (var i=0; i<result.total_rows; i++) {
								var left = result.rows[i].doc._id;
								var ileft = left.indexOf('_');
								cmi += ',["' + left.substr(0,ileft) +'","'+ left.substr(ileft+1) +'",'+ toJSONString(decodeURIComponent(result.rows[i].doc.rvalue)) +']';
							}
							if (cmi != "") cmi = cmi.substr(1);
							s_s=toJSONString(sop2il_data);
							var cmdUrl = document.URL.substring(0,document.URL.indexOf('?'))+'?baseClass=ilSAHSPresentationGUI&ref_id='+sopGlobals.refId+'&client_id='+sopGlobals.ilClient+'&cmd=';
							var ret = JSON.parse(sendRequest(cmdUrl+"offlineMode_sop2ilpush", '{"cmi":['+cmi+'],'+s_s.substr(1)));
							if (ret.msg[0] == "post data recieved") {
								dbtri.destroy();
								removeTableRow('sahs_user', ""+sopGlobals.ilClient+'_'+sopGlobals.lmId);
								removeTableRow('lm', ""+sopGlobals.ilClient+'_'+sopGlobals.lmId);
							}
							if (purgeCache) {
								purgeAppCache();
							}
							else { // deprecated
								outProgress();
								loadOnlineMode();
							}
						});
					} else if (scorm_version == "2004") {
						var cmi_node=[];
						dbtri = new PouchDB('cmi_node_'+sopGlobals.ilClient+'_'+sopGlobals.lmId);
						dbtri.allDocs({include_docs: true, descending: true}).then(function(result){
							for (var i=0; i<result.total_rows; i++) {
								cmi_node[i]=result.rows[i].doc.value;
							}
							var cmi_data={"node":[],"comment":[],"correct_response":[],"interaction":[],"objective":[],"i_check":15,"i_set":15};
							cmi_data.node = cmi_node;
							// cmi_data.node = JSON.parse(db.getData("sop2ilNode",[client,obj_id,sco],false,false));
							cmi_data.comment = [];//sop2ilComment",[client,obj_id,sco]
							cmi_data.correct_response = [];//sop2ilCorrectResponse",[client,obj_id,sco]
							cmi_data.interaction = [];//sop2ilInteraction",[client,obj_id,sco]
							cmi_data.objective = [];//sop2ilObjective",[client,obj_id,sco]
							log(JSON.stringify(cmi_data));
							sop2il_data.cmi = [];
							sop2il_data.cmi.push(cmi_data);
							s_s=toJSONString(sop2il_data);
							var cmdUrl = document.URL.substring(0,document.URL.indexOf('?'))+'?baseClass=ilSAHSPresentationGUI&ref_id='+sopGlobals.refId+'&client_id='+sopGlobals.ilClient+'&cmd=';
							var ret = JSON.parse(sendRequest(cmdUrl+"offlineMode_sop2ilpush", '{"cmi":['+cmi+'],'+s_s.substr(1)));
							if (ret.msg[0] == "post data recieved") {
								dbtri.destroy();
								removeTableRow('sahs_user', ""+sopGlobals.ilClient+'_'+sopGlobals.lmId);
								removeTableRow('lm', ""+sopGlobals.ilClient+'_'+sopGlobals.lmId);
							}
							if (purgeCache) {
								purgeAppCache();
							}
							else { // deprecated
								outProgress();
								loadOnlineMode();
							}
						});
					} else {
						log("SCORM version could not be determined");
					}
				}).catch(function (err) {
					// dbtri.close();
					console.log(err);
					outProgress();
				});
			});
		}
		
		var tracking2sopclient = function( client_data, resolve ) {
			log("tracking2sopclient");
			var db = new PouchDB('client_data',{auto_compaction:true, revs_limit: 1});
			var remoteCouch = false;
			var id = sopGlobals.ilClient;
			var insertRow = function() {
				log("insertRow");
				return db.put({ // may be we can redefine a insertRow function to avoid redundance
					_id: id,
					support_mail: client_data[0]
				});
			}
			
			return db.get(id).then(function(doc) {
				return db.remove(doc._id, doc._rev);
			}).then(function (result) {
				log("tracking2sopclient entry");
				if (resolve) {
					return insertRow().then( function() { log( CLIENT_SUCCESS ) } ).catch( function(err) { log(err) } );
				}
				else {
					return insertRow();
				}
			}).catch(function (err) {
				log("tracking2sopclient new entry");
				if (resolve) {
					return insertRow().then( function() { log( CLIENT_SUCCESS ) } ).catch( function(err) { log(err) } ); 
				}
				else {
					return insertRow();
				}
			});
		}
		
		var tracking2sopuser = function(user_data, resolve) {
			log("tracking2sopuser");
			var db = new PouchDB('user_data',{auto_compaction:true, revs_limit: 1});
			var remoteCouch = false;
			var id = ""+sopGlobals.ilClient;//'+_'+user_data[6];
			
			var insertRow = function(){
				log("insertRow");
				return db.put({
					_id: id,
					client: sopGlobals.ilClient,
					user_id: user_data[6],
					login: user_data[0],
					passwd: "",
					firstname: user_data[2],
					lastname: user_data[3],
					title: user_data[4],
					gender: user_data[5]
				});
			}
			
			return db.get(id).then(function(doc){
				return db.remove(doc._id, doc._rev);
			}).then(function (result) {
				log("tracking2sopuser entry");
				if (resolve) {
					return insertRow().then( function() { log( USER_SUCCESS ) } ).catch( function(err) { log(err) } ); 
				}
				else {
					return insertRow();
				}
			}).catch(function (err) {
				log("tracking2sopuser new entry");
				if (resolve) {
					return insertRow().then( function() { log( USER_SUCCESS ) } ).catch( function(err) { log(err) } ); 
				}
				else {
					return insertRow();
				}
			});
		}
		
		var tracking2soplm = function(lm, resolve) {
			log("tracking2soplm");
			var db = new PouchDB('lm',{auto_compaction:true, revs_limit: 1});
			var remoteCouch = false;
			var id = ""+sopGlobals.ilClient+'_'+sopGlobals.lmId;
			var insertRow = function(){
				log("insertRow");
				return db.put({
					_id: id,
					client: sopGlobals.ilClient,
					obj_id: sopGlobals.lmId,
					title: lm[0],
					description: lm[1],
					scorm_version: ""+lm[2],
					active: lm[3],
					init_data: JSON.parse(lm[4]),
					resources: lm[5],
					scorm_tree: lm[6],
					// last_visited: null,//UK(JSON.parse(lm[4])).launchId.toString(),
					module_version: lm[7],
					offline_zip_created: lm[8],
					learning_progress_enabled: lm[9],
					certificate_enabled: lm[10],
					max_attempt: 0,
					adlact_data: "null",
					ilias_version: lm[13]
				});
			}
			return db.get(id).then(function(doc){
				return db.remove(doc._id, doc._rev);
			}).then(function (result) {
				log("tracking2soplm entry");
				if (resolve) {
					return insertRow().then( function() { log( LM_SUCCESS ) } ).catch( function(err) { log(err) } ); 
				}
				else {
					return insertRow();
				}
			}).catch(function (err) {
				log("tracking2soplm new entry");
				if (resolve) {
					return insertRow().then( function() { log( LM_SUCCESS ) } ).catch( function(err) { log(err) } ); 
				}
				else {
					return insertRow();
				}
			});
		}
		
		var tracking2sopsahs = function(sahs_user, usrid, resolve) {
			log("tracking2sopsahs");
			var db = new PouchDB('sahs_user',{auto_compaction:true, revs_limit: 1});
			var remoteCouch = false;
			var id = ""+sopGlobals.ilClient+'_'+sopGlobals.lmId;//+'_'+usrid;
			toInsert = {
				_id: id,
				client: sopGlobals.ilClient,
				obj_id: sopGlobals.lmId,
				user_id: usrid,
				package_attempts: sahs_user[0],
				module_version: sahs_user[1],
				last_visited: sahs_user[2],
				first_access: sahs_user[3],
				last_access: sahs_user[4],
				last_status_change: sahs_user[5],
				total_time_sec: sahs_user[6],
				sco_total_time_sec: sahs_user[7],
				status: sahs_user[8],
				percentage_completed: sahs_user[9],
				user_data: ""
			};
			
			var insertRow = function(){
				log("insertRow");
				return db.put(toInsert);
			}
			
			return db.get(id).then(function(doc){
				return db.remove(doc._id, doc._rev);
			}).then(function (result) {
				log("tracking2sopsahs entry");
				if (resolve) {
					return insertRow().then( function() { log( SAHS_SUCCESS ) } ).catch( function(err) { log(err) } ); 
				}
				else {
					return insertRow();
				}
			}).catch(function (err) {
				log("tracking2sopsahs new entry");
				if (resolve) {
					return insertRow().then( function() { log( SAHS_SUCCESS ) } ).catch( function(err) { log(err) } ); 
				}
				else {
					return insertRow();
				}
			});
		}
		
		var tracking2sopcmi = function(cmi, resolve) {
			log("tracking2sopcmi 1.2");
			var dbname = 'scorm_tracking_'+sopGlobals.ilClient+'_'+sopGlobals.lmId;
			var db = new PouchDB(dbname,{auto_compaction:true, revs_limit: 1});
			var remoteCouch = false;

			var dat = [];
			for (var i=0; i<cmi.length; i++) {
				dat[i]={_id: cmi[i][0]+'_'+cmi[i][1], rvalue: cmi[i][2]}; //id=sco_id+lvalue
			}
			if (resolve) {
				return db.bulkDocs(dat).then( function() { log( SCORM_SUCCESS ) } ).catch( function(err) { log(err) } ); 
			}
			else {
				return db.bulkDocs(dat);
			}
		}
		
		var il2sopCmiNode  = function(cmi, resolve) {
			log("tracking2sopcmi 2004");
			var dbname = 'cmi_node_'+sopGlobals.ilClient+'_'+sopGlobals.lmId;
			var db = new PouchDB(dbname,{auto_compaction:true, revs_limit: 1});
			var remoteCouch = false;

			var dat = [];
			for (var i=0; i<cmi.length; i++) {
				dat[i]={_id: cmi[i][15], value: cmi[i]}; //id=cmi_node_id
			}
			if (resolve) {
				return db.bulkDocs(dat).then( function() { log( SCORM_SUCCESS ) } ).catch( function(err) { log(err) } ); 
			}
			else {
				return db.bulkDocs(dat);
			}
		}
		
		var tracking2sop = function(d) {
			log("tracking2sop");
			var usrid = d.user_data[6];
			var scorm_version = d.lm[2];
			/* no chaining, only scorm_tracking chain after removing */
			tracking2sopclient(d.client_data, true);
			tracking2sopuser(d.user_data, true); 
			tracking2soplm(d.lm, true);
			tracking2sopsahs(d.sahs_user, usrid, true);
			if (scorm_version == "1.2") {
				removeTable('scorm_tracking_'+sopGlobals.ilClient+'_'+sopGlobals.lmId, false).then( function(res) {
					return tracking2sopcmi(d.cmi, false); // new Promise
				}).then( function() {
					log(CLIENT_SUCCESS);
				}).catch( function(err) { log(err) } );
			}
			else if (scorm_version == "2004") {
				removeTable('cmi_node_'+sopGlobals.ilClient+'_'+sopGlobals.lmId, false).then( function(res) {
					return il2sopCmiNode(d.cmi.data.node, false); // new Promise
				}).then( function() {
					log(CLIENT_SUCCESS);
				}).catch( function(err) { log(err) } );
			
			
			/* complete chaining */
			/*
			removeTable('cmi_node_'+sopGlobals.ilClient+'_'+sopGlobals.lmId, false).then( function(res) {
				return il2sopCmiNode(d.cmi.data.node, false);
			}).then( function() {
				log(SCORM_SUCCESS+'cmi_node');
				removeTable('cmi_node_'+sopGlobals.ilClient+'_'+sopGlobals.lmId, false).then( function(res) {return tracking2sopuser(d.user_data, false);})
			}).then( function () {
				log(USER_SUCCESS);
				return tracking2soplm(d.lm, false); // new Promise
			}).then( function () {
				log(LM_SUCCESS);
				return tracking2sopsahs(d.sahs_user, usrid, false); // new Promise
			}).then( function () {
				//log('Successfully put to sahs_user! '+d.sahs_user+' sco_total_time_sec: '+d.sahs_user[7]+' ; status: '+d.sahs_user[8]);
				log(SAHS_SUCCESS);
				return tracking2sopcmi(d.cmi, false); // new Promise
			}).then( function () {
				log(CLIENT_SUCCESS);
			}).catch( function(err) { log(err) } );
			*/ 
			}
		}


		var removeCookie = function removeCookie(sKey, sPath, sDomain) {
			document.cookie = encodeURIComponent(sKey) + 
			"=; expires=Thu, 01 Jan 1970 00:00:00 GMT" + 
			(sDomain ? "; domain=" + sDomain : "") + 
			(sPath ? "; path=" + sPath : "");
		};

		var log = function(txt) {
			console.log(txt);
		};
		
		var msg = function(txt,flush,reset) {
			flush = typeof flush !== 'undefined' ? flush : false;
			reset = typeof reset !== 'undefined' ? reset : false;
			if (reset) {
				msgs = [txt];
			}
			else {
				msgs.push(txt);
			}
			if (flush) {
				$('#sopMessage').html(msgs.join("<br/>"));
				msgs = [];
			}
		};
		
		var msgShow = function() {
			msg("",true);
		};
		
		var msgReset = function() {
			msg("",true,true);
		}
		
		var msgProgress = function() {
			var m = $('#sopProgress').text() + " .";
			$('#sopProgress').text(m);
		}
		
		var msgProgressTimeout = function() {
			$('#sopProgress').text(sopGlobals.sop_progress_timeout);
		}
		
		var inProgress =  function() {
			progress = true;
			progressTime = 0;
			var funcid = setInterval(inc, progressInterval);
			function inc() {
				if (!progress) {
					clearInterval(funcid);
					return;
				}
				if (progressTime > progressMaxtime) {
					clearInterval(funcid);
					msgProgressTimeout();
					return;
				}
				msgProgress();
				progressTime += progressInterval;
			}
		}
		
		var outProgress = function(keepMsg) {
			progress = false;
			$('#sopProgress').text("");
			if (!keepMsg) {
				msgReset();
			}
		}

/* XMLHHTP functions */
function sendRequest (url, data, callback, user, password, headers) {		

	function sendAndLoad(url, data, callback, user, password, headers) {
		function createHttpRequest() {
			try 
			{
				return window.XMLHttpRequest 
					? new window.XMLHttpRequest()
					: new window.ActiveXObject('MSXML2.XMLHTTP');
			} 
			catch (e) 
			{
				throw new Error('cannot create XMLHttpRequest');
			}
		}
		function HttpResponse(xhttp) 
		{
			this.status = Number(xhttp.status);
			this.content = String(xhttp.responseText);
			this.type = String(xhttp.getResponseHeader('Content-Type'));
		}
		function onStateChange() 
		{
			if (xhttp.readyState === 4) { // COMPLETED
				if (typeof callback === 'function') {
					callback(new HttpResponse(xhttp));
				} else {
					return new HttpResponse(xhttp);
				} 
			}
		}		
		var xhttp = createHttpRequest();
		var async = !!callback;
		var post = !!data; 
		xhttp.open(post ? 'POST' : 'GET', url, async, user, password);
		if (typeof headers !== 'object') 
		{
			headers = new Object();
		}
		if (post) 
		{
			headers['Content-Type'] = 'application/x-www-form-urlencoded';
		}
		if (headers && headers instanceof Object) 
		{
			for (var k in headers) {
				xhttp.setRequestHeader(k, headers[k]);
			}
		}
		if (async) 
		{
			xhttp.onreadystatechange = onStateChange;
//				xhttp.send(data ? String(data) : '');				
			xhttp.send(data);				
		} else 
		{
			xhttp.send(data ? String(data) : '');				
			return onStateChange();
		}
	}

	if (typeof headers !== "object") {headers = {};}
	headers['Accept'] = 'text/javascript';
	// headers['Accept-Charset'] = 'UTF-8'; // error
	var r = sendAndLoad(url, data, callback, user, password, headers);
	
	if (r.content) {
		if (r.content.indexOf("login.php")>-1) {
			window.location.href = "./Modules/Scorm2004/templates/default/session_timeout.html";
		}
	}
	
	if ((r.status===200 && (/^text\/javascript;?.*/i).test(r.type)) || r.status===0)
	{
		return r.content;
	}
	else
	{
		return r.content;
	}
}

function toJSONString (v, tab) {
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
				ra.push(v[i]===undefined ? 'null' : toJSONString(v[i], tab.charAt(0) + tab));
			}
			return '[' + nl + tab + ra.join(',' + nl + tab) + nl + tab + ']';
		} else {
			var ro = new Array();
			for (var k in v) {	
				if (v.hasOwnProperty && v.hasOwnProperty(k)) {
					ro.push(esc(String(k)) + ':' + toJSONString(v[k], tab.charAt(0) + tab));
				}
			}
			return '{' + nl + tab + ro.join(',' + nl + tab) + nl + tab + '}';
		}
	}
}
		
		return {
			init 				: init,
			exportLm 			: exportLm,
			startSop			: startSop,
			startSom			: startSom,
			pushTracking 			: pushTracking,
			createSopAppCacheEventHandler 	: createSopAppCacheEventHandler,
			createLmAppCacheEventHandler 	: createLmAppCacheEventHandler
		};
	}());
	sop.init();
});
