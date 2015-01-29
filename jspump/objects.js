
/************************* AnimatedObject **************************/
PUMPER.AnimatedObject = PUMPER.AnimatedObject || function (parameters) {
    if(parameters === undefined)    return;
    this.opacity    = parameters.opacity !== undefined ? parameters.opacity : 1;
    this.x          = parameters.x          || 0;
    this.y          = parameters.y          || 0;
    this.scale      = parameters.scale      || {x:1,y:1};
    this.rotation   = parameters.rotation   || 0;
    this.layer      = parameters.layer      || 3;
    
    this.image      = parameters.image      || new Image();
    
    this.infinite   = parameters.infinite   || true;
    this.lifetime   = parameters.lifetime   || 0;
    this.visible    = parameters.visible    || true;
    this.blendtype  = parameters.blendtype  || "source-over";
    
    this.gl         = parameters.gl;
    this.coord      = parameters.coord      || [ 0,0,0,0 ];
    
    this.livedtime  = 0;
    this.Drawer     = undefined;
    this.id         = '_AnimObj_' + Math.floor(Math.random()*1000000) + "_" + (new Date()).getTime();
    this.anchor     = { x : this.image.width/2 , y : this.image.height / 2 };
    
    this.Update = parameters.Update || function(timeDelta)   { /* Place Holder */ };
    this.NeedsRedraw = true;
    
    if(PUMPER.Globals.WebGL)    {
        this.VertexBuffer           = this.gl.createBuffer();
        this.TextureCoordBuffer     = this.gl.createBuffer();
        this.VertexIndexBuffer      = this.gl.createBuffer();
        this.VertexIndexArray       = [];
        if(this.coord[2] == 0 && this.coord[3] == 0)    {
            this.coord[2] = this.image.width;
            this.coord[3] = this.image.height;
        }
        this.image.XStep = 1.0 / this.image.width;
        this.image.YStep = 1.0 / this.image.height;
    }
    //PUMPER.debug("Created new PUMPER::AnimatedObject with id "+this.id);
    
};

PUMPER.AnimatedObject.prototype.Draw = function(ctx)   {
    if(this.opacity != 0 && this.visible)  {
        var oldAlpha = ctx.globalAlpha;
        var newHeight   =   (this.image.height * this.scale.y) >> 0,
            newWidth    =   (this.image.width * this.scale.x) >> 0,
            newX        =   (this.x + this.anchor.x - newWidth/2) >> 0,
            newY        =   (this.y + this.anchor.y - newHeight/2) >> 0,
            oldComp     =   ctx.globalCompositeOperation;
        ctx.save();
        ctx.globalAlpha = this.opacity;
        ctx.globalCompositeOperation = this.blendtype;
        ctx.rotate(this.rotation);
        ctx.drawImage(this.image,newX,newY,newWidth, newHeight);
        ctx.globalAlpha = oldAlpha;
        ctx.globalCompositeOperation = oldComp;
        ctx.restore();
        if(PUMPER.Globals.DrawAnchors)   {
            ctx.beginPath();
            ctx.arc(this.anchor.x+this.x, this.anchor.y+this.y, 4, 0, 2 * Math.PI, false);
            ctx.fillStyle = 'red';
            ctx.fill();
            ctx.lineWidth = 2;
            ctx.strokeStyle = '#003300';
            ctx.stroke();
        }
        this.NeedsRedraw = false;
    }        
};
PUMPER.AnimatedObject.prototype.GLUpdate  = function()  {
    if(this.NeedsRedraw)    {
        var data = PUMPER.GL.GenSprite(this.x,this.y,this.coord[2]-this.coord[0],this.coord[3]-this.coord[1],this.coord[0]*this.image.XStep,this.coord[1]*this.image.YStep,this.coord[2]*this.image.XStep,this.coord[3]*this.image.YStep,1,0);
        this.VertexIndexArray = data[2];
        this.gl.bindBuffer(this.gl.ARRAY_BUFFER, this.VertexBuffer);
        this.gl.bufferData(this.gl.ARRAY_BUFFER, new Float32Array(data[0]) , this.gl.STATIC_DRAW);

        this.gl.bindBuffer(this.gl.ARRAY_BUFFER, this.TextureCoordBuffer);
        this.gl.bufferData(this.gl.ARRAY_BUFFER, new Float32Array(data[1]) , this.gl.STATIC_DRAW);

        this.gl.bindBuffer(this.gl.ELEMENT_ARRAY_BUFFER, this.VertexIndexBuffer);
        this.gl.bufferData(this.gl.ELEMENT_ARRAY_BUFFER, new Uint16Array(data[2]), this.gl.STATIC_DRAW);
        this.NeedsRedraw = false;
    }
    if(this.shd != undefined)
        this.shd.Render();
};   
PUMPER.AnimatedObject.prototype.CheckLife = function(timeDelta)    {
        if(!this.infinite)
            this.livedtime += timeDelta;
        if( (this.livedtime >= this.lifetime) && !this.infinite) {
            this.Drawer.RemoveAnimObj(this.id);
            return true;
        }    
        return false;
};
PUMPER.AnimatedObject.prototype.Ressurect   = function(lifetime)    {
    this.lifetime = lifetime || this.lifetime || 0;
    this.livedtime = 0;
    this.NeedsRedraw = true;
};
PUMPER.AnimatedObject.prototype.SetPosition = function(x,y) {
    if(!PUMPER.Globals.SubPixelRender)  {
        x = x >> 0;
        y = y >> 0;
    }
    if(x != this.x || y != this.y)  {
        this.x = x;
        this.y = y;
        this.NeedsRedraw = true;
    }    
};
PUMPER.AnimatedObject.prototype.SetX        = function(x)   {
    if(!PUMPER.Globals.SubPixelRender)  
        x = x >> 0;
    if(x != this.x) {
        this.x = x;
        this.NeedsRedraw = true;
    }
};
PUMPER.AnimatedObject.prototype.SetY        = function(y)   {
    if(!PUMPER.Globals.SubPixelRender)  
        y = y >> 0;
    if(y != this.y) {
        this.y = y;
        this.NeedsRedraw = true;
    }
};
PUMPER.AnimatedObject.prototype.SetScale    = function(x,y) {
    if(!PUMPER.Globals.SubPixelRender)  {
        x = x >> 0;
        y = y >> 0;
    }
    if( x != this.scale.x || y != this.scale.y  )   {
        this.scale.x = x;
        this.scale.y = y;
        this.NeedsRedraw = true;
    }
};
PUMPER.AnimatedObject.prototype.SetScaleX   = function(x)   {
    if(!PUMPER.Globals.SubPixelRender)  
        x = x >> 0;
    if(x != this.scale.x)   {
        this.scale.x = x;
        this.NeedsRedraw = true;
    }
};
PUMPER.AnimatedObject.prototype.SetScaleY   = function(y)   {
    if(!PUMPER.Globals.SubPixelRender)  
        y = y >> 0;
    if(y != this.scale.y)   {
        this.scale.y = y;
        this.NeedsRedraw = true;
    }
};
PUMPER.AnimatedObject.prototype.SetVisible  = function(visible) {
    if(visible != this.visible) {
        this.visible = visible;
        this.NeedsRedraw = true;
    }
};
PUMPER.AnimatedObject.prototype.Clone = function()  {
    return new PUMPER.AnimatedObject({"opacity" : this.opacity, "x" : this.x, "y" : this.y, "scale" : { "x" : this.scale.x, "y" : this.scale.y}, "rotation" : this.rotation, "layer" : this.layer, "image" : this.image, "infinite": this.infinite, "lifetime" : this.lifetime, "visible": this.visible, "Update" : this.Update, "gl" : this.gl, "coord" : this.coord}); 
    /*
    var copy = new PUMPER.AnimatedObject({});
    for (var attr in this) {
        if (this.hasOwnProperty(attr)) copy[attr] = this[attr];
    }
    return copy;*/
};

