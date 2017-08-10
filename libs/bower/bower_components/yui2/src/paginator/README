Paginator Release Notes

2.9.0
    * alwaysVisible=false now works when rowsPerPageOptions is supplied
      an array of objects (e.g. [{ label: 'A few', value: '5' }, ... ]
    * Added JumpToPageDropdown UI Component (hat tip to Matt Parker)
    * renderUIComponent is now chainable
    * Add link title configuration attributes (hat tip to Pete Peterson)
    * RowsPerPageDropdown configuration rowsPerPageOptions text now assigned
      to option.text rather than option.innerHTML.
    * IDs generated for UI components now use Dom.generateId for safety from
      creating duplicate IDs when using in YUI 3 as yui2-paginator

2.8.1
    * No changes

2.8.0
    * Replaced non-breaking space entity with regular space in next, previous,
      first, and last links to support xhtml
    * updateOnChange configuration is deprecated in favor of a changeRequest
      subscriber that immediately calls setState(newState)
    * setState now only updates known attributes
    * render broken into render, _renderTemplate, and renderUIComponent
    * Now always builds DOM structure, but doesn't remove display:none if
      alwaysVisible is set to false and conditions for visibility are not met
    * render() is now chainable
    * Now hides the container when rowsPerPage <= totalRecords.  It used to be
      only < not <=
    * destroy() now calls unsubscribeAll() to flush event listeners

2.7.0
    * Code trimming and restructure for better compression
    * Added support for numeric strings pag.set('totalRecords','100');
    * Added support for "all" value of RowsPerPageDropdown

2.6.0
    * Initial standalone realease (extracted from DataTable)
