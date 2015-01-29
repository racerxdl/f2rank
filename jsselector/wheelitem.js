PUMP_SELECTOR.WheelItem = PUMP_SELECTOR.WheelItem || function ( parameters ) {
    var _this = this;
    this.width = 140;
    this.height= 110;
    this.image = new Image();
    this.preview = new Image();
    this.filename = parameters.image;
    this.Loaded = false;
    
    this.skin = parameters.skin;
    this.preview.onload = function()   {PUMP_SELECTOR.Globals.LoadedData += 1;};
    this.opacity = parameters.opacity || 1;
    this.scale   = parameters.scale || {x:1,y:1};
    this.visible = parameters.visible || true;
    this.movespeed = 0.01;
    this.anchor  = { x:0,y:0 };
    this.targetx = parameters.targetx || 0;
    this.targety = parameters.targety || 0;
    this.x = parameters.x || 0;
    this.y = parameters.y || 0;

    this.Load = function()  {
        if(!_this.Loaded)   {
            
            _this.image = _this.skin.EYE[_this.filename.split(".")[0]];
            if(_this.image == undefined)
                _this.image = _this.skin.EYE["BLANK"];  
            _this.anchor = { x: _this.image.width/2 , y: _this.image.height/2 };
            _this.preview = _this.skin.PREVIEW[_this.filename.split(".")[0]];
            _this.Loaded = true;
            
        }
    };
    this.Draw = function(ctx)   {
        if( _this.opacity != 0 && _this.visible)  {
            var oldAlpha = ctx.globalAlpha;
            var newHeight   =   (_this.image.height * _this.scale.y) >> 0,
                newWidth    =   (_this.image.width * _this.scale.x) >> 0,
                newX        =   (_this.x + _this.anchor.x - newWidth/2) >> 0,
                newY        =   (_this.y + _this.anchor.y - newHeight/2) >> 0;
           ctx.globalAlpha = _this.opacity;
           ctx.drawImage(_this.image,newX,newY,newWidth, newHeight);
           ctx.globalAlpha = oldAlpha;
           if(PUMP_SELECTOR.Globals.DrawAnchors)   {
                ctx.beginPath();
                ctx.arc(_this.anchor.x+_this.x, _this.anchor.y+_this.y, 4, 0, 2 * Math.PI, false);
                ctx.fillStyle = 'red';
                ctx.fill();
                ctx.lineWidth = 2;
                ctx.strokeStyle = '#003300';
                ctx.stroke();
             }
        }
    };
    this.MoveTo = function(x,y) {
        _this.targetx = x;
        _this.targety = y;
    };
    this.Move = function(x,y)   {
        _this.targetx = x;
        _this.targety = y;
        _this.x = x;
        _this.y = y;
    };
    this.Update = function(timeDelta)   {
        var deltaX = _this.targetx - _this.x,
            deltaY = _this.targety - _this.y;
        if(Math.round(deltaX) == 0) 
            _this.x = _this.targetx;
        else    
            _this.x += timeDelta * deltaX * _this.movespeed;
        if(Math.round(deltaY) == 0)
            _this.y = _this.targety;
        else
            _this.y += timeDelta * deltaY * _this.movespeed;
    };
};
