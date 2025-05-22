il.Awareness = {

  rendered: false,
  base_url: "",
  $body:  $(document.body),
  loader_src: "",


  setBaseUrl: function(url) {
    var t = il.Awareness;
    t.base_url = url;
  },

  getBaseUrl: function() {
    var t = il.Awareness;
    return t.base_url;
  },

  setLoaderSrc: function(loader) {
    var t = il.Awareness;
    t.loader_src = loader;
  },

  getLoaderSrc: function() {
    var t = il.Awareness;
    return t.loader_src;
  },

  init: function() {
    document.querySelectorAll('.ilAwarenessItem div[role="button"]').forEach((el) => {
      el.addEventListener('click', () => {
        const ul = el.parentNode.querySelector('ul');
        if (ul.style.display === 'none') {
          ul.style.display = 'block';
          el.setAttribute('aria-expanded', 'true');
        } else {
          ul.style.display = 'none';
          el.setAttribute('aria-expanded', 'false');
        }
      });
    });
  },


  getContent: function () {
    var t = il.Awareness;
    if (!t.rendered) {
      t.content = $("#awareness-content-container").html();
      $("#awareness-content-container").html("");
      t.updateList("");
      t.rendered = true;
    }
    return t.content;
  },

  ajaxReplaceSuccess: function(o) {
    var t = il.Awareness, cnt;

    // perform page modification
    if(o.html !== undefined)
    {
      t.content = o.html;
      $('#awareness-content').replaceWith(o.html);
      $('#il_awareness_filter').val(o.filter_val);
      t.afterListUpdate();

      cnt = o.cnt.split(":");
      t.setCounter(cnt[0], false);
      t.setCounter(cnt[1], true);

      // throw custom event
      $("#awareness_trigger a").trigger("awrn:shown");
    }
  },

  afterListUpdate: function() {
    var t = il.Awareness;

    $("#il_awrn_filter_form").submit(function (e) {
      var t = il.Awareness;
      $("#il_awrn_filer_btn").html("<img src='" + t.loader_src + "' />");
      t.updateList($("#il_awareness_filter").val());
      e.preventDefault();
    });
    $("#il_awareness_filter").each(function() {
      t = this;
      t.focus();
      if (t.setSelectionRange) {
        var len = $(t).val().length * 2;
        t.setSelectionRange(len, len);
      }
    });

    $("#awareness-list").trigger("il.user.actions.updated", ["awareness-list"]);
  },

  updateList: function(filter) {
    var t = il.Awareness;
    $.ajax({
      url: t.getBaseUrl() + "&cmd=getAwarenessList"
        + "&filter=" + encodeURIComponent(filter),
      dataType: "json"
    }).done(t.ajaxReplaceSuccess);
  },

  setCounter: function(c, highlighted) {
    var id = "#awareness_badge";

    if (highlighted) {
      id = "#awareness_hbadge";
    }
    $(id + " span").html(c);
    if (c > 0) {
      $(id + " .badge").removeClass("ilAwrnBadgeHidden");
    } else {
      $(id + " .badge").addClass("ilAwrnBadgeHidden");
    }
  }
};

/* temporary fix, since initial ajax loading does not work */
il.Util.addOnLoad(function() {
  il.Awareness.afterListUpdate();
})
