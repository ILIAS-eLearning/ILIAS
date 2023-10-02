ilPortfolio = {
  img_closed: 'templates/default/images/nav/tree_col.svg',
  img_open: 'templates/default/images/nav/tree_exp.svg',
  init() {
    $('a.ilPCMyCoursesToggle').on('click', function (e) {
      // #15509
      e.preventDefault();
      e.stopPropagation();

      const that = this;
      $(this).parent().find('ul').each(function () {
        if ($(this).is(':visible')) {
          $(that).children('img').attr('src', ilPortfolio.img_closed);
        } else {
          $(that).children('img').attr('src', ilPortfolio.img_open);
        }
        $(this).toggle();
      });
    });
  },
};
