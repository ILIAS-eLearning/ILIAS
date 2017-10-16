package com.yahoo.astra.utils
{
	import flash.geom.Point;
	import flash.display.DisplayObject;
	
	/**
	 * Allows you to manipulate display objects based on a registration point other
	 * than the standard (0,0).
	 * 
	 * @author Josh Tynjala
	 */
	public class DynamicRegistration
	{
		/**
		 * Moves a <code>DisplayObject</code> to a new position (x,y) based on a registration point. The
		 * true position of the object will be (x - registration.x, y - registration.y).
		 * 
		 * @param	target				the DisplayObject to move
		 * @param	registration		the registration point of the DisplayObject
		 * @param	x					the new x position, in pixels
		 * @param	y					the new y position, in pixels
		 */
		public static function move(target:DisplayObject, registration:Point, x:Number = 0, y:Number = 0):void
		{
			//generate the location of the registration point in the parent
			registration = target.localToGlobal(registration);
			registration = target.parent.globalToLocal(registration);
			
			//move the target and offset by the registration point
			target.x += x - registration.x;
			target.y += y - registration.y;
		}
		
		/**
		 * Rotates a <code>DisplayObject</code> based on a registration point. 
		 * 
		 * @param	target				the DisplayObject to move
		 * @param	registration		the registration point of the DisplayObject
		 * @param	rotation			the new rotation angle
		 */
		public static function rotate(target:DisplayObject, registration:Point, degrees:Number = 0):void
		{
			changePropertyOnRegistrationPoint(target, registration, "rotation", degrees);
		}
		
		/**
		 * Scales a <code>DisplayObject</code> based on a registration point. 
		 * 
		 * @param	target				the DisplayObject to move
		 * @param	registration		the registration point of the DisplayObject
		 * @param	scaleX				the new x scaling factor
		 * @param	scaleY				the new y scaling factor
		 */
		public static function scale(target:DisplayObject, registration:Point, scaleX:Number = 0, scaleY:Number = 0):void
		{
			changePropertyOnRegistrationPoint(target, registration, "scaleX", scaleX);
			changePropertyOnRegistrationPoint(target, registration, "scaleY", scaleY);
		}
		
		/**
		 * @private
		 * Alters an arbitary property based on the registration point.
		 * 
		 * @param	target				the DisplayObject to move
		 * @param	registration		the registration point of the DisplayObject
		 * @param	propertyName		the property to change
		 * @param	value				the new value of the property to change
		 */
		private static function changePropertyOnRegistrationPoint(target:DisplayObject, registration:Point, propertyName:String, value:Number):void
		{
			//generate the location of the registration point in the parent
			var a:Point = registration.clone();
			a = target.localToGlobal(a);
			a = target.parent.globalToLocal(a);
			
			target[propertyName] = value;
			
			//after the property change, regenerate the location of the registration
			//point in the parent
			var b:Point = registration.clone();
			b = target.localToGlobal(b);
			b = target.parent.globalToLocal(b);
			
			//move the target based on the difference to make it appear the change
			//happened based on the registration point
			target.x -= b.x - a.x;
			target.y -= b.y - a.y;
		}
		
	}
}
