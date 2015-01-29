PUMPER.EffectBank = function(parameters)  {
    this.drawer = parameters.drawer;
    this.gl     = parameters.gl;
    this.FlashEffect = new PUMPER.Effects.FlashEffect(this.drawer, this.gl);
    PUMPER.Globals.ObjectsToLoad += 1;
    
};

PUMPER.Effects = {};

PUMPER.Effects.FlashEffect = function(drawer, gl)    {
    this.drawer = drawer;
    this.gl = gl;
    this.CurrentBeat100 = -1;
    this.EffectImage = document.createElement("canvas");
    this.EffectImage.width = this.drawer.canvas.width;
    this.EffectImage.height = this.drawer.canvas.height;
    //  This effect is just a blank screen
    var ctx = this.EffectImage.getContext("2d");

    ctx.beginPath();
    ctx.rect(0, 0, this.EffectImage.width, this.EffectImage.height);
    ctx.fillStyle = 'white';
    ctx.fill();
    if(!PUMPER.Globals.WebGL)
        this.EffectTexture = this.EffectImage;
    else{
        this.EffectTexture = this.gl.createTexture();
        this.gl.bindTexture(this.gl.TEXTURE_2D, this.EffectTexture);
        this.gl.pixelStorei(this.gl.UNPACK_FLIP_Y_WEBGL, true);
        this.gl.texImage2D(this.gl.TEXTURE_2D, 0, this.gl.RGBA, this.gl.RGBA, this.gl.UNSIGNED_BYTE, PUMPER.GL.ToPowerOfTwo(this.EffectImage));
        this.gl.texParameteri(this.gl.TEXTURE_2D, this.gl.TEXTURE_MAG_FILTER, this.gl.LINEAR);
        this.gl.texParameteri(this.gl.TEXTURE_2D, this.gl.TEXTURE_MIN_FILTER, this.gl.LINEAR_MIPMAP_NEAREST); 
        this.gl.generateMipmap(this.gl.TEXTURE_2D);
        this.gl.bindTexture(this.gl.TEXTURE_2D, null);  
        this.EffectTexture.width = PUMPER.GL.nextHighestPowerOfTwo(this.width); 
        this.EffectTexture.height = PUMPER.GL.nextHighestPowerOfTwo(this.height); 
        this.EffectTexture.rwidth = this.width;
        this.EffectTexture.rheight = this.height;     
    }    
    this.EffectObject = new PUMPER.AnimatedObject({
        "image"     :   this.EffectTexture, 
        "opacity"   :   0,
        "x"         :   0,
        "y"         :   0,
        "gl"        :   this.gl,
        "Update"    :   function(timeDelta) {
            if(this.opacity != 0)   {
                var delta = 0;
                if(this.MusicTime != undefined) 
                    delta = PUMPER.Globals.Music.GetTime() - this.MusicTime;
                this.MusicTime = PUMPER.Globals.Music.GetTime();
                this.opacity -= (delta * this.AnimTime);
                if(this.opacity < 0)
                    this.opacity = 0;
                this.NeedsRedraw = true;
            }
        }
    });    
    this.EffectObject.Start = function()    {
        this.opacity = 0.95;
    };
    this.EffectObject.AnimTime = 1;
    this.drawer.AddObj(this.EffectObject,4);
    PUMPER.Globals.ObjectsLoaded += 1;
    PUMPER.Globals.CheckLoaded();
};
PUMPER.Effects.FlashEffect.prototype.Start = function ( CurrentBeat)   {
    if((CurrentBeat*100)>>0 > this.CurrentBeat100)  {
        this.CurrentBeat100 = (CurrentBeat*100)>>0;
        this.EffectObject.Start();
        //Do Effect
    }
};
