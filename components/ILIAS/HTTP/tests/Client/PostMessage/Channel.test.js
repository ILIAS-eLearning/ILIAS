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
 ********************************************************************
 */

import { describe, it } from 'node:test';
import { strict } from 'node:assert/strict';
import Channel from '../../../resources/js/postmessage/src/Channel.js';
import { envelopeCodec, MARKER } from '../../../resources/js/postmessage/src/protocol.js';

/**
 * Minimal window: postMessage() dispatches asynchronously to the listeners of
 * the receiving window, with the origin of the sender, if the target origin
 * matches. This mirrors the browser behaviour the channel relies on.
 */
class FakeWindow {
  constructor(origin) {
    this.origin = origin;
    this.listeners = [];
    this.received = [];
  }

  addEventListener(type, listener) {
    this.listeners.push(listener);
  }

  removeEventListener(type, listener) {
    this.listeners = this.listeners.filter((l) => l !== listener);
  }

  deliver(data, origin, source) {
    this.received.push({ data, origin });
    setTimeout(() => this.listeners.forEach((l) => l({ data, origin, source })), 0);
  }

  pair(other) {
    this.postMessage = (data, targetOrigin) => {
      if (targetOrigin !== this.origin) {
        return;
      }
      this.deliver(data, other.origin, other);
    };
  }
}

function connect() {
  const host = new FakeWindow('https://ilias.example.org');
  const guest = new FakeWindow('https://content.example.org');
  host.pair(guest);
  guest.pair(host);
  const hostChannel = new Channel(host, () => guest, guest.origin, 'test');
  const guestChannel = new Channel(guest, () => host, host.origin, 'test');
  return {
    host, guest, hostChannel, guestChannel,
  };
}

const tick = () => new Promise((resolve) => { setTimeout(resolve, 5); });

describe('postMessage channel', () => {
  it('rejects wildcard and empty origins', () => {
    const w = new FakeWindow('https://ilias.example.org');
    strict.throws(() => new Channel(w, () => w, '*', 'test'));
    strict.throws(() => new Channel(w, () => w, '', 'test'));
    strict.throws(() => new Channel(w, () => w, 'null', 'test'));
  });

  it('addresses messages to the peer origin only', () => {
    const { guest, hostChannel } = connect();
    hostChannel.send('ping', { a: 1 });
    strict.equal(guest.received.length, 1);
    strict.equal(guest.received[0].data[MARKER], 1);
    strict.equal(guest.received[0].data.type, 'ping');
  });

  it('dispatches events to the registered handler', async () => {
    const { hostChannel, guestChannel } = connect();
    let received = null;
    guestChannel.on('ping', (payload) => { received = payload; });
    hostChannel.send('ping', { a: 1 });
    await tick();
    strict.deepEqual(received, { a: 1 });
  });

  it('answers requests with the result of the handler', async () => {
    const { hostChannel, guestChannel } = connect();
    guestChannel.on('sum', (payload) => payload.a + payload.b);
    strict.equal(await hostChannel.request('sum', { a: 2, b: 3 }), 5);
  });

  it('waits for asynchronous handlers', async () => {
    const { hostChannel, guestChannel } = connect();
    guestChannel.on('later', () => new Promise((resolve) => { setTimeout(() => resolve('done'), 5); }));
    strict.equal(await hostChannel.request('later'), 'done');
  });

  it('rejects requests if the handler fails or is missing', async () => {
    const { hostChannel, guestChannel } = connect();
    guestChannel.on('fail', () => { throw new Error('broken'); });
    await strict.rejects(hostChannel.request('fail'), /broken/);
    await strict.rejects(hostChannel.request('unknown'), /No handler/);
  });

  it('times out unanswered requests', async () => {
    const { hostChannel, guest } = connect();
    guest.postMessage = () => {};
    await strict.rejects(hostChannel.request('ping', null, 10), /timed out/);
  });

  it('ignores messages of foreign origins', async () => {
    const { host, guest, hostChannel } = connect();
    let called = false;
    hostChannel.on('ping', () => { called = true; });
    host.deliver(envelopeCodec.encode('test', { type: 'ping', payload: null }), 'https://evil.example.org', guest);
    await tick();
    strict.equal(called, false);
  });

  it('ignores messages of other windows of the same origin', async () => {
    const { host, hostChannel } = connect();
    let called = false;
    hostChannel.on('ping', () => { called = true; });
    const other = new FakeWindow('https://content.example.org');
    host.deliver(envelopeCodec.encode('test', { type: 'ping', payload: null }), other.origin, other);
    await tick();
    strict.equal(called, false);
  });

  it('ignores messages of other channels and without marker', async () => {
    const { host, guest, hostChannel } = connect();
    let called = false;
    hostChannel.on('ping', () => { called = true; });
    host.deliver(envelopeCodec.encode('other', { type: 'ping', payload: null }), guest.origin, guest);
    host.deliver({ type: 'ping', channel: 'test' }, guest.origin, guest);
    host.deliver('ping', guest.origin, guest);
    await tick();
    strict.equal(called, false);
  });

  it('works with another codec', async () => {
    // e.g. a protocol like WOPI: JSON strings with a MessageId
    const codec = {
      encode: (channel, message) => JSON.stringify({ MessageId: message.type, Values: message.payload }),
      decode: (channel, data) => {
        try {
          const parsed = JSON.parse(data);
          return typeof parsed.MessageId === 'string' ? { type: parsed.MessageId, payload: parsed.Values } : null;
        } catch (e) {
          return null;
        }
      },
    };
    const host = new FakeWindow('https://ilias.example.org');
    const editor = new FakeWindow('https://office.example.org');
    host.pair(editor);
    editor.pair(host);
    const hostChannel = new Channel(host, () => editor, editor.origin, 'wopi', codec);
    let received = null;
    hostChannel.on('App_LoadingStatus', (payload) => { received = payload; });
    host.postMessage(JSON.stringify({ MessageId: 'App_LoadingStatus', Values: { Status: 'Document_Loaded' } }), host.origin);
    host.postMessage('no json', host.origin);
    await tick();
    strict.deepEqual(received, { Status: 'Document_Loaded' });
    hostChannel.send('Host_PostmessageReady', {});
    strict.equal(editor.received[0].data, '{"MessageId":"Host_PostmessageReady","Values":{}}');
  });

  it('rejects pending requests when destroyed', async () => {
    const { hostChannel, guest } = connect();
    guest.postMessage = () => {};
    const pending = hostChannel.request('ping', null, 0);
    hostChannel.destroy();
    await strict.rejects(pending, /closed/);
  });
});
