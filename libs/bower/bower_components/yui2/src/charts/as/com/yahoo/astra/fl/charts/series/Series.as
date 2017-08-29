package com.yahoo.astra.fl.charts.series
{
    import com.yahoo.astra.fl.charts.events.ChartEvent;

    import fl.core.InvalidationType;
    import fl.core.UIComponent;
    import fl.transitions.easing.Strong;

    import flash.display.DisplayObject;
    import flash.display.DisplayObjectContainer;
    import flash.display.InteractiveObject;
    import flash.display.Shape;
    import flash.events.Event;
    import flash.events.MouseEvent;
    import flash.utils.Dictionary;
    import flash.utils.getQualifiedClassName;
        
    //--------------------------------------
    //  Styles
    //--------------------------------------

    /**
     * The easing function for animations that occur on data changes.
     */
    [Style(name="animationEasingFunction", type="Function")]

    /**
     * The duration for animations that occur on data changes.
     */
    [Style(name="animationDuration", type="int")]

    /**
     * If true, data changes will be displayed with animations. If false, changes will happen instantly.
     */
    [Style(name="animationEnabled", type="Boolean")]

    /**
     * The base color used by objects displayed in this series.
     */
    [Style(name="color", type="uint")]

    /** 
     * The border color used by programatic skins in this series.
     */
    [Style(name="borderColor", type="uint")]

    /**
     * The fill color used by programatic skins in this series.
     */
    [Style(name="fillColor", type="uint")]

    /**
     * The Class used to instantiate each marker's skin.
     */
    [Style(name="markerSkin", type="Class")]

    /**
     * The size, in pixels, of each marker.
     */
    [Style(name="markerSize", type="Number")]

    /**
     * The alpha value from 0.0 to 1.0 to use for drawing the markers.
     */
    [Style(name="markerAlpha", type="Number")]

    /**
     * The alpha value from 0.0 to 1.0 to use for drawing the fills of markers.
     */
    [Style(name="fillAlpha", type="Number")]

    /**
     * The alpha value from 0.0 to 1.0 to use for drawing the border of markers.
     */
    [Style(name="borderAlpha", type="Number")]

    /**
     * Functionality common to most series. Generally, a <code>Series</code> object
     * shouldn't be instantiated directly. Instead, a subclass with a concrete
     * implementation should be used.
     * 
     * @author Josh Tynjala
     */
    public class Series extends UIComponent implements ISeries
    {
        
    //--------------------------------------
    //  Class Variables
    //--------------------------------------
        
        /**
         * @private
         */
        private static var defaultStyles:Object = 
        {	
            markerSkin: Shape, //an empty display object
            fillColor: null,
            markerSize: 10,
            markerAlpha: 1.0,
            fillAlpha: 1.0,
            borderAlpha: 1.0,
            animationEnabled: true,
            animationEasingFunction: fl.transitions.easing.Strong.easeOut,
            animationDuration: 500,
            borderColor: null,
            color: 0x00b8bf
        };
        
        /**
         * @private
         */
        private static const RENDERER_STYLES:Object = 
        {
            skin: "markerSkin",
            fillColor: "fillColor",
            borderColor: "borderColor",
            color: "color",
            fillAlpha: "fillAlpha",
            borderAlpha: "borderAlpha"
        };
        
    //--------------------------------------
    //  Class Methods
    //--------------------------------------

        /**
         * @private
         * @copy fl.core.UIComponent#getStyleDefinition()
         */
        public static function getStyleDefinition():Object
        {
            return mergeStyles(defaultStyles, UIComponent.getStyleDefinition());
        }
        
    //--------------------------------------
    //  Constructor
    //--------------------------------------

        /**
         * Constructor.
         */
        public function Series(dataProvider:Object = null)
        {
            super();
            this._dataProvider = dataProvider;
        }
        
    //--------------------------------------
    //  Properties
    //--------------------------------------
        
        /**
         * @private
         */
        protected var markers:Array = [];
        
        /**
         * @private
         * A set of flags to indicate if special considerations need to be taken for the markers.
         */
        protected var markerInvalidHash:Dictionary = new Dictionary(true);
        
        /**
         * @private
         * Storage for the chart property.
         */
        private var _chart:Object;
        
        /**
         * @copy com.yahoo.astra.fl.charts.series.ISeries#chart
         */
        public function get chart():Object
        {
            return this._chart;
        }
        
        /**
         * @private
         */
        public function set chart(value:Object):void
        {
            this._chart = value;
            //this is a fun hack to ensure that series know if their parent charts are in live preview
            if(this._chart == null || this._chart.parent == null)
            {
                this.isLivePreview = false;
            }
            var className:String;
            try
            {
                className = getQualifiedClassName(this._chart.parent);	
            }
            catch (e:Error) {}
            this.isLivePreview = (className == "fl.livepreview::LivePreviewParent");	
        }
        
        /**
         * @private
         * A lookup system to convert from an item to its item renderer.
         */
        private var _itemToItemRendererHash:Dictionary = new Dictionary();
        
        /**
         * @private
         * Storage for the itemRenderer property.
         */
        private var _itemRenderer:Object = SeriesItemRenderer;
        
        /**
         * The class used to instantiate item renderers.
         */
        public function get itemRenderer():Object
        {
            return this._itemRenderer;
        }
        
        /**
         * @private
         */
        public function set itemRenderer(value:Object):void
        {
            if(this._itemRenderer != value)
            {
                this._itemRenderer = value;
                this.invalidate("itemRenderer");
            }
        }
        
        /**
         * @private
         * Storage for the data property.
         */
        private var _dataProvider:Object;
        
        /**
         * @copy com.yahoo.astra.fl.charts.series.ISeries#dataProvider
         */
        public function get dataProvider():Object
        {
            return this._dataProvider;
        }
        
        /**
         * @private
         */
        public function set dataProvider(value:Object):void
        {
            if(this._dataProvider != value)
            {
                //if we get XML data and it isn't an XMLList,
                //ignore the root tag
                if(value is XML && !(value is XMLList))
                {
                    value = value.elements();
                }
                
                if(value is XMLList)
                {
                    value = XMLList(value).copy();
                }
                else if(value is Array)
                {
                    value = (value as Array).concat();
                }
                
                this._dataProvider = value;
                this.dispatchEvent(new Event("dataChange"));
                this.invalidate(InvalidationType.DATA);
            }
        }
        
        /**
         * @private
         * Storage for the displayName property.
         */
        private var _displayName:String;
        
        /**
         * @copy com.yahoo.astra.fl.charts.series.ISeries#displayName
         */
        public function get displayName():String
        {
            return this._displayName;
        }
        
        /**
         * @private
         */
        public function set displayName(value:String):void
        {
            this._displayName = value;
        }
        
        /**
         * @copy com.yahoo.astra.fl.charts.series.ISeries#length
         */
        public function get length():int
        {
            if(this._dataProvider is Array)
            {
                return (this._dataProvider as Array).length;
            }
            else if(this._dataProvider is XMLList)
            {
                return (this._dataProvider as XMLList).length();
            }
            
            return 0;
        }
        
        /**
         * @private
         * Storage for dataTipFunction
         */
        private var _dataTipFunction:Function;
        
        /**
         * @copy com.yahoo.astra.fl.charts.series.ISeries#dataTipFunction
         */
        public function get dataTipFunction():Function
        {
            return this._dataTipFunction;
        }
        
        /**
         * @private (setter)
         */
        public function set dataTipFunction(value:Function):void
        {
            this._dataTipFunction = value;
        }
        
        /**
         * @private 
         * Storage for legendLabelFunction
         */
        private var _legendLabelFunction:Function = null;
        
        /** 
         * @copy com.yahoo.astra.fl.charts.series.ISeries#legendLabelFunction
         */
        public function get legendLabelFunction():Function
        {
            return this._legendLabelFunction;
        }
        
        /**
         * @private
         */
        public function set legendLabelFunction(value:Function):void
        {
            this._legendLabelFunction = value;
        }	
        
        
    //--------------------------------------
    //  Public Methods
    //--------------------------------------
        
        /**
         * @copy com.yahoo.astra.fl.charts.series.ISeries#clone()
         */
        public function clone():ISeries
        {
            var series:Series = new Series();
            series.dataProvider = this.dataProvider;
            series.displayName = this.displayName;
            return series;
        }
        
        /**
         * @copy com.yahoo.astra.fl.charts.series.ISeries#itemRendererToIndex()
         */
        public function itemRendererToIndex(renderer:ISeriesItemRenderer):int
        {
            return this.markers.indexOf(renderer);
        }
        
        /**
         * @copy com.yahoo.astra.fl.charts.series.ISeries#itemToItemRenderer()
         */		
        public function itemToItemRenderer(item:Object):ISeriesItemRenderer
        {
            return this._itemToItemRendererHash[item] as ISeriesItemRenderer;
        }
        
    //--------------------------------------
    //  Protected Methods
    //--------------------------------------
        
        /**
         * @private
         */
        override protected function draw():void
        {
            //the class for the item renderers has changed. remove all markers
            //so that they may be recreated.
            if(this.isInvalid("itemRenderer"))
            {
                this.removeAllMarkers();
            }
            
            if(this.isInvalid("itemRenderer", InvalidationType.DATA, InvalidationType.STYLES))
            {
                this.refreshMarkers();
                this._itemToItemRendererHash = new Dictionary(true);
                var itemCount:int = this.markers.length;
                for(var i:int = 0; i < itemCount; i++)
                {
                    var marker:ISeriesItemRenderer = this.markers[i] as ISeriesItemRenderer;
                    if(this.isInvalid(InvalidationType.DATA)) //update data if needed
                    {
                        marker.data = this.dataProvider[i];
                    }
                    this._itemToItemRendererHash[marker.data] = marker;
                    
                    var markerComponent:UIComponent = marker as UIComponent;
                    this.copyStylesToChild(markerComponent, RENDERER_STYLES);
                    markerComponent.drawNow();
                }
            }
            
            super.draw();
        }
        
        /**
         * @private
         * All markers are removed from the display list.
         */
        protected function removeAllMarkers():void
        {
            var markerCount:int = this.markers.length;
            for(var i:int = 0; i < markerCount; i++)
            {
                var marker:ISeriesItemRenderer = this.markers.pop() as ISeriesItemRenderer;
                marker.removeEventListener(MouseEvent.ROLL_OVER, markerRollOverHandler);
                marker.removeEventListener(MouseEvent.ROLL_OUT, markerRollOutHandler);
                marker.removeEventListener(MouseEvent.CLICK, markerClickHandler);
                marker.removeEventListener(MouseEvent.DOUBLE_CLICK, markerDoubleClickHandler);
                this.removeChild(DisplayObject(marker));
            }
        }
        
        /**
         * @private
         * Add or remove markers as needed. current markers will be reused.
         */
        protected function refreshMarkers():void
        {
            var itemCount:int = this.length;
            var difference:int = itemCount - this.markers.length;
            if(difference > 0)
            {
                for(var i:int = 0; i < difference; i++)
                {
                    var marker:ISeriesItemRenderer = new this.itemRenderer();
                    marker.series = this;
                    DisplayObjectContainer(marker).mouseChildren = false;
                    InteractiveObject(marker).doubleClickEnabled = true;
                    marker.addEventListener(MouseEvent.ROLL_OVER, markerRollOverHandler, false, 0, true);
                    marker.addEventListener(MouseEvent.ROLL_OUT, markerRollOutHandler, false, 0, true);
                    marker.addEventListener(MouseEvent.CLICK, markerClickHandler, false, 0, true);
                    marker.addEventListener(MouseEvent.DOUBLE_CLICK, markerDoubleClickHandler, false, 0, true);
                    this.addChild(DisplayObject(marker));
                    this.markers.push(marker);
                    this.invalidateMarker(marker);
                }
            }
            else if(difference < 0)
            {
                difference = Math.abs(difference);
                for(i = 0; i < difference; i++)
                {
                    marker = this.markers.pop() as ISeriesItemRenderer;
                    this.validateMarker(marker);
                    marker.removeEventListener(MouseEvent.ROLL_OVER, markerRollOverHandler);
                    marker.removeEventListener(MouseEvent.ROLL_OUT, markerRollOutHandler);
                    marker.removeEventListener(MouseEvent.CLICK, markerClickHandler);
                    marker.removeEventListener(MouseEvent.DOUBLE_CLICK, markerDoubleClickHandler);
                    this.removeChild(DisplayObject(marker));
                }
            }
            
            var markerCount:int = this.markers.length;
            for(i = 0; i < markerCount; i++)
            {
                marker = ISeriesItemRenderer(this.markers[i]);
                marker.data = this.dataProvider[i];
                DisplayObject(marker).alpha = this.getStyleValue("markerAlpha") as Number;
                this.copyStylesToChild(UIComponent(marker), RENDERER_STYLES);
            }
        }
        
        /**
         * Indicates whether special considerations should be taken for a newly created marker.
         */
        protected function isMarkerInvalid(marker:ISeriesItemRenderer):Boolean
        {
            return this.markerInvalidHash[marker];
        }
        
        /**
         * Invalidates a marker (considered new).
         */
        protected function invalidateMarker(marker:ISeriesItemRenderer):void
        {
            markerInvalidHash[marker] = true;
            DisplayObject(marker).visible = false;
        }
        
        /**
         * @private
         * We never want the series to callLater after invalidating.
         * The chart will ALWAYS handle drawing.
         */
        override public function invalidate(property:String = InvalidationType.ALL, callLater:Boolean = true):void
        {
            //never call later!
            super.invalidate(property, false);
        }
        
        /**
         * Makes a marker valid. To be used by subclasses.
         */
        protected function validateMarker(marker:ISeriesItemRenderer):void
        {
            DisplayObject(marker).visible = true;
            delete markerInvalidHash[marker];
        }
        
        /**
         * @private
         * Notify the parent chart that the user's mouse is over this marker.
         */
        protected function markerRollOverHandler(event:MouseEvent):void
        {
            var itemRenderer:ISeriesItemRenderer = ISeriesItemRenderer(event.currentTarget);
            var index:int = this.itemRendererToIndex(itemRenderer);
            var item:Object = this.dataProvider[index];
            var rollOver:ChartEvent = new ChartEvent(ChartEvent.ITEM_ROLL_OVER, index, item, itemRenderer, this);
            this.dispatchEvent(rollOver);
        }
        
        /**
         * @private
         * Notify the parent chart that the user's mouse has left this marker.
         */
        protected function markerRollOutHandler(event:MouseEvent):void
        {			
            var itemRenderer:ISeriesItemRenderer = ISeriesItemRenderer(event.currentTarget);
            var index:int = this.itemRendererToIndex(itemRenderer);
            var item:Object = this.dataProvider[index];
            var rollOut:ChartEvent = new ChartEvent(ChartEvent.ITEM_ROLL_OUT, index, item, itemRenderer, this);
            this.dispatchEvent(rollOut);
        }
        
        /**
         * @private
         * Notify the parent chart that the user clicked this marker.
         */
        protected function markerClickHandler(event:MouseEvent):void
        {
            var itemRenderer:ISeriesItemRenderer = ISeriesItemRenderer(event.currentTarget);
            var index:int = this.itemRendererToIndex(itemRenderer);
            var item:Object = this.dataProvider[index];
            var click:ChartEvent = new ChartEvent(ChartEvent.ITEM_CLICK, index, item, itemRenderer, this);
            this.dispatchEvent(click);
        }
        
        /**
         * @private
         * Notify the parent chart that the user double-clicked this marker.
         */
        protected function markerDoubleClickHandler(event:MouseEvent):void
        {
            var itemRenderer:ISeriesItemRenderer = ISeriesItemRenderer(event.currentTarget);
            var index:int = this.itemRendererToIndex(itemRenderer);
            var item:Object = this.dataProvider[index];
            var doubleClick:ChartEvent = new ChartEvent(ChartEvent.ITEM_DOUBLE_CLICK, index, item, itemRenderer, this);
            this.dispatchEvent(doubleClick);
        }
    }
}
