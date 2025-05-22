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
 *********************************************************************/

export default ({closeModal, showModal, node}, labels, invite, userList, send) => {
  const input = node.querySelector('input[type=text]');
  let value = null;
  node.querySelector('.modal-body form').addEventListener('submit', e => {
    e.preventDefault();
    if (value !== null) {
      invite(value);
      closeModal();
    }
    return false;
  });

  const reset = autocomplete(input, labels, userList, send, v => {
    value = v;
  });

  return () => {
    reset();
    showModal();
  };
};

function autocomplete(input, labels, userList, send, setValue) {
  const parent = input.parentNode;
  const container = document.createElement('div');
  const list = document.createElement('ul');
  const nothingFound = document.createElement('div');
  container.classList.add('chat-autocomplete-container');
  container.setAttribute('aria-live', 'asertive');
  container.setAttribute('aria-relevant', 'additions');
  container.setAttribute('role', 'status');
  list.classList.add('chat-autocomplete');
  list.classList.add('ilNoDisplay');
  nothingFound.classList.add('ilNoDisplay');
  nothingFound.appendChild(labels.nothingFound);
  input.setAttribute('autocomplete', 'off');
  container.appendChild(list);
  container.appendChild(nothingFound);
  parent.appendChild(container);

  const select = entry => {
    setValue(entry);
    input.value = entry.value;
  };

  const search = sendSearch => searchForUsers(userList, sendSearch, input.value).then(displayResults(input, list, nothingFound, select, {
    load: () => search(s => send({...s, all: true})),
    label: labels.more,
  }));

  const searchDelayed = debounce(() => search(send), 500);

  input.addEventListener('input', () => {
    setValue(null);
    searchDelayed();
  });

  input.addEventListener('keydown', e => {
    if (e.key === 'ArrowDown' && list.firstChild) {
      list.firstChild.querySelector('button').focus();
    } else if (e.key === 'ArrowUp' && list.lastChild) {
      list.lastChild.querySelector('button').focus();
    }
  })

  return () => {
    setValue(null);
    input.value = '';
    list.innerHTML = '';
    list.classList.add('ilNoDisplay');
    nothingFound.classList.add('ilNoDisplay');
  };
}

function displayResults(input, list, nothingFound, select, more) {
  return ({items: results, hasMoreResults, inputTooShort}) => {
    const clearList = () => {
      list.innerHTML = '';
      list.classList.add('ilNoDisplay');
    };
    const willSelect = entry => () => {
      clearList();
      select(entry);
    };
    if (results.length === 0) {
      if (inputTooShort) {
        clearList();
      } else {
        clearList();
        nothingFound.classList.remove('ilNoDisplay');
        return;
      }
    } else {
      list.innerHTML = '';
      list.classList.remove('ilNoDisplay');
    }

    nothingFound.classList.add('ilNoDisplay');
    results.forEach(entry => list.appendChild(createResultItem(entry, input, willSelect(entry))));

    if (hasMoreResults) {
      list.appendChild(createLoadMoreItem(more.label, () => {
        clearList();
        more.load();
      }));
    }
  };
}

function createResultItem(entry, input, select)
{
  const li = document.createElement('li');
  const button = document.createElement('button');

  button.setAttribute('tabindex', '0');
  button.textContent = entry.label;
  button.addEventListener('click', select);
  button.addEventListener('keydown', e => {
    const cases = {
      'Enter': select,
      'ArrowDown': () => {
        (li.nextSibling || {querySelector: () => input}).querySelector('button').focus();
      },
      'ArrowUp': () => {
        (li.previousSibling || {querySelector: () => input}).querySelector('button').focus();
      },
    };

    (cases[e.key] || Void)();
  });

  li.appendChild(button);

  return li;
}

function createLoadMoreItem(label, loadMore)
{
  const li = document.createElement('li');
  const button = document.createElement('button');
  button.classList.add('load-more');
  button.textContent = label;

  li.appendChild(button);

  button.addEventListener('click', loadMore);

  return li;
}

function debounce(proc, delay) {
  let del = Void;
  return (...args) => {
    return new Promise((ok, err) => {
      del();
      del = window.clearTimeout.bind(
        window,
        window.setTimeout(() => proc(...args).then(ok).catch(err), delay)
      );
    });
  };
}

function searchForUsers(userList, send, search) {
  if (search.length < 3) {
    return Promise.resolve({items: [], hasMoreResults: false, inputTooShort: true});
  }
  return send({search}).then(
    response => ({
      items: Object.values(response.items).filter(item => !userList.includes(item.id)),
      hasMoreResults: response.hasMoreResults || false,
    })
  );
}

function Void() {}
