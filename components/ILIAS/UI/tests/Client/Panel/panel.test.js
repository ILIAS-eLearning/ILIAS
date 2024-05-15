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

import { expect } from 'chai';

import panel from '../../../resources/js/Panel/src/panel.main';

describe('Panel', () => {
    it('components are defined', () => {
        expect(panel).to.not.be.undefined;
    });

    const p = panel();

    it('public interface is defined on panel', () => {
        expect(p.initExpandable).to.be.a('function');
        expect(p.onCollapseCmd).to.be.a('function');
        expect(p.onExpandCmd).to.be.a('function');
    });
});
