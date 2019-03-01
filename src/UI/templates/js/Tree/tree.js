il = il || {};
il.UI = il.UI || {};

(function($, UI) {
	UI.tree = (function($) {

		var init = function (component_id) {
			var tree = $('#' + component_id);
			initNodesForExpansion(tree);
			initNodesForAsyncLoading(tree);
		};

		var initNodesForExpansion = function (tree) {
			tree.find('.il-tree-node > span').click(
				function(e){
					$(this).parent('.il-tree-node').toggleClass('expanded');
					return false;
				}
			);

		};

		var initNodesForAsyncLoading = function (tree) {
			tree.find(".il-tree-node[data-async_url][data-async_loaded='false'] > span").click(
				function(e){
					var node = $(e.target).parent('.il-tree-node');
					if(node.attr('data-async_loaded') == 'false') {
						$.ajax({
							url: node.attr('data-async_url'),
							dataType: 'html'
						}).done(
							function(html) {
								node.attr('data-async_loaded', true);

								if(!html) {
									node.removeClass('expandable');
								}
								$(html).insertAfter($(e.target));
							}
						);
					}

					return false;
				}
			);
		};

		return {
			init: init
		}

	})($);
})($, il.UI);
