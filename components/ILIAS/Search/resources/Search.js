/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

il.Search = {
  syncFilterScope(filterId, value) {
    /*
    todo: querySelector may be too weak in future,
     but currently there is only one select input in the filter
     */
    const element = document.getElementById(filterId).querySelector('select');
    element.value = value;
  },
};
