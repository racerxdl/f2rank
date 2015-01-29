/********************** SoundManager ************************/
PUMPER.SoundManager = PUMPER.SoundManager || function () {};
PUMPER.SoundManager.prototype.PlaySwitch = function()    {
        if(PUMPER.Globals.EnableSound)
            this.Switch.Play();
};
PUMPER.SoundManager.prototype.PlayCMDSet = function()    {     
        if(PUMPER.Globals.EnableSound)
           this.CMDSet.Play();
};
PUMPER.SoundManager.prototype.PlayBack = function()  {
        if(PUMPER.Globals.EnableSound)
            this.Back.Play();
};
PUMPER.SoundManager.prototype.PlayPress = function() {
         if(PUMPER.Globals.EnableSound)
            this.Press.Play();
};
PUMPER.SoundManager.prototype.PlayBomb  = function()    {
        if(PUMPER.Globals.EnableSound)
            this.Bomb.Play();
};
PUMPER.SoundManager.prototype.PlayMusic = function(music)    {
        if(this.Music != undefined)    {
            if( this.Music.filename != music )    {
                this.Music.Pause();
                this.Music = new PUMPER.SoundPlayer({filename:music,autoplay:true,loop:true});
            }else if(_this.Music.audiounit.paused){
                this.Music.Play();
            }
        }else
            this.Music = new PUMPER.SoundPlayer({filename:music,autoplay:true,loop:true});
};
PUMPER.SoundManager.prototype.PauseMusic = function()    {
        if(this.Music != undefined)    
            this.Music.Pause();
      
};


/********************** SoundPlayer  ************************/
PUMPER.SoundPlayer = PUMPER.SoundPlayer || function (parameters) {
    this.idname = "aplay-"+new Date().getUTCMilliseconds();
    this.filename = parameters.filename;
    this.autoplay = parameters.autoplay || false;
    this.audiounit = new Audio();
    this.audiounit.loop = parameters.loop || false;
    this.resettozero = parameters.reset || false;
    this.buildnew = parameters.buildnew || false;
    //PUMPER.debug("Creating PUMPER::AudioPlayer with "+this.filename);
    
    /*
    var a,b;
    b=new Date();
    a=x+b.getTime();
    playing[a]=new Audio(sounds[x]);
    // with this we prevent playing-object from becoming a memory-monster:
    playing[a].onended=function(){delete playing[a]};
    playing[a].play();    
    */
    
    if(!this.buildnew)  {
        this.audiounit.src = this.filename;

        clearTimeout(this.playtimeout);
        if(parameters.autoplay) 
           this.playtimeout = setTimeout(this.Play, 300);
    }else{
        this.audiounit = {};
    }  
};
        
PUMPER.SoundPlayer.prototype.Play = function()  {
        var _this = this;
        PUMPER.debug("PUMPER::AudioPlayer("+this.idname+").Play()");
        if(PUMPER.Globals.EnableSound)   {
        
            if(this.buildnew)   {
                var a,b;
                b=new Date();
                a=b.getTime();
                this.audiounit[a] = new Audio(this.filename);
                this.audiounit[a].onended=function(){delete _this.audiounit[a];};
                this.audiounit[a].play();
            }else{
                if(this.audiounit.readyState != 0)    {
                        if(this.resettozero)
                            this.audiounit.currenttime = 0;
                        this.audiounit.play();
                }else{
                    clearTimeout(this.playtimeout);
                    this.playtimeout = setTimeout(this.Play, 300);
                }
            }
        }
};
PUMPER.SoundPlayer.prototype.Pause = function() {
        PUMPER.debug("PUMPER::AudioPlayer("+this.idname+").Pause()");
        clearTimeout(this.playtimeout);
        this.audiounit.pause();
};
PUMPER.SoundPlayer.prototype.GetTime = function()   {
    return this.audiounit.currentTime;
};

