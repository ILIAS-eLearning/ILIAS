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
import codec from '../../resources/js/src/codec.js';

describe('WOPI PostMessage codec', () => {
  it('encodes messages as WOPI JSON strings', () => {
    const data = JSON.parse(codec.encode('wopi', { type: 'Action_Save', payload: { Notify: true } }));
    strict.equal(data.MessageId, 'Action_Save');
    strict.deepEqual(data.Values, { Notify: true });
    strict.equal(typeof data.SendTime, 'number');
  });

  it('sends empty values if there is no payload', () => {
    strict.deepEqual(JSON.parse(codec.encode('wopi', { type: 'Host_PostmessageReady' })).Values, {});
  });

  it('decodes WOPI JSON strings and objects', () => {
    strict.deepEqual(
      codec.decode('wopi', '{"MessageId":"Doc_ModifiedStatus","Values":{"Modified":true}}'),
      { type: 'Doc_ModifiedStatus', payload: { Modified: true } },
    );
    strict.deepEqual(
      codec.decode('wopi', { MessageId: 'App_LoadingStatus' }),
      { type: 'App_LoadingStatus', payload: {} },
    );
  });

  it('ignores everything else', () => {
    ['no json', '{"Values":{}}', '{"MessageId":4}', 'null', null, 42, { foo: 'bar' }].forEach((data) => {
      strict.equal(codec.decode('wopi', data), null);
    });
  });
});
