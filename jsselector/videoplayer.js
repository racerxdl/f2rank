PUMP_SELECTOR.VideoPlayer = PUMP_SELECTOR.VideoPlayer || function ( parameters ) {
    var _vthis = this;
    this.filename = parameters.filename;
    this.autoplay = parameters.autoplay || false;
    this.Enable   = parameters.enable || true;
    this.idname = "vplay-"+new Date().getUTCMilliseconds();
    this.GetVideo = function()  {
        return _vthis.videounit;
    };
    this.Destroy = function()   {
        $("#"+_vthis.idname).remove();
        $(_vthis.videoHolder).remove();
        _vthis.videounit = new Image();
        _vthis.Created = false;
    };
    this.Create = function()    {
        if(!_vthis.Created && _vthis.Enable) {
            if(_vthis.filename == undefined || !_vthis.Enable)    {
                //console.debug("Creating PUMP_SELECTOR::VideoPlayer with Dummy as "+_vthis.idname);
                _vthis.videounit = new Image();
            }else{
                //console.debug("Creating PUMP_SELECTOR::VideoPlayer with "+_vthis.filename+" as "+_vthis.idname);
                _vthis.videoHolder = document.createElement('div');
	            _vthis.videoHolder.setAttribute("style", "display:none;");
	            $(_vthis.videoHolder).html('<video controls loop id="'+_vthis.idname+'"  width="320" height="240" hidden>' + 
		                '<source src="'+_vthis.filename+'.webm" type=video/webm>' + 
                        '<source src="'+_vthis.filename+'.ogg" type=video/ogg>'  + 
                        '<source src="'+_vthis.filename+'.mp4" type=video/mp4>'  + 
                        '</video>');
                $('body').append(_vthis.videoHolder);   
                _vthis.videounit = document.getElementById(_vthis.idname);   
                if(_vthis.autoplay) 
                    setTimeout(_vthis.Play, 1000);
            }
            _vthis.Created = true;
        }else{
            if(!_vthis.Enable)
                 _vthis.videounit = new Image();
        }
    };
    this.Play = function()  {
        //console.log("PUMP_SELECTOR::VideoPlayer("+_vthis.idname+").Play()");
        if(_vthis.Enable)
            _vthis.videounit.play();
    };
    this.Pause = function() {
        //console.log("PUMP_SELECTOR::VideoPlayer("+_vthis.idname+").Pause()");
        _vthis.videounit.pause();
    };
    this.ChangeVideo = function(filename,autoplay) {
        _vthis.filename = filename;
        _vthis.autoplay = autoplay || false;
        _vthis.Destroy();
        _vthis.Create();
    };
    this.Create();
};
