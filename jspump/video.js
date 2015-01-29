/********************** VideoPlayer  ************************/
PUMPER.VideoPlayer = PUMPER.VideoPlayer || function ( parameters ) {
    this.filename = parameters.filename;
    this.autoplay = parameters.autoplay || false;
    this.Enable   = parameters.enable || true;
    this.idname = "vplay-"+new Date().getUTCMilliseconds();

    this.Create();
};
PUMPER.VideoPlayer.prototype.GetVideo = function()  {   return this.videounit; };
PUMPER.VideoPlayer.prototype.Destroy  = function()  {
        $("#"+this.idname).remove();
        $(this.videoHolder).remove();
        this.videounit = new Image();
        this.Created = false;
};
PUMPER.VideoPlayer.prototype.Create = function()    {
        if(!this.Created && this.Enable) {
            if(this.filename == undefined || !this.Enable)    {
                PUMPER.debug("Creating PUMPER::VideoPlayer with Dummy as "+this.idname);
                this.videounit = new Image();
            }else{
                PUMPER.debug("Creating PUMPER::VideoPlayer with "+this.filename+" as "+this.idname);
                this.videoHolder = document.createElement('div');
	            this.videoHolder.setAttribute("style", "display:none;");
	            $(this.videoHolder).html('<video controls loop id="'+this.idname+'"  width="320" height="240" hidden>' + 
		                '<source src="'+this.filename+'.webm" type=video/webm>' + 
                        '<source src="'+this.filename+'.ogg" type=video/ogg>'  + 
                        '<source src="'+this.filename+'.mp4" type=video/mp4>'  + 
                        '</video>');
                $('body').append(this.videoHolder);   
                _vthis.videounit = document.getElementById(this.idname);   
                if(this.autoplay) 
                    setTimeout(this.Play, 1000);
            }
            this.Created = true;
        }else{
            if(!this.Enable)
                 this.videounit = new Image();
        }
    }
PUMPER.VideoPlayer.prototype.Play = function()  {
        PUMPER.debug("PUMPER::VideoPlayer("+this.idname+").Play()");
        if(this.Enable)
            this.videounit.play();
};
PUMPER.VideoPlayer.prototype.Pause = function() {
        PUMPER.debug("PUMPER::VideoPlayer("+this.idname+").Pause()");
        this.videounit.pause();
};
PUMPER.VideoPlayer.prototype.ChangeVideo = function(filename,autoplay) {
        this.filename = filename;
        this.autoplay = autoplay || false;
        this.Destroy();
        this.Create();
};
