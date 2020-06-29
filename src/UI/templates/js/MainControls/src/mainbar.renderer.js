var renderer = function($) {
    var css = {
        engaged: 'engaged'
        ,disengaged: 'disengaged'
        ,hidden: 'hidden'
        ,page_div: 'il-layout-page'
        ,page_has_engaged_slated: 'with-mainbar-slates-engaged'
        ,tools_btn: 'il-mainbar-tools-button'
        ,toolentries_wrapper: 'il-mainbar-tools-entries'
        ,remover_class: 'il-mainbar-remove-tool'
        ,mainbar: 'il-mainbar'
        ,mainbar_buttons: '.il-mainbar .il-mainbar-entries .btn-bulky, .il-mainbar .il-mainbar-entries .link-bulky'
        ,mainbar_entries: 'il-mainbar-entries'
    },

    dom_references = {},
    dom_element = {
        withHtmlId: function (html_id) {
            return Object.assign({}, this, {html_id: html_id});
        },
        getElement: function(){
            //return document.getElementById(this.html_id);
            return $('#' + this.html_id);
        },
        engage: function() {
            this.getElement().addClass(css.engaged);
            this.getElement().removeClass(css.disengaged);
            this.getElement().trigger('in_view'); //this is most important for async loading of slates,
                                                  //it triggers the GlobalScreen-Service.
            if(il.UI.page.isSmallScreen() && il.UI.maincontrols.metabar) {
                il.UI.maincontrols.metabar.disengageAll();
            }
            this.additional_engage();
        },
        disengage: function() {
            this.getElement().addClass(css.disengaged);
            this.getElement().removeClass(css.engaged);
            this.additional_disengage();
        },
        mb_hide: function(on_parent) {
            var element = this.getElement();
            if(on_parent) {
                element = element.parent();
            }
            element.addClass(css.hidden);
        },
        mb_show: function(on_parent) {
            var element = this.getElement();
            if(on_parent) {
                element = element.parent();
            }
            element.removeClass(css.hidden);
        },
        additional_engage: function(){},
        additional_disengage: function(){}
    },
    parts = {
        triggerer: Object.assign({}, dom_element, {
            remove: function() {},
            additional_engage: function(){
                this.getElement().attr('aria-pressed', true);
            },
            additional_disengage: function(){
                this.getElement().attr('aria-pressed', false);
            }
        }),
        slate: Object.assign({}, dom_element, {
            remove: null,
            mb_hide: null,
            mb_show: null,
            additional_engage: function(){
                this.getElement().attr('aria-expanded', true);
                this.getElement().attr('aria-hidden', false);
            },
            additional_disengage: function(){
                this.getElement().attr('aria-expanded', false);
                this.getElement().attr('aria-hidden', true);
            }
        }),
        remover: Object.assign({}, dom_element, {
            engage: null,
            disengage:null,
            mb_show: function(){this.getElement().parent().show();}
        }),
        page: {
            getElement: function(){
                return $('.' + css.page_div);
            },
            slatesEngaged: function(engaged) {
                if(engaged) {
                    this.getElement().addClass(css.page_has_engaged_slated);
                } else {
                    this.getElement().removeClass(css.page_has_engaged_slated);
                }
            }
        },
        removers: {
            getElement: function(){
                return $('.' + css.remover_class);
            },
            mb_hide: function() {
                this.getElement().hide();
            }

        },
        tools_area: Object.assign({}, dom_element, {
            getElement: function(){
                return $(' .' + css.toolentries_wrapper);
            }
        }),
        tools_button: Object.assign({}, dom_element, {
            getElement: function(){
                return $('.' + css.tools_btn + ' .btn');
            },
            remove: null,
            additional_engage: function(){
                this.getElement().attr('aria-pressed', true);
            },
            additional_disengage: function(){
                this.getElement().attr('aria-pressed', false);
            }
        }),
        mainbar: {
            getElement: function(){
                return $('.' + css.mainbar);
            },
            getOffsetTop: function() {
                return this.getElement().offset().top;
            }
        }
    },

    //more-slate
    more = {
        calcAmountOfButtons: function() {

            var window_height = $(window).height(),
                window_width = $(window).width(),
                horizontal = il.UI.page.isSmallScreen(),
                btn = $(css.mainbar_buttons).first()
                btn_height = btn.height(),
                btn_width = btn.width(),
                amount_buttons = Math.floor(
                    (window_height - parts.mainbar.getOffsetTop()) / btn_height
                );

            if(horizontal) {
                amount_buttons = Math.floor(window_width / btn_width);
            }
            return amount_buttons;
        }
    },

    actions = {
        addEntry: function (entry_id, part, html_id) {
            dom_references[entry_id] = dom_references[entry_id] || {};
            dom_references[entry_id][part] = html_id;
        },
        renderEntry: function (entry, is_tool) {
            if(!dom_references[entry.id]){
                return;
            }

            var triggerer = parts.triggerer.withHtmlId(dom_references[entry.id].triggerer),
                slate = parts.slate.withHtmlId(dom_references[entry.id].slate);

            if(entry.hidden) {
                triggerer.mb_hide(is_tool);
            } else {
                triggerer.mb_show(is_tool);
            }

            if(entry.engaged) {
                triggerer.engage();
                slate.engage();
                if(entry.removeable) {
                    remover = parts.remover.withHtmlId(dom_references[entry.id].remover);
                    remover.mb_show(true);
                }
            } else {
                triggerer.disengage();
                slate.disengage();
            }
        },

        moveToplevelTriggerersToMore: function (model_state) {
            var entry_ids = Object.keys(model_state.entries),
                last_entry_id = entry_ids[entry_ids.length - 1],
                more_entry = model_state.entries[last_entry_id],
                more_slate = parts.slate.withHtmlId(dom_references[more_entry.id].slate),
                root_entries = il.UI.maincontrols.mainbar.model.getTopLevelEntries(),
                root_entries_length = root_entries.length - 1,
                max_buttons = more.calcAmountOfButtons() - 1; //room for the more-button

            if(model_state.any_tools_visible()) { max_buttons--};

            for(i = max_buttons; i < root_entries_length; i++) {
                btn = parts.triggerer.withHtmlId(dom_references[root_entries[i].id].triggerer);
                btn.getElement().appendTo(more_slate.getElement().children('.il-maincontrols-slate-content'));
            }
        },
        render: function (model_state) {
            var entry_ids = Object.keys(model_state.entries),
                last_entry_id = entry_ids[entry_ids.length - 1],
                more_entry = model_state.entries[last_entry_id],
                more_button = parts.triggerer.withHtmlId(dom_references[more_entry.id].triggerer),
                more_slate = parts.slate.withHtmlId(dom_references[more_entry.id].slate);
                //reset
                more_slate.getElement().find('.btn-bulky, .link-bulky').insertBefore(more_button.getElement());

            if(model_state.more_available) {
                actions.moveToplevelTriggerersToMore(model_state);
            }

            parts.page.slatesEngaged(model_state.any_entry_engaged || model_state.tools_engaged);

            if(model_state.any_tools_visible()) {
                parts.tools_button.mb_show();
            } else {
                parts.tools_button.mb_hide();
            }

            if(model_state.tools_engaged){
                parts.tools_button.engage();
                parts.tools_area.engage();
            } else {
                parts.tools_button.disengage();
                parts.tools_area.disengage();
            }

            for(idx in model_state.entries) {
                actions.renderEntry(model_state.entries[idx], false);
            }
            for(idx in model_state.tools) {
                actions.renderEntry(model_state.tools[idx], true);
            }
            //unfortunately, this does not work properly via a class
            $('.' + css.mainbar_entries).css('visibility', 'visible');
        }
    },
    public_interface = {
        addEntry: actions.addEntry,
        calcAmountOfButtons: more.calcAmountOfButtons,
        render: actions.render
    };

    return public_interface;
}

export default renderer;