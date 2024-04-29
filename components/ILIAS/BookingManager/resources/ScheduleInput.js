'use strict';

$(() => {
  function init() {
    const obj = document.getElementsByTagName('button');
    for (let i = 0; i < obj.length; i++) {
      if (/ilMultiAdd~/.test(obj[i].id)) {
        obj[i].addEventListener("click", addEvent);
      }
      if (/ilMultiRmv~/.test(obj[i].id)) {
        if (obj[i].style.display !== "none") {
          obj[i].addEventListener("click", removeEvent);
        }
      }
    }
  }

  function add(id) {
    // find original field
    const row = document.getElementById('ilFormField~' + id);
    if (!row) {
      return;
    }

    // count original & copies
    let max = 0;
    for (let i = 0; i < row.parentNode.childNodes.length; i++) {
      const id = row.parentNode.childNodes[i].id;
      if (id) {
        const parts = row.parentNode.childNodes[i].id.split("~");
        if (parts[0] === "ilFormField" && parts[2] > max) {
          max = parseInt(parts[2]);
        }
      }
    }
    max = max + 1;

    // create clone and fix ids
    const clone = row.cloneNode(true);
    for (var i = 0; i < clone.childNodes.length; i++) {
      if (/ilMultiAdd~/.test(clone.childNodes[i].id)) {
        clone.childNodes[i].removeEventListener("click", addEvent);

        clone.childNodes[i].id = fixId(clone.childNodes[i].id, max);
        clone.childNodes[i].style.display = "none";
      }
      if (/ilMultiRmv~/.test(clone.childNodes[i].id)) {
        clone.childNodes[i].removeEventListener("click", removeEvent);

        clone.childNodes[i].id = fixId(clone.childNodes[i].id, max);
        clone.childNodes[i].style.display = "";

        clone.childNodes[i].addEventListener("click", removeEvent);
      }
      if (clone.childNodes[i].tagName === "INPUT" && clone.childNodes[i].name) {
        var parts = clone.childNodes[i].name.split("~");
        var new_name = parts[0] + "~" + max;
        if (parts[1].substr(-2, 2) === "[]") {
          new_name = new_name + "[]";
        }
        clone.childNodes[i].name = new_name;
      }
    }

    // insert clone into html
    clone.id = fixId(clone.id, max);
    row.parentNode.appendChild(clone);
  }

  function addEvent(e) {
    const target = (e.currentTarget) ? e.currentTarget : e.srcElement;
    const id = target.id.substr(11);
    add(id);
  }

  function fixId(old_id, new_count) {
    const parts = old_id.split("~");
    return parts[0] + "~" + parts[1] + "~" + new_count;
  }

  function removeEvent(e) {
    const target = (e.currentTarget) ? e.currentTarget : e.srcElement;
    const id = target.id.substr(11);
    if (id.substr(id.length - 2) !== "~0") {
      const row = document.getElementById('ilFormField~' + id);
      row.parentNode.removeChild(row);
    }
  }

  init();
});
