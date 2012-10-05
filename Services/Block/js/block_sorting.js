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

					$this.sortable({
						onElementDragStart: function(event, ui) {

							for(i in internals.sortableContainer) {
								var $elm = $(internals.sortableContainer[i]);
								if ($(">div", $elm).size() == 0) {
									var $container = $elm.parent();
									$container.css("width", "");
									$elm.css({
										"width": $container.width(),
										"min-width": $container.width(),
										"height": $container.height(),
										"min-height": $container.height()
									});
								}
							}

						},
						stop: function(event, ui) {

							var postData = [];

							for(i in internals.sortableContainer) {
								var $elm = $(internals.sortableContainer[i]);
								$elm.sortable("disable");
								if ($(">div", $elm).size() == 0) {
									var $container = $elm.parent();
									$container.css("width", "0px");
									$elm.css({
										"width": '',
										"min-width": '',
										"height": '',
										"min-height": ''
									});
								}
							}

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
										$(internals.sortableContainer[i]).sortable("enable");

										$(internals.sortableContainer[i]).find("tr.il_adv_sel").each(function() {
											$(this).attr("onclick", $(this).attr("onclick").replace(/col_side=(left|right)/, "col_side=" + data.properties.column_parameter[i]));
										});

										$(internals.sortableContainer[i]).find("td.il_adv_sel a").each(function() {
											$(this).attr("href", $(this).attr("href").replace(/col_side=(left|right)/, "col_side=" + data.properties.column_parameter[i]));
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
					}).disableSelection();
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