(function($) {

	$.ui.sortable.prototype.ilColumnSideSerialize = function(side) {
		var items = this._getItemsAsjQuery(false);

		var str = [];
		$(items).each(function() {
			str.push(side + '[sequence][]' + '=' + $(this).attr('id'));
		});

		return str.join('&');
	};
	
	var jquis_mouseStart = $.ui.sortable.prototype._mouseStart;
	$.ui.sortable.prototype._mouseStart = function(event, overrideHandle, noActivation) {
		this._trigger("onElementDragStart", event, this._uiHash());
		jquis_mouseStart.call(this, event, overrideHandle, noActivation);
	};


	$.fn.ilBlockMoving = function(method) {

		var internals = {
			sortableContainer: []
		};

		var methods = {
			init: function(params) {
				return this.each(function () {
					var $this = $(this);

					var data = {
						properties: $.extend(
							true, {}, {}, params
						)
					};

					$this.addClass('iosPdBlockSortableContainer').find("div .ilBlockHeader").css("cursor", "move");
					internals.sortableContainer.push($this);
					
					var $center_column = $("#il_center_col");

					$this.sortable({
						onElementDragStart: function (event, ui) {

							$center_column.removeClass("col-sm-9");
							$center_column.addClass("col-sm-6");

							for (i in internals.sortableContainer) {

								var $elm = $(internals.sortableContainer[i]);
								$elm.addClass("col-sm-3");

								if ($(">div", $elm).size() == 0) {
									$elm.css({
										"width":     "",
										"min-width": "",
										"height":    $center_column.height(),
										"min-height":$center_column.height()
									});

									$elm.html($('<div class="iosPdBlockColumnPlaceholder">&nbsp;</div>'));
								}

								if (i == 0) {
									// there are two columns on drag event, so set the right "pull" class
									$center_column.addClass("col-sm-push-3");
									$elm.removeClass("col-sm-pull-9");
									$elm.addClass("col-sm-pull-6");
								}
							}
						},
						stop: function (event, ui) {

							var postData      = [];

							for (i in internals.sortableContainer) {

								var $elm = $(internals.sortableContainer[i]);
								var size = $(">div", $elm).size();

								if (size == 0 || (size == 1 && $(">div.iosPdBlockColumnPlaceholder", $elm).size() == 1)) {
									$elm.css({
										"width":     "",
										"min-width": "",
										"height":    "",
										"min-height":""
									});

									// Remove class on empty drop areas
									$elm.removeClass("col-sm-3");

									// One drop area is empty, set the right css class for the center column
									$center_column.removeClass("col-sm-6");
									$center_column.addClass("col-sm-9");

									if (i == 0) {
										// left column is empty on drop event, all blocks are on the right side
										$elm.removeClass("col-sm-pull-6");
										$elm.removeClass("col-sm-pull-9");
										$center_column.removeClass("col-sm-push-3");
									} else if (i == 1) {
										// right column is empty on drop event, all blocks are on the left site
										$(internals.sortableContainer[0]).removeClass("col-sm-pull-6");
										$(internals.sortableContainer[0]).addClass("col-sm-pull-9");
										$center_column.addClass("col-sm-push-3");
									}
								}
							}

							$('.iosPdBlockColumnPlaceholder').remove();

							for(i in internals.sortableContainer) {
								postData.push($(internals.sortableContainer[i]).sortable("ilColumnSideSerialize", data.properties.column_parameter[i]));
							}

							// send data to server
							$.ajax({
								type:    "POST",
								dataType:"json",
								data:    postData.join("&"),
								url:     data.properties.url,
								success: function (response) {
									for(i in internals.sortableContainer) {
										var $elm = $(internals.sortableContainer[i]);

										$elm.sortable("enable");

										$elm.find("tr.il_adv_sel").each(function() {
											$(this).attr("onclick", $(this).attr("onclick").replace(/col_side=(left|right)/, "col_side=" + data.properties.column_parameter[i]));
										});

										$elm.find("td.il_adv_sel a").each(function() {
											$(this).attr("href", $(this).attr("href").replace(/col_side=(left|right)/, "col_side=" + data.properties.column_parameter[i]));
										});

										$elm.find(".ilBlockInfo a").each(function() {
											$(this).attr("href", $(this).attr("href").replace(/col_side=(left|right)/, "col_side=" + data.properties.column_parameter[i]));
											if (typeof $(this).attr("onclick") == "string") {
												$(this).attr("onclick", $(this).attr("onclick").replace(/col_side=(left|right)/, "col_side=" + data.properties.column_parameter[i]));
											}
										});
									}
								}
							}).fail(function(jqXHR, textStatus) {
								for(i in internals.sortableContainer) {
									$(internals.sortableContainer[i]).sortable("enable");
								}
							});

						},
						opacity: 0.6,
						revert: true,
						handle: ".ilBlockHeader",
						placeholder: "iosPdBlockDragAndDropPlaceholder",
						connectWith: ".iosPdBlockSortableContainer",
						forcePlaceholderSize: true,
						cursor: "move",
						items: ">div"
					});
				});
			}
		};
		
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === "object" || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error("Method " + method + " does not exist on jQuery.ilBlockMoving");
		}

	};
	
})(jQuery);