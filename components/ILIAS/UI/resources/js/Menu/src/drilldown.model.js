var ddmodel = function() {
    var
    data = [],

    classes = {
        level : {
            id: null,
            parent: null,
            engaged : false,
            filtered : false,
            headerDisplayElement : '',
            leaves : []
        }
    },

    factories = {
        cloned: (obj, params) => Object.assign({}, obj, params),
        level : (headerDisplayElement, parent, leaves) => factories.cloned(classes.level, {
            id : data.length.toString(),
            headerDisplayElement : headerDisplayElement,
            parent : parent,
            leaves : leaves
        })
    },

    actions = {
      addLevel : function(headerDisplayElement, parent, leaves) {
          if(! parent) {
              parent = null;
          }
          var level = factories.level(headerDisplayElement, parent, leaves);
          data[level.id] = level;
          return level;
      },
      /**
       * @param  {String} id
       */
      engageLevel : function(id) {
          for(var idx in data) {
              data[idx].engaged = false;
              if(data[idx].id === id) {
                  data[idx].engaged = true;
              }
          }
      },
      getCurrent : function() {
          for(var idx in data) {
              if(data[idx].engaged) {
                  return data[idx];
              };
          }
          return data[0];
      },
      getParent : function() {
          let cur = actions.getCurrent();
          if (cur.parent) {
            return data[cur.parent];
          }

          return {};
      },
      upLevel : function() {
          var cur = actions.getCurrent();
          if(cur.parent) {
              actions.engageLevel(data[cur.parent].id);
          }
      },
      /**
       * @param {Event} e
       */
      filter : function (e) {
        let
        value = e.target.value.toLowerCase(),
        removeFilteredRecursive = (id) => {
          if (id !== null && id !== 0) {
            data[id].filtered = false;
            if (data[id].parent !== null && data[id].parent !== 0) {
              removeFilteredRecursive(data[id].parent);
            }
          }
        };

        data.forEach(
          (level, levelId) => {
            var hasVisibleLeaves = false;
            level.leaves.forEach(
              (leaf) => {
                if (leaf.text.toLowerCase().includes(value) === false) {
                  leaf.filtered = true;
                  return;
                }
                leaf.filtered = false;
                hasVisibleLeaves = true;
              }
            );
            level.filtered = true;
            if (hasVisibleLeaves) {
              level.filtered = false;
              if (level.parent !== null && level.parent !== 0) {
                removeFilteredRecursive(levelId);
              }
            }
          }
        );
      },
      getFiltered : function () {
        let filtered = [];
        data.forEach(
          (level) => {
            if (level.filtered) {
              filtered.push(level);
              return;
            }
            let leaves = level.leaves.filter(
              (leaf) => {
                return leaf.filtered;
              }
            );
            if (leaves.length > 0) {
              let clone = factories.cloned(classes.level, {
                id : level.id,
                headerDisplayElement : level.headerDisplayElement,
                parent : level.parent,
                leaves : [...leaves]
              });
              filtered.push(clone);
            }
          }
        );
        return filtered;
      }
    },

    public_interface = {
        actions : actions
    };
    return public_interface;
};
export default ddmodel;