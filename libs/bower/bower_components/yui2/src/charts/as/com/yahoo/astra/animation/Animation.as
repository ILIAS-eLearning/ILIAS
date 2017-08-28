package com.yahoo.astra.animation
{
	import flash.events.EventDispatcher;
	import flash.events.TimerEvent;
	import flash.utils.Dictionary;
	import flash.utils.Timer;
	import flash.utils.getTimer;

	//--------------------------------------
	//  Events
	//--------------------------------------
	
	/**
	 * Dispatched when the Animation instance starts.
	 * 
	 * @eventType com.yahoo.astra.animation.AnimationEvent.START
	 */
	[Event(name="start", type="com.yahoo.astra.animation.AnimationEvent")]

	/**
	 * Dispatched when the Animation instance has changed.
	 * 
	 * @eventType com.yahoo.astra.animation.AnimationEvent.UPDATE
	 */
	[Event(name="update", type="com.yahoo.astra.animation.AnimationEvent")]

	/**
	 * Dispatched when the Animation instance has finished.
	 * 
	 * @eventType com.yahoo.astra.animation.AnimationEvent.COMPLETE
	 */
	[Event(name="complete", type="com.yahoo.astra.animation.AnimationEvent")]

	/**
	 * Dispatched when the Animation instance is paused.
	 * 
	 * @eventType com.yahoo.astra.animation.AnimationEvent.PAUSE
	 */
	[Event(name="pause", type="com.yahoo.astra.animation.AnimationEvent")]
	
	/**
	 * An ultra lightweight animation engine.
	 * 
	 * @example The following code animates a Shape from its current location to a new location over a period of two seconds:
	 * <listing version="3.0">
	 * // create the square
	 * var square:Shape = new Shape();
	 * square.graphics.beginFill( 0xcccccc );
	 * square.graphics.drawRect( 0, 0, 20, 20 );
	 * square.graphics.endFill();
	 * square.x = 20;
	 * square.y = 20;
	 * this.addChild( square );
	 * 
	 * // animate the square's position
	 * var animation:Animation = Animation.create( square, 2000, { x: 100, y: 200 } );
	 * </listing>
	 * 
	 * @example The following code will draw a circle and use an Animation instance
	 * 	to change its alpha property from 0.0 to 1.0 over a period of 1.5 seconds.
	 * 	It will set the easingFunction property to <code>Back.easeOut</code>, which
	 *	is an easing function included with Flash CS3. In order to implement this 
	 *	example, you will need to save this code as a class file and set it as the   
	 *	Document Class of your flash application.
	 * 
	 * <listing version="3.0">
	 * 	package
	 * 	{
	 * 		import fl.motion.easing.Back;
	 * 		import flash.display.Shape; 
	 * 		import flash.display.Sprite;
	 * 		import com.yahoo.astra.animation.Animation;
	 * 		import com.yahoo.astra.animation.AnimationEvent;
	 * 	
	 * 		public class AnimationExample extends Sprite
	 * 		{
	 * 			public function AnimationExample()
	 * 			{
	 * 				// Create a simple circular display object
	 * 				this.circle = new Shape();
	 * 				this.circle.graphics.beginFill(0xcccccc);
	 * 				this.circle.graphics.drawEllipse(0, 0, 50, 50);
	 * 				this.circle.graphics.endFill();
	 * 				this.addChild(circle);
	 * 	
	 * 				// Create the instance animating over 1500ms from 0 to 1
	 * 				this.animation = new Animation( 1500, { alpha: 0.0 }, { alpha: 1.0 } );
	 * 	
	 * 				// Use an easing equation
	 * 				this.animation.easingFunction = Back.easeOut;
	 * 	
	 * 				// Listen for events to update our circle's values
	 * 				this.animation.addEventListener( AnimationEvent.UPDATE, animationUpdateHandler );
	 * 				this.animation.addEventListener( AnimationEvent.COMPLETE, animationCompleteHandler );
	 * 			}
	 * 	
	 * 			// Should be a member variable so that the garbage collector doesn't
	 * 			// remove the instance from memory before it finishes
	 * 			private var animation:Animation;
	 * 	
	 * 			// The display object whose properties we will animate
	 * 			private var circle:Shape;
	 * 	
	 * 			private function animationUpdateHandler(event:AnimationEvent):void
	 * 			{
	 * 				this.circle.alpha = event.parameters.alpha;
	 * 			}
	 * 	
	 * 			private function animationCompleteHandler(event:AnimationEvent):void
	 * 			{
	 * 				this.animationUpdateHandler(event);
	 * 	
	 * 				// Set the animation instance to null to ensure garbage collection
	 * 				this.animation = null;
	 * 			}
	 * 		}
	 * 	}
	 * </listing>
	 * @author Josh Tynjala
	 */
	public class Animation extends EventDispatcher
	{
		
	//--------------------------------------
	//  Class Properties
	//--------------------------------------
	
		/**
		 * @private
		 * Hash to get an Animation's target.
		 */
		private static var animationToTarget:Dictionary = new Dictionary();
		
		/**
		 * @private
		 * Hash to get the a target's Animation.
		 */
		private static var targetToAnimations:Dictionary = new Dictionary();
	
		/**
		 * @private
		 * The main timer shared by all Animation instances.
		 */
		private static var mainTimer:Timer = new Timer(10);
	
	//--------------------------------------
	//  Class Methods
	//--------------------------------------
		
		/**
		 * Animates one or more properties of a target object. Uses the current values
		 * of these properties as the starting values.
		 *
		 * @param target			the object whose properties will be animated.
		 * @param duration			the time in milliseconds over which the properties will be animated.		 
		 * @param parameters		an object containing keys of property names on the object and the ending values.
		 * @param autoStart			if true (the default), the animation will begin automatically.
		 *							if false, the returned Animation object will not automatically begin, and
		 *							one must call the <code>start()</code> function to make it run.
		 * @param clearAllRunning	If true, all other animations started with <code>create()</code> for this target will be cleared.
		 * 
		 * @return					The newly-created Animation instance
		 */
		public static function create(target:Object, duration:int, parameters:Object, autoStart:Boolean = true, clearAllRunning:Boolean = false):Animation
		{
			var animations:Array = targetToAnimations[target] as Array;
			if(!animations)
			{
				animations = [];
				targetToAnimations[target] = animations;
			}
			
			//if requested, stop all other animations running for this target
			if(clearAllRunning && animations.length > 0)
			{
				var animationCount:int = animations.length;
				for(var i:int = 0; i < animationCount; i++)
				{
					var oldAnimation:Animation = Animation(animations[i]);
					//stop it at the current position
					oldAnimation.pause();
					removeAnimation(oldAnimation);
				}
			}
			
			//create the start parameters from the existing properties
			var startParameters:Object = {};
			for(var prop:String in parameters)
			{
				if(target.hasOwnProperty(prop))
				{
					startParameters[prop] = target[prop];
				}
				else startParameters[prop] = 0;
			}
			
			//create the Animation instance
			var animation:Animation = new Animation(duration, startParameters, parameters, autoStart);
			animation.addEventListener(AnimationEvent.UPDATE, tweenUpdateHandler);
			animation.addEventListener(AnimationEvent.COMPLETE, tweenCompleteHandler);
			animations.push(animation);
			
			//reference the target so that we may remove the animation later
			animationToTarget[animation] = target;
			
			return animation;
		}
		
		/**
		 * Immediately destroys an animation instantiated with <code>create()</code>.
		 */
		public static function kill(animation:Animation):void
		{
			if(!animation)
			{
				return;
			}
		
			if(animation.active)
			{
				animation.pause();
			}
			removeAnimation(animation);
		}
		
		/**
		 * @private
		 * Handles updating the properties on a Animation target.
		 */
		private static function tweenUpdateHandler(event:AnimationEvent):void
		{
			var animation:Animation = Animation(event.target);
			var target:Object = animationToTarget[animation];
			var updatedParameters:Object = event.parameters;
			for(var prop:String in updatedParameters)
			{
				if(target.hasOwnProperty(prop))
				{
					target[prop] = updatedParameters[prop];
				}
			}
		}
		
		/**
		 * @private
		 * Completes a tween for a Animation target.
		 */
		private static function tweenCompleteHandler(event:AnimationEvent):void
		{
			tweenUpdateHandler(event);
			
			var animation:Animation = Animation(event.target);
			//if the animation is active, that means it has been restarted
			//and we can leave it running. our listeners will still be valid. 
			if(!animation.active)
			{
				removeAnimation(animation);
			}
		}
		
		/**
		 * @private
		 * Removes an Animation and its target from management.
		 */
		private static function removeAnimation(animation:Animation):void
		{
			if(!animation)
			{
				return;
			}
			
			var target:Object = animationToTarget[animation];
			animationToTarget[animation] = null;
			
			if(target)
			{
				//remove the reference to the animation
				var animations:Array = targetToAnimations[target] as Array;
				var index:int = animations.indexOf(animation); 
				animations.splice(index, 1);
			}
			
			animation.removeEventListener(AnimationEvent.UPDATE, tweenUpdateHandler);
			animation.removeEventListener(AnimationEvent.COMPLETE, tweenCompleteHandler);
		}
		
		/**
		 * @private
		 * Animation uses a single global Timer to save CPU time. This function lets each
		 * individual instance listen for the timer's events.
		 */
		private static function startListenToTimer(handler:Function):void
		{
			Animation.mainTimer.addEventListener(TimerEvent.TIMER, handler, false, 0, true);
			//if this is the first listener, start the timer
			if(!Animation.mainTimer.running)
			{
				Animation.mainTimer.start();
			}
		}
		

		/**
		 * @private
		 * Animation uses a single global Timer to save CPU time. This function lets each
		 * individual instance stop listening for the timer's events.
		 */
		private static function stopListenToTimer(handler:Function):void
		{
			Animation.mainTimer.removeEventListener(TimerEvent.TIMER, handler);
			//if the timer doesn't have any more listeners, we don't need to keep it running
			if(!Animation.mainTimer.hasEventListener(TimerEvent.TIMER))
			{
				Animation.mainTimer.stop();
			}
		}
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
		
		/**
		 * Constructor.
		 * 
		 * @param duration		the time in milliseconds that the tween will run
		 * @param start			the starting values of the tween
		 * @param end			the ending values of the tween
		 * @param autoStart		if false, the tween will not run until start() is called
		 */
		public function Animation(duration:int, start:Object, end:Object, autoStart:Boolean = true)
		{
			super();
			this._duration = duration;
			this._startParameters = start;
			this.endParameters = end;
			
			if(autoStart)
			{
				this.start();
			}
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
		
		/**
		 * @private
		 * Storage for the active property.
		 */
		private var _active:Boolean = false;
		
		/**
		 * If true, the animation is currently running.
		 */
		public function get active():Boolean
		{
			return this._active;
		}
		
		/**
		 * @private
		 * The time at which the animation last started running. If it has been paused
		 * one or more times, this value is reset to the restart time.
		 */
		private var _startTime:int;
		
		/**
		 * @private
		 * If the animation is paused, the running time is saved here.
		 */
		private var _savedRuntime:int;
		
		/**
		 * @private
		 * Storage for the duration property.
		 */
		private var _duration:int;
		
		/**
		 * The duration in milliseconds that the animation will run.
		 */
		public function get duration():int
		{
			return this._duration;
		}
		
		/**
		 * @private
		 * Storage for the starting values.
		 */
		private var _startParameters:Object;
		
		/**
		 * @private
		 * Storage for the ending values.
		 */
		private var _endParameters:Object;
		
		/**
		 * @private
		 * Used to determine the "ranges" between starting and ending values.
		 */
		protected function get endParameters():Object
		{
			return this._endParameters;
		}
		
		/**
		 * @private
		 */
		protected function set endParameters(value:Object):void
		{
			this._ranges = {};
			for(var prop:String in value)
			{
				var startValue:Number = Number(this._startParameters[prop]);
				var endValue:Number = Number(value[prop]);
				var range:Number = endValue - startValue;
				this._ranges[prop] = range;
			}
			this._endParameters = value;
		}
		
		/**
		 * @private
		 * The difference between the startParameters and endParameters values.
		 */
		private var _ranges:Object;
		
		/**
		 * @private
		 * Storage for the easingFunction property.
		 */
		private var _easingFunction:Function = function(t:Number, b:Number, c:Number, d:Number):Number
		{
			return c * t / d + b;
		}
		
		/**
     	 * The easing function which is used with the tween.
		 */
		public function get easingFunction():Function
		{
			return this._easingFunction;
		}
		
		/**
		 * @private
		 */
		public function set easingFunction(value:Function):void
		{
			this._easingFunction = value;
		}
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------

		/**
		 * Starts the tween. Should be used to restart a paused tween, or to
		 * start a new tween with autoStart disabled.
		 */
		public function start():void
		{
			Animation.startListenToTimer(this.timerUpdateHandler);
			this._startTime = getTimer();
			this._active = true;
			this.dispatchEvent(new AnimationEvent(AnimationEvent.START, this._startParameters));
			this.dispatchEvent(new AnimationEvent(AnimationEvent.UPDATE, this._startParameters));
		}
		
		/**
		 * Pauses a tween so that it may be restarted again with the same
		 * timing.
		 */
		public function pause():void
		{
			Animation.stopListenToTimer(this.timerUpdateHandler);
			this._savedRuntime += getTimer() - this._startTime;
			this._active = false;
			
			this.dispatchEvent(new AnimationEvent(AnimationEvent.PAUSE, update(this._savedRuntime)));
		}
		
		/**
		 * Swaps the start and end parameters and restarts the animation.
		 */
		public function yoyo():void
		{
			this.pause();
			this._savedRuntime = 0;
			
			var temp:Object = this._startParameters;
			this._startParameters = this.endParameters;
			this.endParameters = temp;
			this.start();
		}
		
		/**
		 * Forces a tween to its completion values.
		 */
		public function end():void
		{
			Animation.stopListenToTimer(this.timerUpdateHandler);
			this._active = false;
			this.dispatchEvent(new AnimationEvent(AnimationEvent.COMPLETE, this.endParameters));
		}
		
	//--------------------------------------
	//  Private Methods
	//--------------------------------------
	
		/**
		 * @private
		 */
		private function timerUpdateHandler(event:TimerEvent):void
		{
			var runtime:int = this._savedRuntime + getTimer() - this._startTime;
			if(runtime >= this._duration)
			{
				this.end();
				return;
			}
			
			this.dispatchEvent(new AnimationEvent(AnimationEvent.UPDATE, this.update(runtime)));
		}
	
		/**
		 * @private
		 * Generates updated values for the animation based on the current time.
		 */
		private function update(runtime:int):Object
		{
			//can easily handle parameters as hashes or Arrays.
			var updated:Object;
			if(this._startParameters is Array)
			{
				updated = [];
			}
			else
			{
				updated = {};
			}
			for(var prop:String in this._ranges)
			{
				var startValue:Number = this._startParameters[prop] as Number;
				var range:Number = this._ranges[prop];
				updated[prop] = this._easingFunction(runtime, startValue, range, this._duration);
			}
			return updated;
		}
		
	}
}