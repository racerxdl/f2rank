PUMP_SELECTOR.AnimatedObject = PUMP_SELECTOR.AnimatedObject || function (parameters) {
    var _this = this;
    
    this.opacity = parameters.opacity || 1;
    this.x = parameters.x || 0;
    this.y = parameters.y || 0;
    this.scale = parameters.scale || {x:1,y:1};
    
    this.layer = parameters.layer || 3;
    
    this.image = parameters.image || new Image();
    
    this.infinite = parameters.infinite || true;
    this.lifetime = parameters.lifetime || 0;
    this.visible  = parameters.visible || true;
    
    this.livedtime = 0;
    this.Drawer = undefined;
    this.id = '_AnimObj_' + Math.floor(Math.random()*1000000) + "_" + (new Date()).getTime();
    //console.log("Created new PUMP_SELECTOR::AnimatedObject with id "+this.id);
    
    this.anchor = { x : this.image.width/2 , y : this.image.height / 2 };
    this.dir = false;
    this.Draw = function(ctx)   {
        if(_this.opacity != 0 && _this.visible)  {
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
    this.Update = parameters.update || function(timeDelta)   {
        //  PlaceHolder
    };
    this.CheckLife = function(timeDelta)    {
        if(!_this.infinite)
            _this.livedtime += timeDelta;
        if( (_this.livedtime >= _this.lifetime) && !_this.infinite) {
            //  Good bye, cruel world! :'(
            _this.Drawer.RemoveAnimObj(_this.id);
            return true;
        }    
        return false;
    };
};

