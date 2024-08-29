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

export default ({closeModal, showModal, node}, translation, iliasConnector, userList, send) => {
  const input = node.querySelector('input[type=text]');
  let value = null;

  node.querySelector('form').addEventListener('submit', e => {
    e.preventDefault();
    if (value != null) {
      iliasConnector.inviteToPrivateRoom(value, 'byId');
      closeModal();
    }
    return false;
  });

  const reset = autocomplete(input, userList, send, v => {
    value = v;
  });

  return () => {
    reset();
    showModal();
  };
};

function autocomplete(node, userList, send, setId) {
  const p = node.parentNode;
  const list = document.createElement('div');
  list.classList.add('chat-autocomplete');
  node.setAttribute('autocomplete', 'off');
  p.appendChild(list);

  const set = entry => {
    setId(entry.id);
    node.value = entry.value;
  };

  const search = debounce(
    () => searchForUsers(userList, send, node.value).then(displayResults(list, set)),
    500
  );

  node.addEventListener('input', () => {
    setId(null);
    search();
  });

  return () => {
    setId(null);
    node.value = '';
    list.innerHTML = '';
  };
}

function displayResults(node, set) {
  return results => {
    node.innerHTML = '';
    results.forEach(entry => {
      const b = document.createElement('button');
      b.textContent = entry.label;
      node.appendChild(b);
      b.addEventListener('click', () => {
        node.innerHTML = '';
        set(entry);
      });
    });
  };
}

function debounce(proc, delay) {
  let del = () => {};
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

const call = m => o => o[m]();

function searchForUsers(userList, send, search) {
  if (search.length < 3) {
    return Promise.resolve([]);
  }
  return send('inviteUsersToPrivateRoom-getUserList', {q: search}).then(call('json')).then(
    response => {
      return response.items.filter(item => !userList.has(item.id));
    }
  );
}
