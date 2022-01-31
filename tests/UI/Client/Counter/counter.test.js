import { expect } from 'chai';
import {JSDOM} from 'jsdom';
import fs from 'fs';

import {counterFactory,counterObject} from "../../../../src/UI/templates/js/Counter/src/counter.main";

var test_dom_string = fs.readFileSync('./tests/UI/Client/Counter/CounterTest.html').toString();
var test_document = new JSDOM( test_dom_string ); ;
var $ = global.jQuery = require( 'jquery' )(test_document.window);

var getCounterTest1 = function($){
	return counterFactory($).getCounterObject($("#test1"));

}
var getCounterTest2 = function($){
	return counterFactory($).getCounterObject($("#test2"));
}

describe('Counter Factory', function() {
	it('Counter Factory is here', function() {
		expect(counterFactory).to.not.be.undefined;
	});

	it('getCounterObjectOrNull On Empty', function() {
		expect(counterFactory($).getCounterObjectOrNull($("#testEmpty"))).to.be.null;
	});
	it('Counter Object is here', function() {
		expect(counterObject).to.not.be.undefined;
	});

	it('Get Valid Object', function() {
		expect(getCounterTest1($)).not.to.be.undefined;
		expect(getCounterTest1($)).not.to.be.instanceOf(jQuery);
	});
});
describe('Counter Object', function() {
	it('Get Valid Counts', function() {
		expect(getCounterTest1($).getStatusCount()).to.equal(1);
		expect(getCounterTest1($).getNoveltyCount()).to.equal(5);
	});
	it('Get Valid Counts Test Setters with Div containing two counter Objectgs, which are summed up', function() {
		expect(getCounterTest2($).getStatusCount()).to.equal(2);
		expect(getCounterTest2($).getNoveltyCount()).to.equal(10);
	});

	it('Test Setters', function() {
		var ctest1 = getCounterTest1($);
		expect(ctest1.setStatusTo(2).getStatusCount()).to.equal(2);
		expect(ctest1.getNoveltyCount()).to.equal(5);

		expect(ctest1.setNoveltyTo(7).getStatusCount()).to.equal(2);
		expect(ctest1.getNoveltyCount()).to.equal(7);
	});
	it('Test Setters with Div containing two counter Objectgs, which are summed up', function() {
		var ctest2 = getCounterTest2($);
		expect(ctest2.setStatusTo(2).getStatusCount()).to.equal(4);
		expect(ctest2.getNoveltyCount()).to.equal(10);

		expect(ctest2.setNoveltyTo(7).getStatusCount()).to.equal(4);
		expect(ctest2.getNoveltyCount()).to.equal(14);
	});

	it('Test Increment', function() {
		expect(getCounterTest1($).setNoveltyTo(3).incrementNoveltyCount(2).getNoveltyCount()).to.equal(5);
		expect(getCounterTest1($).setStatusTo(3).incrementStatusCount(2).getStatusCount()).to.equal(5);

		expect(getCounterTest2($).setNoveltyTo(3).incrementNoveltyCount(2).getNoveltyCount()).to.equal(10);
		expect(getCounterTest2($).setStatusTo(3).incrementStatusCount(2).getStatusCount()).to.equal(10);
	});

	it('Test Decrement', function() {
		expect(getCounterTest1($).setNoveltyTo(3).decrementNoveltyCount(2).getNoveltyCount()).to.equal(1);
		expect(getCounterTest1($).setStatusTo(3).decrementStatusCount(2).getStatusCount()).to.equal(1);

		expect(getCounterTest2($).setNoveltyTo(3).decrementNoveltyCount(2).getNoveltyCount()).to.equal(2);
		expect(getCounterTest2($).setStatusTo(3).decrementStatusCount(2).getStatusCount()).to.equal(2);
	});

	it('Test Decrement', function() {
		expect(getCounterTest1($).setNoveltyTo(3).decrementNoveltyCount(2).getNoveltyCount()).to.equal(1);
		expect(getCounterTest1($).setStatusTo(3).decrementStatusCount(2).getStatusCount()).to.equal(1);
	});

	it('Test Get Novelty To Status', function() {
		var counter = getCounterTest1($).setStatusTo(3).setNoveltyTo(2).setTotalNoveltyToStatusCount();

		expect(counter.getStatusCount()).to.equal(5);
		expect(counter.getNoveltyCount()).to.equal(0);

		var counter = getCounterTest2($).setStatusTo(3).setNoveltyTo(2).setTotalNoveltyToStatusCount();
		expect(counter.getStatusCount()).to.equal(14);
		expect(counter.getNoveltyCount()).to.equal(0);
	});
});
