package com.yahoo.astra.utils
{
	import flash.utils.getQualifiedClassName;
	
	/**
	 * Creates an instance of the specified class. Sets initial properties, and calls specified methods.
	 * 
	 * @author Josh Tynjala
	 */
	public class InstanceFactory
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
		
		/**
		 * Constructor.
		 */
		public function InstanceFactory(targetClass:Class, properties:Object = null, methods:Object = null)
		{
			this.targetClass = targetClass;
			this.properties = properties;
			this.methods = methods;
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
		
		/**
		 * @private
		 * Storage for the targetClass property.
		 */
		private var _targetClass:Class = Object;
		
		/**
		 * The class that will be instantiated.
		 */
		public function get targetClass():Class
		{
			return this._targetClass;
		}
		
		/**
		 * @private
		 */
		public function set targetClass(value:Class):void
		{
			this._targetClass = value;
		}
		
		/**
		 * Storage for the properties property.
		 */
		private var _properties:Object;
		
		/**
		 * The initial values to pass to the properties of the
		 * newly-instantiated object.
		 */
		public function get properties():Object
		{
			return this._properties;
		}
		
		/**
		 * @private
		 */
		public function set properties(value:Object):void
		{
			this._properties = value;
		}
		
		/**
		 * @private
		 * Storage for the methods property.
		 */
		private var _methods:Object;
		
		/**
		 * A set of methods to call once the object has been created and
		 * properties have been initialized. Format is a set of key-value pairs
		 * where the key is the name of the method and the value is an Array
		 * of parameter values.
		 * 
		 * <p>Example: <code>{ load: [ "image.gif" ] }</code></p>
		 */
		public function get methods():Object
		{
			return this._methods;
		}
		
		/**
		 * @private
		 */
		public function set methods(value:Object):void
		{
			this._methods = value;
		}
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
	
		/**
		 * Creates a new instance of the target class and initializes it.
		 */
		public function createInstance():Object
		{
			var instance:Object = new targetClass();
			this.restoreInstance(instance);
			return instance;
		}
		
		/**
		 * Initializes an object with the properties and methods. The object
		 * must be an instance of the <code>targetClass</code> property, or
		 * this method will throw an <code>ArgumentError</code>.
		 */
		public function restoreInstance(instance:Object):void
		{
			if(!(instance is targetClass))
			{
				throw new ArgumentError("Value to be initialized must be an instance of " + getQualifiedClassName(this.targetClass)); 
			}
			
			//set initial properties
			if(this.properties)
			{
				for(var propName:String in this.properties)
				{
					if(instance.hasOwnProperty(propName))
					{
						instance[propName] = properties[propName];
					}
				}
			}
			
			//make initial method calls
			//use case: Loader.load()
			if(this.methods)
			{
				for(var methodName:String in this.methods)
				{
					if(instance[methodName] is Function)
					{
						var args:Array = this.methods[methodName] as Array;
						instance[methodName].apply(instance, args);
					}
				}
			}
		}
		
	}
	
}
