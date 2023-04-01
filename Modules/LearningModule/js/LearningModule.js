il.LearningModule = {
	
	save_url: '',
	toc_refresh_url: '',
	init_frame: {},
	last_frame_url: {},
	all_targets: ["center_bottom", "right", "right_top", "right_bottom"],
	rating_url: '',
	close_html: '',
	core: il.repository.core,

	setSaveUrl: function (url) {
		il.LearningModule.save_url = url;
	},

	setTocRefreshUrl: function (url) {
		il.LearningModule.toc_refresh_url = url;
	},


	setCloseHTML: function (html) {
		il.LearningModule.close_html = html;
	},

	hideNextNavigation: function () {
		document.querySelectorAll(".ilc_page_rnav_RightNavigation").
			forEach(el => { el.classList.add("ilNoDisplay") });
	},

	showNextNavigation: function () {
		document.querySelectorAll(".ilc_page_rnav_RightNavigation").
		forEach(el => { el.classList.remove("ilNoDisplay") });
	},

	showContentFrame: function (e, target) {
		let href = e.target.href;
		this.core.trigger('il-lm-show-' + target + '-slate');
		if (!href) {
			const p = e.target.closest("[href]");
			if (p) {
				href = p.getAttribute("href");
			}
		}
		if (href != "") {
			return il.LearningModule.loadContentFrame(href, target);
		}
	},
	
	initContentFrame: function (href, target) {
		il.LearningModule.init_frame[target] = href;
	},
	
	setLastFrameUrl: function (href, target) {
		il.LearningModule.last_frame_url[target] = href;
	},
	
	openInitFrames: function () {
		var i, t;

		for (i = 0; i < il.LearningModule.all_targets.length; i++) {
			t = il.LearningModule.all_targets[i];
			if (il.LearningModule.init_frame[t]) {
				il.LearningModule.loadContentFrame(il.LearningModule.init_frame[t], t);
			} else if (il.LearningModule.last_frame_url[t]) {
				il.LearningModule.loadContentFrame(il.LearningModule.last_frame_url[t], t);
			}
		}
	},
	
	loadContentFrame: function (href, t) {
		const el_id = t + "_area";
		let doc;
		doc = (window.top != window.self)
			? window.parent.document : document;
		const el = document.getElementById(el_id);
		el.parentNode.style.height = "100%";
		const iframe = doc.querySelector("#" + el_id + " > iframe");
		iframe.src = href;
		return false;
	},
	
	setRatingUrl: function (url) {
		this.rating_url = url;
	},
	
	saveRating: function (rating) {
		this.core.fetchHtml(this.rating_url, {
			rating: rating
		}, true).then((html) => {
			const el = document.getElementById("ilrtrpg");
			this.core.setInnerHTML(el, html);
			if (typeof WebuiPopovers !== "undefined") {
				WebuiPopovers.hideAll();
			}
		});
	},

	processAnswer: function(questions) {
		var correct = true, has_questions = false;
		for (var i in questions.answers) {
			has_questions = true;
			if (!questions.answers[i].passed) {
				correct = false;
			}
		}

		//if (has_questions && correct) {
		if (ilias.questions.determineSuccessStatus() == "passed") {
			il.LearningModule.showNextNavigation();
		}
	},

	refreshToc: function() {
		const treeId = "il_expl2_jstree_cont_out_ilLMProgressTree";
		const treeEl = document.getElementById(treeId);
		if (ilias.questions.determineSuccessStatus() == "passed") {
			if (il.LearningModule.toc_refresh_url != "" && treeEl) {
				this.core.fetchReplace(treeId, il.LearningModule.toc_refresh_url);
			}
		}
	},

  openMenuLink: function(url) {
    window.open(url, '_blank');
  }
};
il.Util.addOnLoad(() => {
	if (typeof ilCOPageQuestionHandler != "undefined") {
		ilCOPageQuestionHandler.setSuccessHandler(il.LearningModule.refreshToc);
	}
});