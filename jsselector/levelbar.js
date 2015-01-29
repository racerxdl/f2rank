PUMP_SELECTOR.LevelBar = PUMP_SELECTOR.LevelBar || function (parameters) {
    var _this = this;
    this.skin = parameters.skin;
    this.levellist = [];
    this.scale = 0.82;
    this.targetscale = this.scale;
    this.movespeed = 0.01;
    //0,6910299
    this.UpdateLevelList = function(levellist)  {
        _this.levellist = levellist;
    };
    this.Draw = function(ctx)  {
        var width  = 602 * _this.scale;
        var height = 52  * _this.scale;
        var newx   = 320 - width/2;
        var newy   = 240 - height/2 + 38;
        var circlesize = 44* _this.scale
        ctx.drawImage(_this.skin.LEVELBAR.LEVELBAR, newx, newy, width, height);
        for(var i=0;i<_this.levellist.length;i++)   {
            var lvl = _this.levellist[i];
             ctx.drawImage(_this.skin.LEVELBAR[lvl.type.toUpperCase()],0,0,44,44,newx+i*((4*_this.scale)+circlesize)+(15*_this.scale),newy+(5*_this.scale),circlesize,circlesize);
             _this.skin.DrawBigNumber(ctx, newx+i*((4*_this.scale)+circlesize)+(21*_this.scale), newy+(20*_this.scale), lvl.level, 15*_this.scale, 2);
        }  
        if(PUMP_SELECTOR.Globals.Scene == PUMP_SELECTOR.Scenes.SELECT_CHART)      
            _this.skin.DrawP1Selector(ctx,newx+(5*_this.scale) + PUMP_SELECTOR.Globals.SelectedChart *((4*_this.scale)+circlesize) ,newy-(5*_this.scale),_this.scale+0.05); 
    };
    this.Update = function(timeDelta)   {
        var delta = _this.targetscale - _this.scale;
        if(delta == 0) 
            _this.scale = _this.targetscale;
        else    
            _this.scale += timeDelta * delta * _this.movespeed;    
    };
};
