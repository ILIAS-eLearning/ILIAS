package com.yahoo.astra.fl.charts
{
    import com.yahoo.astra.fl.charts.events.ChartEvent;
    import com.yahoo.astra.fl.charts.legend.ILegend;
    import com.yahoo.astra.fl.charts.legend.LegendItemData;
    import com.yahoo.astra.fl.charts.series.ICategorySeries;
    import com.yahoo.astra.fl.charts.series.ILegendItemSeries;
    import com.yahoo.astra.fl.charts.series.ISeries;
    import com.yahoo.astra.fl.charts.series.ISeriesItemRenderer;
    import com.yahoo.astra.fl.utils.UIComponentUtil;

    import fl.core.InvalidationType;
    import fl.core.UIComponent;

    import flash.accessibility.AccessibilityProperties;
    import flash.display.DisplayObject;
    import flash.display.Sprite;
    import flash.events.Event;
    import flash.events.MouseEvent;
    import flash.geom.Point;
    import flash.text.TextFormat;
    import flash.text.TextFormatAlign;
    import flash.utils.getDefinitionByName;
    import flash.events.ErrorEvent;
        
    //--------------------------------------
    //  Styles
    //--------------------------------------

    /**
     * The padding that separates the border of the component from its contents,
     * in pixels.
     *
     * @default 10
     */
    [Style(name="contentPadding", type="Number")]

    /**
     * Name of the class to use as the skin for the background and border of the
     * component.
     *
     * @default ChartBackgroundSkin
     */
    [Style(name="backgroundSkin", type="Class")]

    /**
     * The default colors for each series. These colors are used for markers,
     * in most cases, but they may apply to lines, fills, or other graphical
     * items.
     * 
     * <p>An Array of values that correspond to series indices in the data
     * provider. If the number of values in the Array is less than the number
     * of series, then the next series will restart at index zero in the style
     * Array. If the value of this style is an empty Array, then each individual series
     * will use the default or modified value set on the series itself.</p> 
     *
     * <p>Example: If the seriesColors style is equal to [0xffffff, 0x000000] and there
     * are three series in the chart's data provider, then the series at index 0
     * will have a color of 0xffffff, index 1 will have a color of 0x000000, and
     * index 2 will have a color of 0xffffff (starting over from the beginning).</p>
     * 
     * @default [0x00b8bf, 0x8dd5e7, 0xedff9f, 0xffa928, 0xc0fff6, 0xd00050, 0xc6c6c6, 0xc3eafb, 0xfcffad, 0xcfff83, 0x444444, 0x4d95dd, 0xb8ebff, 0x60558f, 0x737d7e, 0xa64d9a, 0x8e9a9b, 0x803e77]
     */
    [Style(name="seriesColors", type="Array")]

    /**
     * The default size of the markers in pixels. The actual drawn size of the
     * markers could end up being different in some cases. For example, bar charts
     * and column charts display markers side-by-side, and a chart may need to make
     * the bars or columns smaller to fit within the required region.
     *
     * <p>An Array of values that correspond to series indices in the data
     * provider. If the number of values in the Array is less than the number
     * of series, then the next series will restart at index zero in the style
     * Array. If the value of this style is an empty Array, then each individual series
     * will use the default or modified value set on the series itself.</p>
     * 
     * <p>Example: If the seriesMarkerSizes style is equal to [10, 15] and there
     * are three series in the chart's data provider, then the series at index 0
     * will have a marker size of 10, index 1 will have a marker size of 15, and
     * index 2 will have a marker size of 10 (starting over from the beginning).</p>
     * 
     * @default []
     */
    [Style(name="seriesMarkerSizes", type="Array")]

    /**
     * An Array containing the default skin classes for each series. These classes
     * are used to instantiate the marker skins. The values may be fully-qualified
     * package and class strings or a reference to the classes themselves.
     *
     * <p>An Array of values that correspond to series indices in the data
     * provider. If the number of values in the Array is less than the number
     * of series, then the next series will restart at index zero in the style
     * Array. If the value of this style is an empty Array, then each individual series
     * will use the default or modified value set on the series itself.</p> 
     * 
     * <p>Example: If the seriesMarkerSkins style is equal to [CircleSkin, DiamondSkin] and there
     * are three series in the chart's data provider, then the series at index 0
     * will have a marker skin of CircleSkin, index 1 will have a marker skin of DiamondSkin, and
     * index 2 will have a marker skin of CircleSkin (starting over from the beginning).</p>
     * 
     * @default []
     */
    [Style(name="seriesMarkerSkins", type="Array")]

    /**
     * The TextFormat object to use to render data tips.
     *
     * @default TextFormat("_sans", 11, 0x000000, false, false, false, '', '', TextFormatAlign.LEFT, 0, 0, 0, 0)
     */
    [Style(name="dataTipTextFormat", type="TextFormat")]

    /**
     * Name of the class to use as the skin for the background and border of the
     * chart's data tip.
     *
     * @default ChartDataTipBackground
     */
    [Style(name="dataTipBackgroundSkin", type="Class")]

    /**
     * If the datatip's content padding is customizable, it will use this value.
     * The padding that separates the border of the component from its contents,
     * in pixels.
     *
     * @default 6
     */
    [Style(name="dataTipContentPadding", type="Number")]

    /**
     * Determines if data changes should be displayed with animation.
     *
     * @default true
     */
    [Style(name="animationEnabled", type="Boolean")]

    /**
     * Indicates whether embedded font outlines are used to render the text
     * field. If this value is true, Flash Player renders the text field by
     * using embedded font outlines. If this value is false, Flash Player
     * renders the text field by using device fonts.
     * 
     * If you set the embedFonts property to true for a text field, you must
     * specify a font for that text by using the font property of a TextFormat
     * object that is applied to the text field. If the specified font is not
     * embedded in the SWF file, the text is not displayed.
     * 
     * @default false
     */
    [Style(name="embedFonts", type="Boolean")]

    /**
     * Functionality common to most charts. Generally, a <code>Chart</code> object
     * shouldn't be instantiated directly. Instead, a subclass with a concrete
     * implementation should be used. That subclass generally should implement the
     * <code>IPlotArea</code> interface.
     * 
     * @author Josh Tynjala
     */
    public class Chart extends UIComponent
    {
        
    //--------------------------------------
    //  Class Variables
    //--------------------------------------

        /**
         * @private
         */
        private static var defaultStyles:Object = 
        {
            seriesMarkerSizes: null,
            seriesMarkerSkins: null,
            seriesColors:
            [
                0x00b8bf, 0x8dd5e7, 0xedff9f, 0xffa928, 0xc0fff6, 0xd00050,
                0xc6c6c6, 0xc3eafb, 0xfcffad, 0xcfff83, 0x444444, 0x4d95dd,
                0xb8ebff, 0x60558f, 0x737d7e, 0xa64d9a, 0x8e9a9b, 0x803e77
            ],
            seriesBorderColors:[],
            seriesFillColors:[],
            seriesLineColors:[],
            seriesBorderAlphas:[1],
            seriesFillAlphas:[1],
            seriesLineAlphas:[1],
            contentPadding: 10,
            backgroundSkin: "ChartBackground",
            backgroundColor: 0xffffff,
            dataTipBackgroundSkin: "ChartDataTipBackground",
            dataTipContentPadding: 6,
            dataTipTextFormat: new TextFormat("_sans", 11, 0x000000, false, false, false, '', '', TextFormatAlign.LEFT, 0, 0, 0, 0),
            animationEnabled: true,
            embedFonts: false
        };
        
        /**
         * @private
         */
        private static const ALL_SERIES_STYLES:Object = 
        {
            color: "seriesColors",
            markerSize: "seriesMarkerSizes",
            markerSkin: "seriesMarkerSkins",
            borderColor: "seriesBorderColors",
            fillColor: "seriesFillColors",
            lineColor: "seriesLineColors",
            borderAlpha: "seriesBorderAlphas",
            fillAlpha: "seriesFillAlphas",
            lineAlpha: "seriesLineAlphas"
        };
        
        /**
         * @private
         */
        private static const SHARED_SERIES_STYLES:Object = 
        {
            animationEnabled: "animationEnabled"
        };
        
        private static const DATA_TIP_STYLES:Object = 
        {
            backgroundSkin: "dataTipBackgroundSkin",
            contentPadding: "dataTipContentPadding",
            textFormat: "dataTipTextFormat",
            embedFonts: "embedFonts"
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
        public function Chart()
        {
            super();
            this.accessibilityProperties = new AccessibilityProperties();
            this.accessibilityProperties.forceSimple = true;
            this.accessibilityProperties.description = "Chart";
        }
        
    //--------------------------------------
    //  Variables and Properties
    //--------------------------------------
        
        /**
         * @private
         * The display object representing the chart background.
         */
        protected var background:DisplayObject;
        
        /**
         * @private
         * The area where series are drawn.
         */
        protected var content:Sprite;
        
        /**
         * @private
         * The mouse over data tip that displays information about an item on the chart.
         */
        protected var dataTip:DisplayObject;
        
        /**
         * @private
         * Storage for the data property. Saves a copy of the unmodified data.
         */
        private var _dataProvider:Object;
        
        /**
         * @private
         * Modified version of the stored data.
         */
        protected var series:Array = [];
        
        [Inspectable(type=Array)]
        /**
         * @copy com.yahoo.astra.fl.charts.IChart#dataProvider
         */
        public function get dataProvider():Object
        {
            return this.series;
        }
        
        /**
         * @private
         */
        public function set dataProvider(value:Object):void
        {
            if(this._dataProvider != value)
            {
                this._dataProvider = value;
                this.invalidate(InvalidationType.DATA);
            }
        }
        
        /**
         * @private
         * Storage for the defaultSeriesType property.
         */
        private var _defaultSeriesType:Class;
        
        /**
         * When raw data (like an Array of Numbers) is encountered where an
         * ISeries instance is expected, it will be converted to this default
         * type. Accepts either a Class instance or a String referencing a
         * fully-qualified class name.
         */
        public function get defaultSeriesType():Object
        {
            return this._defaultSeriesType;
        }
        
        /**
         * @private
         */
        public function set defaultSeriesType(value:Object):void
        {
            if(!value) return;
            var classDefinition:Class = null;
            if(value is Class)
            {
                classDefinition = value as Class;
            }
            else
            {
                // borrowed from fl.core.UIComponent#getDisplayObjectInstance()
                try
                {
                    classDefinition = getDefinitionByName(value.toString()) as Class;
                }
                catch(e:Error)
                {
                    try
                    {
                        classDefinition = this.loaderInfo.applicationDomain.getDefinition(value.toString()) as Class;
                    }
                    catch (e:Error)
                    {
                        // Nothing
                    }
                }
            }
            
            this._defaultSeriesType = classDefinition;
            //no need to redraw.
            //if the series have already been created, the user probably wanted it that way.
            //we have no way to tell if the user chose a particular series' type or not anyway.
        }
        
        private var _lastDataTipRenderer:ISeriesItemRenderer;
        
        /**
         * @private
         * Storage for the dataTipFunction property.
         */
        private var _dataTipFunction:Function = defaultDataTipFunction;
        
        /**
         * If defined, the chart will call the input function to determine the
         * text displayed in the chart's data tip. The function uses the following
         * signature:
         * 
         * <p><code>function dataTipFunction(item:Object, index:int, series:ISeries):String</code></p>
         */
        public function get dataTipFunction():Function
        {
            return this._dataTipFunction;
        }
        
        /**
         * @private
         */
        public function set dataTipFunction(value:Function):void
        {
            this._dataTipFunction = value;
        }

        /**
         * @private
         * Storage for the legend property.
         */
        private var _legend:ILegend;
                
        /**
         * The component that will display a human-readable legend for the chart. 
         */
        public function get legend():ILegend
        {
            return this._legend;
        }
        
        /**
         * @private
         */
        public function set legend(value:ILegend):void
        {
            this._legend = value;
            this.invalidate();
        }
        
        /**
         * @private 
         * Storage for legendLabelFunction
         */
        private var _legendLabelFunction:Function;
        
        /**
         * If defined, the chart will call the input function to determine the text displayed in 
         * in the chart's legend.
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
         * Returns the index within this plot area of the input ISeries object.
         * 
         * @param series	a series that is displayed in this plot area.
         * @return			the index of the input series
         */
        public function seriesToIndex(series:ISeries):int
        {
            return this.series.indexOf(series);
        }
        
        /**
         * Returns the ISeries object at the specified index.
         * 
         * @param index		the index of the series to return
         * @return			the series that appears at the input index or null if out of bounds 
         */
        public function indexToSeries(index:int):ISeries
        {
            if(index < 0 || index >= this.series.length) return null;
            return this.series[index];
        }
        
    //--------------------------------------
    //  Protected Methods
    //--------------------------------------

        /**
         * @private
         */
        override protected function configUI():void
        {
            super.width = 400;
            super.height = 300;
            
            super.configUI();
            
            this.content = new Sprite();
            this.addChild(this.content);
            
            this.dataTip = new DataTipRenderer();
            this.dataTip.visible = false;
            this.addChild(this.dataTip);
        }

        /**
         * @private
         */
        override protected function draw():void
        {
            var dataInvalid:Boolean = this.isInvalid(InvalidationType.DATA);
            var stylesInvalid:Boolean = this.isInvalid(InvalidationType.STYLES);
            var sizeInvalid:Boolean = this.isInvalid(InvalidationType.SIZE);
            
            if(stylesInvalid || dataInvalid)
            {
                this.refreshSeries();
            }
            
            //update the background if needed
            if(stylesInvalid)
            {
                if(this.background)
                {
                    this.removeChild(this.background);
                }
                var skinClass:Object = this.getStyleValue("backgroundSkin");
                this.background = UIComponentUtil.getDisplayObjectInstance(this, skinClass);
                this.addChildAt(this.background, 0);
            }
            
            if(this.background && (stylesInvalid || sizeInvalid))
            {
                this.background.width = this.width;
                this.background.height = this.height;
            
                //force the background to redraw if it is a UIComponent
                if(this.background is UIComponent)
                {
                    (this.background as UIComponent).drawNow();
                }
            }
            
            if(this.dataTip is UIComponent)
            {
                var dataTip:UIComponent = UIComponent(this.dataTip);
                this.copyStylesToChild(dataTip, DATA_TIP_STYLES);
                dataTip.drawNow();
            }
            
            super.draw();
        }

        /**
         * Analyzes the input data and smartly converts it to the correct ISeries type
         * required for drawing. Adds new ISeries objects to the display list and removes
         * unused series objects that no longer need to be drawn.
         */
        protected function refreshSeries():void
        {
            var modifiedData:Object = this._dataProvider;
            
            //loop through each series and convert it to the correct data type
            if(modifiedData is Array)
            {
                var arrayData:Array = (modifiedData as Array).concat();
                var seriesCount:int = arrayData.length;
                var foundIncompatibleData:Boolean = false;
                for(var i:int = 0; i < seriesCount; i++)
                {
                    var currentItem:Object = arrayData[i];
                    if(currentItem is Array || currentItem is XMLList)
                    {
                        var itemSeries:ISeries = new this.defaultSeriesType();
                        if(currentItem is Array)
                        {
                            itemSeries.dataProvider = (currentItem as Array).concat();
                        }
                        else if(currentItem is XMLList)
                        {
                            itemSeries.dataProvider = (currentItem as XMLList).copy();
                        }
                        arrayData[i] = itemSeries;
                    }
                    else if(!(currentItem is ISeries))
                    {
                        //we only support Array, XMLList, and ISeries
                        //anything else means that we should restore the original data
                        var originalData:Array = (modifiedData as Array).concat();
                        modifiedData = new this.defaultSeriesType(originalData);
                        foundIncompatibleData = true;
                        break;
                    }
                }
                if(!foundIncompatibleData)
                {
                    modifiedData = arrayData;
                }
            }
            
            //attempt to turn a string into XML
            if(modifiedData is String)
            {
                try
                {
                    modifiedData = new XML(modifiedData);
                }
                catch(error:Error)
                {
                    //this isn't a valid xml string, so ignore it
                    return;
                }
            }
            
            //we need an XMLList, so get the elements
            if(modifiedData is XML)
            {
                modifiedData = (modifiedData as XML).elements();
            }
            
            //convert the XMLList to a series
            if(modifiedData is XMLList)
            {
                modifiedData = new this.defaultSeriesType(modifiedData);
            }
        
            //we should have an ISeries object by now, so put it in an Array
            if(modifiedData is ISeries)
            {
                //if the main data is a series, put it in an array
                modifiedData = [modifiedData];
            }
            
            //if it's not an array, we have bad data, so ignore it
            if(!(modifiedData is Array))
            {
                return;
            }
            
            arrayData = modifiedData as Array;
            
            seriesCount = this.series.length;
            for(i = 0; i < seriesCount; i++)
            {
                var currentSeries:ISeries = this.series[i] as ISeries;
                if(arrayData.indexOf(currentSeries) < 0)
                {
                    //if the series no longer exists, remove it from the display list and stop listening to it
                    this.content.removeChild(DisplayObject(currentSeries));
                    currentSeries.removeEventListener("dataChange", seriesDataChangeHandler);
                    currentSeries.removeEventListener(ChartEvent.ITEM_ROLL_OVER, chartItemRollOver);
                    currentSeries.removeEventListener(ChartEvent.ITEM_ROLL_OUT, chartItemRollOut);
                    currentSeries.chart = null;
                }
            }
            
            //rebuild the series Array
            this.series = [];
            seriesCount = arrayData.length;
            for(i = 0; i < seriesCount; i++)
            {
                currentSeries = arrayData[i] as ISeries;
                this.series.push(currentSeries);
                if(!this.contains(DisplayObject(currentSeries)))
                {
                    //if this is a new series, add it to the display list and listen for events
                    currentSeries.addEventListener("dataChange", seriesDataChangeHandler, false, 0, true);
                    currentSeries.addEventListener(ChartEvent.ITEM_ROLL_OVER, chartItemRollOver, false, 0, true);
                    currentSeries.addEventListener(ChartEvent.ITEM_ROLL_OUT, chartItemRollOut, false, 0, true);
                    currentSeries.chart = this;
                    this.content.addChild(DisplayObject(currentSeries));
                }
                
                DisplayObject(currentSeries).x = 0;
                DisplayObject(currentSeries).y = 0;
                
                //make sure the series are displayed in the correct order
                this.content.setChildIndex(DisplayObject(currentSeries), this.content.numChildren - 1);
                
                //update the series styles
                this.copyStylesToSeries(currentSeries, ALL_SERIES_STYLES);
                if(currentSeries is UIComponent)
                {
                    this.copyStylesToChild(UIComponent(currentSeries), SHARED_SERIES_STYLES);
                }
            }
        }
        
        /**
         * @private
         * Refreshes the legend's data provider.
         */
        protected function updateLegend():void
        {
            if(!this.legend) return;
            
            var legendData:Array = [],
                series:ISeries,
                seriesCount:int = this.series.length,
                i:int = 0,
                n:int = 0,
                itemLen:int,
                itemData:LegendItemData,
                message:String;
            for(; i < seriesCount; i++)
            {
                series = ISeries(this.series[i]);
                if(series is ILegendItemSeries)
                {
                    if(!(series as ILegendItemSeries).showInLegend) continue;
                    itemData = ILegendItemSeries(series).createLegendItemData();
                    itemData.label = itemData.label ? itemData.label : i.toString();
                    if(series.legendLabelFunction != null && series.legendLabelFunction is Function)
                    {
                        try
                        {
                            itemData.label = series.legendLabelFunction(itemData.label);
                        }
                        catch(e:Error)
                        {
                            message = "There is an error in the series level legendLabelFunction.";
                            this.dispatchEvent(new ErrorEvent(ErrorEvent.ERROR, false, false, message));
                        }
                    }
                    else if(this.legendLabelFunction != null && this.legendLabelFunction is Function)
                    {
                        try
                        {
                            message = "There is an error in the legendLabelFunction.";
                            itemData.label = this.legendLabelFunction(itemData.label);
                        }
                        catch(e:Error)
                        {
                            this.dispatchEvent(new ErrorEvent(ErrorEvent.ERROR, false, false, message));
                        }
                    }
                    legendData.push(itemData);
                }
                else if(series is ICategorySeries)
                {
                    legendData = legendData.concat(ICategorySeries(series).createLegendItemData());
                    if(this.legendLabelFunction != null && this.legendLabelFunction is Function)
                    {
                        if(legendData && legendData.length)
                        {
                            itemLen = legendData.length;
                        }
                        for(; n < itemLen; ++n)
                        {
                            itemData = legendData[n];
                            itemData.label = this.legendLabelFunction(itemData.label);
                        }
                    }
                }
            }
            
            this.legend.dataProvider = legendData;
            
            if(UIComponent.inCallLaterPhase)
            {
                UIComponent(this.legend).drawNow();
            }
        }
        
        /**
         * @private
         * Tranfers the chart's styles to the ISeries components it contains. These styles
         * must be of the type Array, and the series index determines the index of the value
         * to use from that Array. If the chart contains more ISeries components than there
         * are values in the Array, the indices are reused starting from zero.
         */
        protected function copyStylesToSeries(child:ISeries, styleMap:Object):void
        {
            var index:int = this.series.indexOf(child);
            var childComponent:UIComponent = child as UIComponent;
            for(var n:String in styleMap)
            {
                var styleValues:Array = this.getStyleValue(styleMap[n]) as Array;
                
                //if it doesn't exist, ignore it and go with the defaults for this series
                if(styleValues == null || styleValues.length == 0) continue;
                childComponent.setStyle(n, styleValues[index % styleValues.length])
            }
        } 
        
        /**
         * @private
         */
        protected function defaultDataTipFunction(item:Object, index:int, series:ISeries):String
        {
            if(series.displayName)
            {
                return series.displayName;
            }
            return "";
        }
        
        /**
         * @private
         * Passes data to the data tip.
         */
        protected function refreshDataTip():void
        {
            var item:Object = this._lastDataTipRenderer.data;
            var series:ISeries = this._lastDataTipRenderer.series;
            var index:int = series.itemRendererToIndex(this._lastDataTipRenderer);
            
            var dataTipText:String = "";
            if(series.dataTipFunction != null)
            {
                try
                {
                    dataTipText = series.dataTipFunction(item, index, series);
                }
                catch(e:Error)
                {
                    var message:String = "There is an error in your series level dataTipFunction";
                    this.dispatchEvent(new ErrorEvent(ErrorEvent.ERROR, false, false, message));
                }
            }
            else if(this.dataTipFunction != null)
            {
                try
                {
                    dataTipText = this.dataTipFunction(item, index, series);
                }
                catch(e:Error)
                {
                    message = "There is an error in your dataTipFunction";
                    this.dispatchEvent(new ErrorEvent(ErrorEvent.ERROR, false, false, message));
                }
            }
            
            var dataTipRenderer:IDataTipRenderer = this.dataTip as IDataTipRenderer;
            dataTipRenderer.text = dataTipText;
            dataTipRenderer.data = item;
            
            this.setChildIndex(this.dataTip, this.numChildren - 1);
            if(this.dataTip is UIComponent)
            {
                UIComponent(this.dataTip).drawNow();
            }
        }
        
    //--------------------------------------
    //  Protected Event Handlers
    //--------------------------------------
        
        /**
         * @private
         * Display the data tip when the user moves the mouse over a chart marker.
         */
        protected function chartItemRollOver(event:ChartEvent):void
        {	
            this._lastDataTipRenderer = event.itemRenderer;
            this.refreshDataTip();
            
            var position:Point = this.mousePositionToDataTipPosition();
            this.dataTip.x = position.x;
            this.dataTip.y = position.y;
            this.dataTip.visible = true;
            
            this.stage.addEventListener(MouseEvent.MOUSE_MOVE, stageMouseMoveHandler, false, 0 ,true);
        }
        
        /**
         * @private
         * Hide the data tip when the user moves the mouse off a chart marker.
         */
        protected function chartItemRollOut(event:ChartEvent):void
        {
            this.stage.removeEventListener(MouseEvent.MOUSE_MOVE, stageMouseMoveHandler);
            this.dataTip.visible = false;
        }
        
    //--------------------------------------
    //  Private Methods
    //--------------------------------------
        
        /**
         * @private
         * Determines the position for the data tip based on the mouse position
         * and the bounds of the chart. Attempts to keep the data tip within the
         * chart bounds so that it isn't hidden by any other display objects.
         */
        private function mousePositionToDataTipPosition():Point
        {
            var position:Point = new Point();
            position.x = this.mouseX + 2;
            position.x = Math.min(this.width - this.dataTip.width, position.x);
            position.y = this.mouseY - this.dataTip.height - 2;
            position.y = Math.max(0, position.y);
            return position;
        }
        
    //--------------------------------------
    //  Private Event Handlers
    //--------------------------------------
        
        /**
         * @private
         * The plot area needs to redraw the axes if series data changes.
         */
        private function seriesDataChangeHandler(event:Event):void
        {
            this.invalidate(InvalidationType.DATA);
            if(this.dataTip.visible)
            {
                this.refreshDataTip();
            }
        }
        
        /**
         * @private
         * Make the data tip follow the mouse.
         */
        private function stageMouseMoveHandler(event:MouseEvent):void
        {
            var position:Point = this.mousePositionToDataTipPosition();
            this.dataTip.x = position.x;
            this.dataTip.y = position.y;
        }
    }
}
