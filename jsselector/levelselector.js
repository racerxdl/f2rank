PUMP_SELECTOR.LevelSelector = PUMP_SELECTOR.LevelSelector || function ( parameters) {
    var _this = this;
    
    this.skin = parameters.skin;
    this.selectedindex = 0;
    this.lastselectedindex = 0;
    
    this.songlist = parameters.songlist || [];
    this.items = [];
    this.pos = parameters.pos || { x: 0, y: 0};
    this.targetx = this.pos.x;
    this.targety = this.pos.y;
    this.movespeed = 0.01;
    this.visible = true;

    this.readyvisible = false;
    this.readyopacity = 0.9;
    this.readyopacitydir = false;
    this.pinkrightdx = 0;
    this.pinkleftdx  = 0;
    this.UpdateItems = function(timeDelta)  {
        //for(var i=0;i<_this.items.length;i++)   
        //    _this.items[i].Update(timeDelta);
        
    };
    this.Draw = function(ctx,x,y)   {
        if(_this.visible && PUMP_SELECTOR.Globals.SelectedSong.levellist != undefined)   {
            if(_this.selectedindex >= PUMP_SELECTOR.Globals.SelectedSong.levellist.length)   {
                _this.selectedindex = 0;
                PUMP_SELECTOR.Globals.SelectedChart = _this.selectedindex;
            }
            var currentlevel = PUMP_SELECTOR.Globals.SelectedSong.levellist[_this.selectedindex];
            // Fix Scores:
            currentlevel.myscore = (currentlevel.myscore==undefined)?12345:currentlevel.myscore;
            currentlevel.machinescore = (currentlevel.machinescore==undefined)?1234567:currentlevel.machinescore;
            
            ctx.drawImage(_this.skin.BASE["BALL"+currentlevel.type.toUpperCase()], 0,0,114,114,_this.pos.x - 114/2, _this.pos.y, 114, 114);
            _this.skin.DrawBigNumber(ctx, _this.pos.x-40, _this.pos.y+40, currentlevel.level, 39, 2) ;
            ctx.drawImage(_this.skin.BASE.SCOREBAR, _this.pos.x-118, _this.pos.y+90);
            ctx.drawImage(_this.skin.BASE.PINKLEFT, _this.pos.x-93-_this.pinkleftdx, _this.pos.y+20);
            ctx.drawImage(_this.skin.BASE.PINKRIGHT, _this.pos.x+53+_this.pinkrightdx, _this.pos.y+20);
            
            if(currentlevel.myscore != undefined)
                _this.skin.DrawBigNumber(ctx, _this.pos.x+56-((currentlevel.myscore.toString().length)*10)/2, _this.pos.y+123, currentlevel.myscore, 10);
            // 48px
            if(currentlevel.machinescore != undefined)
                _this.skin.DrawBigNumber(ctx, _this.pos.x-56-((currentlevel.machinescore.toString().length)*10)/2, _this.pos.y+123, currentlevel.machinescore, 10);
           
            if(currentlevel.machinename != undefined)
            	_this.skin.DrawIDName(ctx, _this.pos.x-56-((currentlevel.machinename.length)*10)/2, _this.pos.y+110, currentlevel.machinename);
            
            if(currentlevel.myname != undefined)
            	_this.skin.DrawIDName(ctx, _this.pos.x+56-((currentlevel.myname.length)*10)/2, _this.pos.y+110, currentlevel.myname);
            
            if(PUMP_SELECTOR.Globals.ChartSelected)  {
                var oldalpha = ctx.globalAlpha;
                ctx.globalAlpha = _this.readyopacity;
                ctx.drawImage(_this.skin.BASE.READY, _this.pos.x-50, _this.pos.y+80);
                ctx.globalAlpha = oldalpha;
            } 
        }
    };

    this.nextlevel = function()  {
        _this.lastselectedindex = _this.selectedindex;
        _this.selectedindex += 1;
        if(_this.selectedindex == PUMP_SELECTOR.Globals.SelectedSong.levellist.length)
            _this.selectedindex = 0;
        _this.pinkrightdx = 8;
        PUMP_SELECTOR.Globals.SelectedChart = _this.selectedindex;
    };
    this.prevlevel = function()  {
        _this.lastselectedindex = _this.selectedindex;
        _this.selectedindex -= 1;
        if(_this.selectedindex < 0 )
            _this.selectedindex = PUMP_SELECTOR.Globals.SelectedSong.levellist.length-1; 
        _this.pinkleftdx = 8;
        PUMP_SELECTOR.Globals.SelectedChart = _this.selectedindex;
    };
    this.MoveTo = function(x,y) {
        _this.targetx = x;
        _this.targety = y;
    };
    this.Move = function(x,y)   {
        _this.targetx = x;
        _this.targety = y;
        _this.pos.x = x;
        _this.pos.y = y;
    };
    this.Update = function(timeDelta)   {
        var deltaX = _this.targetx - _this.pos.x,
            deltaY = _this.targety - _this.pos.y;
        if(Math.round(deltaX) == 0) 
            _this.pos.x = _this.targetx;
        else    
            _this.pos.x += timeDelta * deltaX * _this.movespeed;
        if(Math.round(deltaY) == 0)
            _this.pos.y = _this.targety;
        else
            _this.pos.y += timeDelta * deltaY * _this.movespeed;
        
        if(_this.readyopacitydir)
            _this.readyopacity -= 0.001 * timeDelta;
        else
            _this.readyopacity += 0.001 * timeDelta;
        
        if(_this.readyopacity > 1)  {
            _this.readyopacitydir = true;
            _this.readyopacity = 1;
        }else if(_this.readyopacity < 0.8)  {
            _this.readyopacitydir = false;
            _this.readyopacity = 0.8;
        }
        
        if(_this.pinkleftdx > 0)    
            _this.pinkleftdx -= 0.05 * timeDelta;
        else
            _this.pinkleftdx = 0;
        
        if(_this.pinkrightdx > 0)    
            _this.pinkrightdx -= 0.05 * timeDelta;
        else
            _this.pinkrightdx = 0;

        _this.UpdateItems(timeDelta);

    };
};
