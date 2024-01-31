/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ******************************************************************** */

il.Explorer2 = {

  current_search_term: [],

  selects: {},

  configs: {},

  init(config, js_tree_config) {
    if (config.ajax) {
      const node_name = config.node_par_name;
      js_tree_config.core.data = {
        url: `${config.url}&exp_cmd=getNodeAsync`,
        data(n) {
          let { id } = n;
          console.log('data called');
          if (n.id === '#') {
            id = '';
          }
          if (id == '') {
            console.log(n);
            console.log(il.Explorer2.current_search_term[0]);
            return {
              exp_cont: config.container_id,
              searchterm: il.Explorer2.current_search_term[0],
            };
          }
          console.log(n);
          console.log(il.Explorer2.current_search_term[id]);
          const d = {
            exp_cont: config.container_id,
            searchterm: il.Explorer2.current_search_term[id],
          };
          d[node_name] = id;
          return d;
        },
      };
    }
    config.js_tree_config = js_tree_config;
    il.Explorer2.configs[config.container_id] = config;
    $(`#${config.container_id}`).on('loaded.jstree', (event, data) => {
      let i;
      $(`#${config.container_outer_id}`).removeClass('ilNoDisplay');
      for (i = 0; i < config.second_hnodes.length; i++) {
        $(`#${config.second_hnodes[i]}`).addClass('ilExplSecHighlight');
      }
    }).on('open_node.jstree close_node.jstree', (event, data) => {
      il.Explorer2.toggle(event, data);
    }).on('ready.jstree', (e, data) => {
      il.Explorer2.setEvents(`#${config.container_id}`, config.container_id);
    })
      .on('refresh.jstree', (e, data) => {
        il.Explorer2.setEvents(`#${config.container_id}`, config.container_id);
      })
      .on('after_open.jstree', (e, data) => {
        const cid = data.node.id; let
          p;
        if (cid !== '#') {
          p = `#${cid}`;
          setTimeout(() => {
            il.Explorer2.setEvents(p, config.container_id);
          }, 500);
        }
      })
      .jstree(js_tree_config);
  },

  setEvents(p, cid) {
    $(p).find('a').on('click', function (e) {
      const href = $(this).attr('href');
      const target = $(this).attr('target');
      if (href != '#' && href != '') {
        if (target == '_blank') {
          window.open(href, '_blank');
        } else {
          document.location.href = href;
        }
      }
    });
    $(`${p} .ilExpSearchInput`).parent('a').replaceWith(function () { return $('input:first', this); });

    $(`${p} .ilExpSearchInput`).on('keydown', (e) => {
      if (e.keyCode === 13) {
        e.stopPropagation();
        e.preventDefault();
        const pid = $(e.target).parents('li').parents('li').attr('id');
        if (pid) {
          il.Explorer2.current_search_term[pid] = $(e.target).val();
          $(`#${cid}`).jstree('refresh_node', pid);
        } else {
          il.Explorer2.current_search_term[0] = $(e.target).val();
          $(`#${cid}`).jstree('refresh');
        }
      }
    });
  },

  toggle(event, data) {
    const { type } = event; // "open_node" or "close_node"
    const { id } = data.node; // id of li element
    const container_id = event.target.id;
    const t = il.Explorer2;
    let url;

    // the args[2] parameter is true for the initially
    // opened nodes, but not, if manually opened
    // this is somhow undocumented, but it works
    if (type == 'open_node'
			&& typeof t.configs[container_id].js_tree_config.core.initially_open[id] !== 'undefined') {
      return;
    }

    url = t.configs[container_id].url;
    if (url == '') {
      return;
    }
    if (type == 'open_node') {
      url = `${url}&exp_cmd=openNode`;
    } else {
      url = `${url}&exp_cmd=closeNode`;
    }
    if (id != '') {
      url = `${url}&exp_cont=${container_id}&${t.configs[container_id].node_par_name}=${id}`;
    }
    il.Util.sendAjaxGetRequestToUrl(url, {}, {}, null);
  },

  //
  // ExplorerSelectInputGUI related functions
  //

  // init select input
  initSelect(id) {
    $(`#${id}_select`).on('click', (ev) => {
      il.UICore.unloadWrapperFromRightPanel();
      il.UICore.showRightPanel();
      il.UICore.loadWrapperToRightPanel(`${id}_expl_wrapper`);
      return false;
    });
    $(`#${id}_reset`).on('click', (ev) => {
      $(`#${id}_hid`).empty();
      $(`#${id}_cont_txt`).empty();
      $(`#${id}_expl_content input[type="checkbox"]`).each(function () {
        this.checked = false;
      });

      return false;
    });
    $(`#${id}_expl_content a.ilExplSelectInputButS`).on('click', (ev) => {
      let t = sep = '';
      // create hidden inputs with values
      $(`#${id}_hid`).empty();
      $(`#${id}_cont_txt`).empty();
      $(`#${id}_expl_content input[type="checkbox"]`).each(function () {
        const n = `${this.name.substr(0, this.name.length - 6)}[]`;
        const ni = `<input type='hidden' name='${n}' value='${this.value}' />`;
        if (this.checked) {
          t = t + sep + $(this).parent().find('span.ilExp2NodeContent').html();
          sep = ', ';
          $(`#${id}_hid`).append(ni);
        }
      });
      $(`#${id}_expl_content input[type="radio"]`).each(function () {
        const n = this.name.substr(0, this.name.length - 4);
        const ni = `<input type='hidden' name='${n}' value='${this.value}' />`;
        if (this.checked) {
          t = t + sep + $(this).parent().find('span.ilExp2NodeContent').html();
          sep = ', ';
          $(`#${id}_hid`).append(ni);
        }
      });
      $(`#${id}_cont_txt`).html(t);
      il.UICore.hideRightPanel();

      return false;
    });
    $(`#${id}_expl_content a.ilExplSelectInputButC`).on('click', (ev) => {
      il.UICore.hideRightPanel();
      return false;
    });
  },

  selectOnClick(e, node_id) {
    let el;
    $(`#${node_id} input[type="checkbox"]:first`).each(function () {
      el = this;
      setTimeout(() => {
        el.checked = !el.checked;
      }, 10);
    });
    $(`#${node_id} input[type="radio"]:first`).each(function () {
      el = this;
      setTimeout(() => {
        el.checked = true;
      }, 10);
    });

    return false;
  },
};
