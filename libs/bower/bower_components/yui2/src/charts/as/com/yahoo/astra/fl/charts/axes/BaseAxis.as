package com.yahoo.astra.fl.charts.axes
{
    import com.yahoo.astra.fl.charts.IChart;
    import com.yahoo.astra.display.BitmapText;
    import fl.core.UIComponent;
    import flash.text.TextFormat;
    import flash.text.TextFieldAutoSize;
    import flash.events.ErrorEvent;
    import com.yahoo.astra.fl.charts.CartesianChart;
    import com.yahoo.astra.utils.AxisLabelUtil;
    import flash.events.EventDispatcher;

    /**
     * Implements some of the most common axis functionality
     * to prevent duplicate code in IAxis implementations.
     * 
     * <p>This class is not meant to be instantiated directly! It is an abstract base class.</p>
     * 
     * @author Josh Tynjala
     */
    public class BaseAxis extends EventDispatcher
    {
        
    //--------------------------------------
    //  Constructor
    //--------------------------------------

        /**
         * Constructor.
         */
        public function BaseAxis()
        {
        }

    //--------------------------------------
    //  Properties
    //--------------------------------------
        /**
         * @private
         * Placeholder for width
         */
        private var _width:Number = 0;
        
        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#width
         */
        public function get width():Number
        {
            return this._width;
        }
        
        /**
         * @private (setter)
         */
        public function set width(value:Number):void
        {
            this._width = value;
        }
        
        /**
         * @private
         * Placeholder for height
         */ 
        private var _height:Number = 0;
        
        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#height
         */
        public function get height():Number
        {
            return this._height;
        }
        
        /**
         * @private (setter)
         */
        public function set height(value:Number):void
        {
            this._height = value;
        }

        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#order
         */ 
        public var order:String = "primary";
        
        /**
         * @private
         * Storage for the chart property.
         */
        private var _chart:IChart;

        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#chart
         */
        public function get chart():IChart
        {
            return this._chart;
        }
        
        /**
         * @private
         */
        public function set chart(value:IChart):void
        {
            this._chart = value;
        }
        
        /**
         * @private
         * Storage for the renderer property.
         */
        private var _renderer:IAxisRenderer;
        
        //TODO: Consider having the renderer know about the axis
        //rather than the axis knowing about the renderer. This
        //change will allow multiple views to this model.
        //if this is implemented, a separate controller will be
        //needed too.
        /**
         * The visual renderer applied to this axis.
         */
        public function get renderer():IAxisRenderer
        {
            return this._renderer;
        }
        
        /**
         * @private
         */
        public function set renderer(value:IAxisRenderer):void
        {
            this._renderer = value;
        }
        
        /**
         * @private
         * Storage for the labelFunction property.
         */
        private var _labelFunction:Function;
        
        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#labelFunction
         */
        public function get labelFunction():Function
        {
            return this._labelFunction;
        }
        
        /**
         * @private
         */
        public function set labelFunction(value:Function):void
        {
            this._labelFunction = value;
        }
        
        /**
         * @private
         * Storage for the reverse property.
         */
        private var _reverse:Boolean = false;
        
        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#reverse
         */
        public function get reverse():Boolean
        {
            return this._reverse;
        }
        
        /**
         * @private
         */
        public function set reverse(value:Boolean):void
        {
            this._reverse = value;
        }
        
        /**
         * @private
         * Storage for the title property.
         */
        private var _title:String = "";
        
        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#title
         */
        public function get title():String
        {
            return this._title;
        }
        
        /**
         * @private
         */
        public function set title(value:String):void
        {
            this._title = value;
        }
        
        /**
         * @private
         * placeholder for maximum label width 
         */
        protected var _maxLabelWidth:Number;		
        
        /**
         * Gets or sets the maximum width of a label
         */
        public function get maxLabelWidth():Number
        {
            return _maxLabelWidth;
        }
        
        /**
         * @private (setter)
         */
        public function set maxLabelWidth(value:Number):void
        {
            _maxLabelWidth = value;
        }
        
        /**
         * @private
         * placeholder for maximum label width 
         */
        protected var _maxLabelHeight:Number;		
        
        /**
         * Gets or sets the maximum height of a label
         */
        public function get maxLabelHeight():Number
        {
            return _maxLabelHeight;
        }
        
        /**
         * @private (setter)
         */
        public function set maxLabelHeight(value:Number):void
        {
            _maxLabelHeight = value;
        }
        
        /**
         * @private
         */
        protected var _dataProvider:Array;
        
        /**
         * Data provider for the axis
         */
        public function get dataProvider():Array
        {
            return _dataProvider;
        }
        
        /**
         * @private (setter)
         */
        public function set dataProvider(value:Array):void
        {
            _dataProvider = value;
            this.parseDataProvider();
        }
        
        /**
         * @private
         */
        private var _labelSpacing:Number = 2; 
        
        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#labelSpacing
         */
        public function get labelSpacing():Number
        {
            return _labelSpacing;	
        }
        
        /**
         * @private (setter)
         */
        public function set labelSpacing(value:Number):void
        {
            if(value != _labelSpacing) _labelSpacing = value;
        }
        
        /**
         * @private
         * Placeholder for labelData object
         */
        private var _labelData:AxisLabelData;
        
        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#labelData
         */
        public function get labelData():AxisLabelData
        {
            return _labelData;
        }
        
        /** 
         * @private (setter)
         */
        public function set labelData(value:AxisLabelData):void
        {
            if(value != null && value !== this.labelData) _labelData = value;
        }
        
        /**
         * @private
         * Placeholder for position property
         */
        private var _position:String = "left";
        
        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#position
         */
        public function get position():String
        {
            return this._position;
        }
        
        /**
         * @private (setter)
         */
        public function set position(value:String):void
        {
            this._position = value;
        }
        
        /**
         * @private
         * Storage for maxLabel property.
         */
        private var _maxLabel:String = "";
        
        /**
         * Gets or sets the largest possible label.
         */
        public function get maxLabel():String
        {
            return _maxLabel;
        }
        
        /**
         * @private (setter)
         */
        public function set maxLabel(value:String):void
        {
            _maxLabel = value;
        }
        
    //--------------------------------------
    //  Public Methods
    //--------------------------------------

        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#valueToLabel()
         */
        public function valueToLabel(value:Object):String
        {
            if(value == null)
            {
                return "";
            }
            
            var text:String = value.toString();
            if(this._labelFunction != null)
            {
                try
                {
                    text = this._labelFunction(value);
                }
                catch(e:Error)
                {
                    //dispatch error event from the chart
                    var message:String = "There is an error in your ";
                    message += (ICartesianAxisRenderer(this.renderer).orientation == AxisOrientation.VERTICAL)?"y":"x";
                    message += "-axis labelFunction.";
                    this.chart.dispatchEvent(new ErrorEvent(ErrorEvent.ERROR, false, false, message));
                }
            }
            
            if(text == null)
            {
                text = "";
            }
            return text;
        }
        
    //--------------------------------------
    //  Protected Methods
    //--------------------------------------		
        /**
         * @private
         */
        protected function parseDataProvider():void
        {
            var labelData:Object = getLabelData();
            for(var i:String in labelData)
            {	
                this.labelData[i] = labelData[i];
            }
        }
        
        /**
         * @copy com.yahoo.astra.fl.charts.axes.IAxis#getMaxLabel
         */
        public function getMaxLabel():String
        {
            return "";
        }

        /**
         * @private (protected)
         */
        protected function getLabelData():Object
        {
            var labelData:Object = new Object();
            var label:BitmapText = new BitmapText();
            var renderer:UIComponent = UIComponent(this.renderer);			
            var showLabels:Boolean = renderer.getStyle("showLabels") as Boolean;
            label.embedFonts = renderer.getStyle("embedFonts") as Boolean;
            var rotation:Number = renderer.getStyle("labelRotation") as Number;
            var titleRotation:Number = renderer.getStyle("titleRotation") as Number;
            var textFormat:TextFormat = renderer.getStyle("textFormat") as TextFormat;
            rotation = Math.max(-90, Math.min(rotation, 90));			
            label.selectable = false;
            label.autoSize = TextFieldAutoSize.LEFT;			
            if(textFormat != null) label.defaultTextFormat = textFormat;
            label.text = this.getMaxLabel() as String;
            label.rotation = rotation;			
            var rad:Number;
            
            //vertical
            if(ICartesianAxisRenderer(this.renderer).orientation == AxisOrientation.VERTICAL)
            {
                var topTextOverflow:Number;
                var bottomTextOverflow:Number;

                if(rotation == 0 || Math.abs(rotation) == 90)
                {
                    topTextOverflow = label.height/2;
                    bottomTextOverflow = label.height/2;
                    this.maxLabelWidth = label.width;
                }
                else
                {
                    rad = Math.abs(rotation) * Math.PI/180;
                    if(rotation > 0 && this.position == "left" || rotation < 0 && this.position == "right")
                    {
                        topTextOverflow = label.height - .5 * Math.abs(label.contentHeight*Math.cos(rotation*Math.PI/180));
                        bottomTextOverflow = label.contentHeight/2  * Math.cos((Math.abs(rotation)) * Math.PI/180);
                    }
                    else
                    {
                        topTextOverflow = label.contentHeight/2  * Math.cos((Math.abs(rotation)) * Math.PI/180);
                        bottomTextOverflow = label.height - .5 * Math.abs(label.contentHeight*Math.cos(rotation*Math.PI/180));
                    }		
                    this.maxLabelWidth = label.width - ((label.contentHeight * (1 - Math.abs(rotation)/90)) * Math.sin(Math.abs(rad)));		
                }
                 
                this.labelData.maxLabelHeight = Math.max(label.rotationHeight, this.labelData.maxLabelHeight);
                
                if(showLabels)
                {
                    this.width = this.maxLabelWidth;
                    this.width += renderer.getStyle("labelDistance") as Number;				
                    labelData.topLabelOffset = topTextOverflow;
                    labelData.bottomLabelOffset = bottomTextOverflow;
                }
                else
                {
                    this.width = 0;
                    labelData.topLabelOffset = 0;
                    labelData.bottomLabelOffset = 0;
                }
                
                labelData.leftLabelOffset = 0;
                labelData.rightLabelOffset = 0;	
                this.width += (this.chart as CartesianChart).getAxisTickOffset(this.renderer as ICartesianAxisRenderer);
                
                if(this.title != null && this.title != "") 
                {
                    this.width += AxisLabelUtil.getTextWidth(this.title, textFormat, titleRotation);
                    this.width += renderer.getStyle("titleDistance") as Number;
                }
            }
            else //horizontal
            {
                var leftTextOverflow:Number;
                var rightTextOverflow:Number;

                if(rotation == 0 || Math.abs(rotation) == 90)
                {
                    leftTextOverflow = label.width/2;
                    rightTextOverflow = label.width/2;
                    this.maxLabelHeight = label.height;
                }
                else
                {
                    rad = rotation * Math.PI/180;
                    if(rotation > 0 && this.position == "bottom" || rotation < 0 && this.position == "top")
                    {
                        leftTextOverflow = label.contentHeight * Math.sin(rotation * Math.PI/180)/2;
                        rightTextOverflow = (Math.cos(Math.abs(rad)) * label.contentWidth) + (label.contentHeight * Math.sin(Math.abs(rad)))/2;
                    }
                    else
                    {
                        leftTextOverflow = Math.cos(Math.abs(rad)) * label.contentWidth;
                        rightTextOverflow = label.contentHeight * Math.abs(Math.sin(rotation * Math.PI/180)/2);	
                    }
                    this.maxLabelHeight = label.height - (Math.cos(Math.abs(rad)) * (label.contentHeight * rotation/90));			
                }

                this.labelData.maxLabelWidth = Math.max(label.rotationWidth, this.labelData.maxLabelWidth); 
                
                if(showLabels)
                {
                    this.height = this.maxLabelHeight;	
                    this.height += renderer.getStyle("labelDistance") as Number;
                    labelData.leftLabelOffset = leftTextOverflow;
                    labelData.rightLabelOffset = rightTextOverflow;
                }
                else
                {
                    this.height = 0;	
                    labelData.leftLabelOffset = 0;
                    labelData.rightLabelOffset = 0;
                }
                labelData.topLabelOffset = 0;
                labelData.bottomLabelOffset = 0;	
                if(this.title != null && this.title != "") 
                {
                    this.height += AxisLabelUtil.getTextHeight(this.title, textFormat, titleRotation);
                    this.height += renderer.getStyle("titleDistance") as Number;	
                }
                this.height += (this.chart as CartesianChart).getAxisTickOffset(this.renderer as ICartesianAxisRenderer);
            }

            return labelData;			
        }		
    }
}
