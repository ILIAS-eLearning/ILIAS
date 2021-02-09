var CounterTests = {
	html: "Counter/CounterTest.html",

	testGetCounterOrNullOnEmpty(){
		return il.UI.counter.getCounterObjectOrNull($("#testEmpty")) === null;
	},
	testGetCounterOrNullOnTest1(){
		return il.UI.counter.getCounterObjectOrNull($("#test1")).getStatusCount()==1;
	},
	testInvalidThrowsExceptionA: function(){
		try{
			var counter = il.UI.counter.getCounterObject("invalid");
		}catch(e){
			return true;
		}
	},
	testInvalidThrowsExceptionB: function(){
		try{
			var counter = il.UI.counter.getCounterObject("#test1");
		}catch(e){
			return true;
		}
	},
	testGetValidObject: function(){
		return (!(getCounterTest1() instanceof jQuery));
	},
	testCountStatus: function(){
		return (getCounterTest1().getStatusCount()==1);
	},
	testNoveltyStatus: function(){
		return (getCounterTest1().getNoveltyCount()==5);
	},
	testSetStatusTo: function(){
		return (getCounterTest1().setStatusTo(2).getStatusCount()==2
			&& getCounterTest1().getNoveltyCount()==5);
	},
	testSetNoveltyTo: function(){
		return (getCounterTest1().setNoveltyTo(7).getStatusCount()==2
			&& getCounterTest1().getNoveltyCount()==7);
	},
	testIncrementNoveltyCount: function(){
		return getCounterTest1().setNoveltyTo(3).incrementNoveltyCount(2).getNoveltyCount()==5;
	},
	testDecrementNoveltyCount: function(){
		return getCounterTest1().setNoveltyTo(3).decrementNoveltyCount(2).getNoveltyCount()==1;
	},
	testIncrementStatusCount: function(){
		return getCounterTest1().setStatusTo(3).incrementStatusCount(2).getStatusCount()==5;
	},
	testDecrementStatusCount: function(){
		return getCounterTest1().setStatusTo(3).decrementStatusCount(2).getStatusCount()==1;
	},
	setNoveltyToStatus: function(){
		var counter = getCounterTest1().setStatusTo(3).setNoveltyTo(2).setTotalNoveltyToStatusCount();
		return counter.getStatusCount()==5 && counter.getNoveltyCount()==0;
	},

	testCountStatus2: function(){
		return (getCounterTest2().getStatusCount()==2);
	},
	testNoveltyStatus2: function(){
		return (getCounterTest2().getNoveltyCount()==10);
	},
	testSetStatusTo2: function(){
		return (getCounterTest2().setStatusTo(2).getStatusCount()==4
			&& getCounterTest2().getNoveltyCount()==10);
	},
	testSetNoveltyTo2: function(){
		return (getCounterTest2().setNoveltyTo(7).getStatusCount()==4
			&& getCounterTest2().getNoveltyCount()==14);
	},
	testIncrementNoveltyCount2: function(){
		return getCounterTest2().setNoveltyTo(3).incrementNoveltyCount(2).getNoveltyCount()==10;
	},
	testDecrementNoveltyCount2: function(){
		return getCounterTest2().setNoveltyTo(3).decrementNoveltyCount(2).getNoveltyCount()==2;
	},
	testIncrementStatusCount2: function(){
		return getCounterTest2().setStatusTo(3).incrementStatusCount(2).getStatusCount()==10;
	},
	testDecrementStatusCount2: function(){
		return getCounterTest2().setStatusTo(3).decrementStatusCount(2).getStatusCount()==2;
	},
	setNoveltyToStatus2: function(){
		var counter = getCounterTest2().setStatusTo(3).setNoveltyTo(2).setTotalNoveltyToStatusCount();
			return counter.getStatusCount()==14 && counter.getNoveltyCount()==0;
		}
}

var getCounterTest1 = function(){
	return il.UI.counter.getCounterObject($("#test1"));
}
var getCounterTest2 = function(){
	return il.UI.counter.getCounterObject($("#test2"));
}