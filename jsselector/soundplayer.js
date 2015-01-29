PUMP_SELECTOR.SoundPlayer = PUMP_SELECTOR.SoundPlayer || function (parameters) {
    var _athis = this;
    this.idname = "aplay-"+new Date().getUTCMilliseconds();
    this.filename = parameters.filename;
    this.autoplay = parameters.autoplay || false;
    this.audiounit = new Audio();
    
    this.overrideext = parameters.overrideext || false;
    
    //this.audiounit.preload = "auto";
    //this.audiounit.autoplay = this.autoplay;
    this.audiounit.loop = parameters.loop || false;
    //console.debug("Creating PUMP_SELECTOR::AudioPlayer with "+_athis.filename);
    if(this.filename != undefined)  {
        if(this.overrideext)    {
            this.audiounit.src = this.filename;
        }else{
            if(this.audiounit.canPlayType && this.audiounit.canPlayType('audio/mpeg;').replace(/no/, ''))
                this.audiounit.src = this.filename+".mp3";
            else
                this.audiounit.src = this.filename+".ogg";
        }  
    }  
    this.Play = function()  {
        //console.debug("PUMP_SELECTOR::AudioPlayer("+_athis.idname+").Play()");
        if(PUMP_SELECTOR.Globals.EnableSound)   {
            if(_athis.audiounit.readyState != 0)    {
                _athis.audiounit.currentTime = 0;
                _athis.audiounit.play();
            }else{
                clearTimeout(_athis.playtimeout);
                _athis.playtimeout = setTimeout(_athis.Play, 1000);
            }
        }
    };
    this.Pause = function() {
        //console.debug("PUMP_SELECTOR::AudioPlayer("+_athis.idname+").Pause()");
        clearTimeout(_athis.playtimeout);
        _athis.audiounit.pause();
    };
    clearTimeout(_athis.playtimeout);
    if(parameters.autoplay) 
       this.playtimeout = setTimeout(_athis.Play, 1000);
       
};
