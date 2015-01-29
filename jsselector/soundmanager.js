PUMP_SELECTOR.SoundManager = PUMP_SELECTOR.SoundManager || function ( parameters ) {
    var _this = this;
    PUMP_SELECTOR.Globals.DataToLoad += 1;
    
    this.AUDIOLoader = new JPAK.jpakloader({"file":"datapack/AUDIO.jpak"});
    this.AUDIOLoader.onload = function()    {
        var supported = PUMP_SELECTOR.GetCompatibleCodecs();
        var ext = (supported.audio.indexOf("mp3") > -1)?"mp3":"ogg";
        var mime = (supported.audio.indexOf("mp3") > -1)?"audio/mpeg":"audio/ogg";
        _this.Switch    =   new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/move."+ext, mime), "overrideext" : true});
        _this.CMDSet    =   new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/cmdset."+ext, mime), "overrideext" : true});
        _this.Back      =   new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/back."+ext, mime), "overrideext" : true});
        _this.Press     =   new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/center."+ext, mime), "overrideext" : true});
        _this.ChannelSound = {
            "All Tunes"             : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/alltunes."+ext, mime), "overrideext" : true}),
            "Full Song"             : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/fullsongs."+ext, mime), "overrideext" : true}),
            
            "Remix"                 : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/remix."+ext, mime), "overrideext" : true}),
            "Shortcut"              : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/shortcut."+ext, mime), "overrideext" : true}),
            "Skill Up Zone"         : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/skillup."+ext, mime), "overrideext" : true}),
            "Mission Zone"          : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/missionzone."+ext, mime), "overrideext" : true}),
            
            "1st-3rd"               : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/1st-3rd."+ext, mime), "overrideext" : true}),
            "se-extra"              : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/se-extra."+ext, mime), "overrideext" : true}),
            "rebirth-prex3"         : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/rebirth-prex3."+ext, mime), "overrideext" : true}),
            "exceed-zero"           : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/exceed-zero."+ext, mime), "overrideext" : true}),
            "nxnx2"                 : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/nxnx2."+ext, mime), "overrideext" : true}),
            
            "NX Absolute"           : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/nxa."+ext, mime), "overrideext" : true}),
            "Fiesta"                : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/fiesta."+ext, mime), "overrideext" : true}),
            "Fiesta Ex"             : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/fiestaex."+ext, mime), "overrideext" : true}),
            "Fiesta 2"              : new PUMP_SELECTOR.SoundPlayer({filename:this.GetFileURL("/fiesta2."+ext, mime), "overrideext" : true})
        };
        PUMP_SELECTOR.Globals.LoadedData += 1;
    };
    this.AUDIOLoader.onprogress = function(progress)   {
        if(!this.AddedTotal)    {
            this.AddedTotal = true;
            this.LastLoaded = progress.loaded;
            PUMP_SELECTOR.Globals.LoadedData += this.LastLoaded;
            PUMP_SELECTOR.Globals.DataToLoad += progress.total;  
        }else{
            PUMP_SELECTOR.Globals.LoadedData += progress.loaded - this.LastLoaded;
            this.LastLoaded = progress.loaded;  
        }    
    };
    this.AUDIOLoader.Load();
    this.Switch = new PUMP_SELECTOR.SoundPlayer({});
    this.CMDSet = new PUMP_SELECTOR.SoundPlayer({});
    this.Back = new PUMP_SELECTOR.SoundPlayer({});
    this.Press = new PUMP_SELECTOR.SoundPlayer({});
    
    this.ChannelSound = {
        "All Tunes"             : new PUMP_SELECTOR.SoundPlayer({}),
        "Full Song"             : new PUMP_SELECTOR.SoundPlayer({}),
        
        "Remix"                 : new PUMP_SELECTOR.SoundPlayer({}),
        "Shortcut"              : new PUMP_SELECTOR.SoundPlayer({}),
        "Skill Up Zone"         : new PUMP_SELECTOR.SoundPlayer({}),
        "Mission Zone"          : new PUMP_SELECTOR.SoundPlayer({}),
        
        "1st-3rd"               : new PUMP_SELECTOR.SoundPlayer({}),
        "se-extra"              : new PUMP_SELECTOR.SoundPlayer({}),
        "rebirth-prex3"         : new PUMP_SELECTOR.SoundPlayer({}),
        "exceed-zero"           : new PUMP_SELECTOR.SoundPlayer({}),
        "nxnx2"                 : new PUMP_SELECTOR.SoundPlayer({}),
        
        "NX Absolute"           : new PUMP_SELECTOR.SoundPlayer({}),
        "Fiesta"                : new PUMP_SELECTOR.SoundPlayer({}),
        "Fiesta Ex"             : new PUMP_SELECTOR.SoundPlayer({}),
        "Fiesta 2"              : new PUMP_SELECTOR.SoundPlayer({})
    };
    this.PlaySwitch = function()    {
        if(PUMP_SELECTOR.Globals.EnableSound)
            _this.Switch.Play();
    };
    this.PlayCMDSet = function()    {     
        if(PUMP_SELECTOR.Globals.EnableSound)
           _this.CMDSet.Play();
    };
    this.PlayBack = function()  {
        if(PUMP_SELECTOR.Globals.EnableSound)
            _this.Back.Play();
    };
    this.PlayPress = function() {
         if(PUMP_SELECTOR.Globals.EnableSound)
            _this.Press.Play();
    };
    this.PlayMusic = function(music)    {
        if(_this.Music != undefined)    {
            if( _this.Music.filename != music )    {
                _this.Music.Pause();
                _this.Music = new PUMP_SELECTOR.SoundPlayer({filename:music,autoplay:true});
            }else if(_this.Music.audiounit.paused){
                _this.Music.Play();
            }
        }else
            _this.Music = new PUMP_SELECTOR.SoundPlayer({filename:music,autoplay:true});
    };
    this.PauseMusic = function()    {
        if(_this.Music != undefined)    {
            _this.Music.Pause();
        }    
    };
};
