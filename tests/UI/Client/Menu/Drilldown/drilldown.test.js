import { expect } from 'chai';
import {JSDOM} from 'jsdom';
import fs from 'fs';

import ddmodel from '../../../../../src/UI/templates/js/Menu/src/drilldown.model.js';
import ddmapping from '../../../../../src/UI/templates/js/Menu/src/drilldown.mapping.js';
import ddpersistence from '../../../../../src/UI/templates/js/Menu/src/drilldown.persistence.js';
import dd from '../../../../../src/UI/templates/js/Menu/src/drilldown.main.js';
import drilldown from '../../../../../src/UI/templates/js/Menu/src/drilldown.instances.js';

describe('drilldown', function() {
   
    beforeEach(function(){
        //init test environment
        var dom_string = fs.readFileSync('./tests/UI/Component/Menu/Drilldown/drilldown_test.html').toString(),
            doc = new JSDOM(dom_string);

        doc.getElementById = (id) => { return $('#' + id)[0];};
        global.document = doc;
        global.jQuery = require( 'jquery' )(doc.window);
        global.$ = global.jQuery;

        il = {
            Utilities : {
                 CookieStorage : function (id) {
                    return {
                        items : {},
                        add : function(key, value) {
                            this.items[key] = value;
                        },
                        store : function() {}
                    };
                }
            }
        };
      });


    it('components are defined and provide public interface', function() {
        expect(ddmodel).to.not.be.undefined;
        expect(ddmapping).to.not.be.undefined;
        expect(ddpersistence).to.not.be.undefined;
        expect(drilldown).to.not.be.undefined;
        expect(dd).to.not.be.undefined;

        var ddmain = dd();
        expect(ddmain.init).to.be.an('function');
        expect(ddmain.engage).to.be.an('function');
    });
    
    it('model creates levels, engages/disengages properly', function() {
        var dd_model = ddmodel();
        var l0 = dd_model.actions.addLevel('root'),
            l1 = dd_model.actions.addLevel('1', l0.id),
            l11 = dd_model.actions.addLevel('11', l1.id),
            l2 = dd_model.actions.addLevel('2', l0.id),
            l21 = dd_model.actions.addLevel('21', l2.id),
            l211 = dd_model.actions.addLevel('211', l21.id);
    
        expect(l0.label).to.eql('root');
        expect(l0.id).to.eql('0');
        expect(l0.parent).to.eql(null);

        expect(l11.label).to.eql('11');
        expect(l11.id).to.eql('2');
        expect(l11.parent).to.eql('1');
    
        expect(dd_model.actions.getCurrent()).to.eql(l0);
        expect(l1.engaged).to.be.false;

        dd_model.actions.engageLevel(l1.id);
        expect(l1.engaged).to.be.true;
        expect(dd_model.actions.getCurrent()).to.eql(l1);

        dd_model.actions.engageLevel(l211.id);
        expect(l1.engaged).to.be.false;
        expect(l211.engaged).to.be.true;
        expect(dd_model.actions.getCurrent()).to.eql(l211);
        dd_model.actions.upLevel();
        expect(l21.engaged).to.be.true;
        expect(l211.engaged).to.be.false;
    });

    it('identifies several instances', function() {
        var id = 'dd_one',
            id2 = 'dd_two',
            mock = function(){},
            ddmock = function(a,b,c){
                return {init : function(){}};
            },
            dd_collection = drilldown(mock, mock, mock, ddmock);

        dd_collection.init(id);
        expect(dd_collection.instances[id]).to.not.be.undefined;

        dd_collection.init(id2);
        expect(dd_collection.instances[id2]).to.not.equal(dd_collection.instances[id]);
    });

    it('persistence has internal integrity', function() {
        var p = ddpersistence('id'),
            value = 'test';
        p.store(value);
        expect(p.read()).to.equal(value);
    });


    it('parses, initializes and engages (dom level) ', function() {
        var component = drilldown(
                ddmodel,
                ddmapping,
                ddpersistence,
                dd
            ),
            id = 'id_2',
            signal = 'test_backsignal_id_2',
            persistence_id = 'id_2_cookie',
            menu,
            btns = $('.il-drilldown ul li button');
            
            component.init(id, signal, persistence_id);
            menu = component.instances[id];
            expect(menu).to.be.an('object');
            
            expect($('header h2').html()).to.equal('root');
            expect(btns[1].className).to.equal('menulevel');

            btns[1].click();
            expect($('header h2').html()).to.equal('1');
            expect(btns[1].className).to.equal('menulevel engaged');

            btns[3].click();
            expect($('header h2').html()).to.equal('1.2');
            expect(btns[1].className).to.equal('menulevel');
            expect(btns[3].className).to.equal('menulevel engaged');        
    });

});
