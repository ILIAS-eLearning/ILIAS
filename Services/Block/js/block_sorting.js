(function($) {

	$.ui.sortable.prototype.ilColumnSideSerialize = function(side) {
		var items = this._getItemsAsjQuery(false);

		var str = [];
		$(items).each(function() {
			str.push(side + '[sequence][]' + '=' + $(this).attr('id'));
		});

		return str.join('&');
	};


	$.fn.ilBlockMoving = function (method) {

		var internals = {
			sortableContainer: new Array()
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

					$this.addClass('iosPdBlockSortableContainer');

					// workaround for an empty side
					if ($.trim($this.html()).length == 0) {
						$this.css('height', $this.parent().height());
					}

					$this.sortable({
						stop: function(event, ui) {

							var postData = new Array();

							for(i in internals.sortableContainer) {
								// reset the height
								$(internals.sortableContainer[i]).css('height', 'auto');
								// disable all sortable objects
								$(internals.sortableContainer[i]).sortable('disable');
							}

							// get serialized data of the concerning sortable objects
							for(i in internals.sortableContainer) {
								postData.push($(internals.sortableContainer[i]).sortable('ilColumnSideSerialize', data.properties.columns[i]));
							}

							// send data to server
							$.ajax({
								type:    'POST',
								dataType:'json',
								data:    postData.join('&'),
								url:     data.properties.url,
								success: function (response) {
									// finally enable all sortable objects
									for(i in internals.sortableContainer) {
										$(internals.sortableContainer[i]).sortable('enable');

										$(internals.sortableContainer[i]).find("tr.il_adv_sel").each(function() {
											$(this).attr('onclick', $(this).attr('onclick').replace(/col_side=(left|right)/, 'col_side=' + data.properties.columns[i]));
										});

										$(internals.sortableContainer[i]).find("td.il_adv_sel a").each(function() {
											$(this).attr('href', $(this).attr('href').replace(/col_side=(left|right)/, 'col_side=' + data.properties.columns[i]));
										});
										
									}
								}
							}).fail(function(jqXHR, textStatus) {
								// finally enable all sortable objects
								for(i in internals.sortableContainer) {
									$(internals.sortableContainer[i]).sortable('enable');

									$(internals.sortableContainer[i]).find("tr.il_adv_sel").each(function() {
										$(this).attr('onclick', $(this).attr('onclick').replace(/col_side=(left|right)/, 'col_side=' + data.properties.columns[i]));
									});

									$(internals.sortableContainer[i]).find("td.il_adv_sel a").each(function() {
										$(this).attr('href', $(this).attr('href').replace(/col_side=(left|right)/, 'col_side=' + data.properties.columns[i]));
									});
								}
							});

						},
						opacity: 0.6,
						revert: true,
						handle: '.ilBlockHeader',
						placeholder: 'iosPdBlockDragAndDropPlaceholder',
						connectWith: ".iosPdBlockSortableContainer",
						forcePlaceholderSize: true,
						items: '>div'
					}).disableSelection();

					internals.sortableContainer.push($this);
				});
			}
		}
		
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === "object" || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error("Method " + method + " does not exist on jQuery.ilBlockMoving");
		}

	}
	
})(jQuery);