/************************* FrameObject **************************/
PUMPER.FrameObject = PUMPER.FrameObject || function(parameters) {
    this.opacity    = parameters.opacity !== undefined ? parameters.opacity : 1;
    this.x          = parameters.x          || 0;
    this.y          = parameters.y          || 0;
    this.scale      = parameters.scale      || {x:1,y:1};
    this.rotation   = parameters.rotation   || 0;
    this.layer      = parameters.layer      || 3;
    
    this.Frames = parameters.frames         || [];
    this.image  =   this.Frames[0]          || new Image();
    this.CurrentFrame = 0;
    this.FrameTime = parameters.frametime   || 1;        
    this.Running = parameters.running       || false;
    this.RunOnce = parameters.runonce       || false;
    
    this.visible    = parameters.visible    || true;
    this.blendtype  = parameters.blendtype  || "source-over";
    
    this.Drawer     = undefined;
    this.beat       = 0;
    this.id         = '_FrameObj_' + Math.floor(Math.random()*1000000) + "_" + (new Date()).getTime();
    this.anchor     = { x : this.Frames[0].width/2 , y : this.Frames[0].height / 2 };
    this.gl         = parameters.gl;
    this.coord      = parameters.coord      || [ 0,0,0,0 ];
    
    if(PUMPER.Globals.WebGL)    {
        this.VertexBuffer           = this.gl.createBuffer();
        this.TextureCoordBuffer     = this.gl.createBuffer();
        this.VertexIndexBuffer      = this.gl.createBuffer();
        this.VertexIndexArray       = [];
        if(this.coord[2] == 0 && this.coord[3] == 0)    {
            this.coord[2] = this.image.width;
            this.coord[3] = this.image.height;
        }
        for(var i=0;i<this.Frames.length;i++)   {
            this.Frames[i].XStep = 1.0 / this.Frames[i].width;
            this.Frames[i].YStep = 1.0 / this.Frames[i].height;
        }
    }
    this.NeedsRedraw = true;
    //PUMPER.debug("Created new PUMPER::FrameObject with id "+this.id);
};

PUMPER.FrameObject.prototype = new PUMPER.AnimatedObject();
PUMPER.FrameObject.prototype.constructor = PUMPER.FrameObject;

PUMPER.FrameObject.prototype.Update = function(timeDelta)   {
    if(this.Running)    {
        this.CurrentFrame += (timeDelta/1000) * this.FrameTime;
        this.visible = true;
        if(this.CurrentFrame >= this.Frames.length) {
            this.CurrentFrame = 0;
            this.Running = !this.RunOnce;
            this.visible = false;
        }
        if(this.FrameNum != this.CurrentFrame >> 0) {
            this.FrameNum = this.CurrentFrame >> 0;
            this.image = this.Frames[this.FrameNum];
            this.NeedsRedraw = true;
        }
    }else{
        this.visible = false;
    }
};
PUMPER.FrameObject.prototype.Start  =   function(beat)  {
    if(beat !== undefined)  {
        if(this.beat != (beat*100) >>> 0)   {
            this.visible = true;
            this.Running = true;
            this.beat = (beat*100) >>> 0;
        }
    }else{
        this.visible = true;
        this.Running = true;
    }
    return this;
};
PUMPER.FrameObject.prototype.Stop   =  function()   {
    this.CurrentFrame = 0;
    this.visible = false;
    this.Running = false;
};
