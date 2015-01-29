PUMP_SELECTOR.Looper = PUMP_SELECTOR.Looper || function ( parameters )  {
    var _this = this;
    this.Drawer = parameters.drawer;
    
    this.loop = function()  {
        if(_this.Drawer !== undefined)   { 
        	_this.Drawer.ClearScreen();
            _this.Drawer.UpdateAnimations();
            _this.Drawer.DrawVideo();
            if(PUMP_SELECTOR.Globals.DataToLoad > PUMP_SELECTOR.Globals.LoadedData || !PUMP_SELECTOR.LoadStarted) {
                PUMP_SELECTOR.Globals.Loaded = false;
                this.Drawer.DrawLoading();
            }else{
                PUMP_SELECTOR.Globals.Loaded = true;
                _this.Drawer.DrawChannelName();
                _this.Drawer.DrawWheel();
                _this.Drawer.DrawLevelSelector();
                _this.Drawer.DrawPreview();
                _this.Drawer.DrawLevelBar();
                _this.Drawer.DrawArrows();
                //_this.Drawer.DrawSpeed(_this.Speed);
                _this.Drawer.DrawAnimObj(4);
            }
        }
    };
};
