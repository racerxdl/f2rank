PUMPER.TypeInvalid  =   0;
PUMPER.TypeUCS      =   1;
PUMPER.TypeNX       =   2;
PUMPER.TypeSM       =   3;
PUMPER.TypeSMA      =   4;
PUMPER.TypeSSC      =   5;
PUMPER.TypeJPAKNX   =   6;
PUMPER.TypeTextUCS  =   7;
PUMPER.TypeF2RankUCS=   8;

PUMPER.GameLoader = PUMPER.GameLoader || function(parameters)   {

    this.loadtype       =   parameters["loadtype"]         || PUMPER.TypeInvalid;
    this.loadargs       =   parameters["loadargs"]         || {};
    this.gamestats      =   parameters["gamestats"]        || {};
    this.canvasname     =   parameters["canvasname"]       || {};
    this.compcodec      =   PUMPER.GetCompatibleCodecs();
    this.HighSpeedAnimation = parameters["HighSpeedAnimation"] || false;
    if(this.loadtype == PUMPER.TypeInvalid) 
        PUMPER.debug("Invalid Load Type!");
           
};

PUMPER.GameLoader.prototype.Load    =   function()  {
    var _this = this;
    switch(this.loadtype)   {
        case PUMPER.TypeTextUCS:
            if(this.loadargs["ucsdata"] !== undefined && this.loadargs.songid !== undefined) {
                this.soundfile = (this.compcodec.audio.indexOf("mp3") > -1)?"ucs/mp3/"+this.loadargs["songid"]+".mp3":"ucs/ogg/"+this.loadargs["songid"]+".ogg";
                this.imagefile = "ucs/img/"+this.loadargs.songid+".jpg";
                this._UCS = PUMPER.UCSParser(this.loadargs.ucsdata);
                var gameCanvas = document.getElementById(_this.canvasname);
                PUMPER.Globals.PumpGame = new PUMPER.Game({"notedata" : this._UCS, "musicfile" : this.soundfile, "canvas": gameCanvas, "stats" : this.gamestats});
                PUMPER.Globals.PumpGame.AddBackground(_this.imagefile);
                this.Animate();
                setTimeout(function() { PUMPER.Globals.PumpGame.Play(); }, 1000);
            }else
                PUMPER.debug("No UCS data specifed!");               
            break;
        case PUMPER.TypeF2RankUCS:
        	if(this.loadargs["ucsurl"] !== undefined && this.loadargs["songid"] !== undefined) {
        		this.soundfile = (this.compcodec.audio.indexOf("mp3") > -1)?"ucs/mp3/"+this.loadargs["songid"]+".mp3":"ucs/ogg/"+this.loadargs["songid"]+".ogg";
        		this.imagefile = "ucs/img/"+this.loadargs.songid+".jpg";
            
        		$.getJSON(this.loadargs["ucsurl"],function(data)	{
        			if(data.status == "OK")	{
	                    _this._UCS = PUMPER.UCSParser(data.data.data);
	                    var gameCanvas = document.getElementById(_this.canvasname);
	                    PUMPER.Globals.PumpGame = new PUMPER.Game({"notedata" : _this._UCS, "musicfile" : _this.soundfile, "canvas": gameCanvas});
	                    PUMPER.Globals.PumpGame.AddBackground(_this.imagefile);
	                    _this.Animate();    
        			}else
        				PUMPER.debug("Fail to load data");
	    		});
        	}else
                PUMPER.debug("No UCS file specifed!");    
        	break;
        case PUMPER.TypeUCS:
            if(this.loadargs["ucsfile"] !== undefined && this.loadargs["songid"] !== undefined) {
                this.soundfile = (this.compcodec.audio.indexOf("mp3") > -1)?"ucs/mp3/"+this.loadargs.songid+".mp3":"ucs/ogg/"+this.loadargs.songid+".ogg";
                this.imagefile = "ucs/img/"+this.loadargs.songid+".jpg";
                $.ajax({
                    url : _this.loadargs.ucsfile,
                    dataType: "text",
                        success : function (data) {
                            _this._UCS = PUMPER.UCSParser(data);
                            var gameCanvas = document.getElementById(_this.canvasname);
                            PUMPER.Globals.PumpGame = new PUMPER.Game({"notedata" : _this._UCS, "musicfile" : _this.soundfile, "canvas": gameCanvas, "stats" : _this.gamestats});
                            PUMPER.Globals.PumpGame.AddBackground(_this.imagefile);
                            _this.Animate();
                        }
                });
            }else
                PUMPER.debug("No UCS file specifed!");        
            break;
        case PUMPER.TypeNX:
            if(this.loadargs.nxfile !== undefined && this.loadargs.songid !== undefined) {
                this.soundfile = (this.compcodec.audio.indexOf("mp3") > -1)?"ucs/mp3/"+this.loadargs.songid+".mp3":"ucs/ogg/"+this.loadargs.songid+".ogg";
                this.imagefile = "ucs/img/"+this.loadargs.songid+".jpg";
                
                var oReq = new XMLHttpRequest();
                oReq.open("GET", this.loadargs.nxfile, true);
                oReq.responseType = 'arraybuffer';
                oReq.onload = function(e) {
                    _this._NX = PUMPER.NXParser(this.response);
                    var gameCanvas = document.getElementById(_this.canvasname);
                    PUMPER.Globals.PumpGame = new PUMPER.Game({"notedata" : _this._NX, "musicfile" : _this.soundfile, "canvas": gameCanvas, "stats" : _this.gamestats});
                    PUMPER.Globals.PumpGame.AddBackground(_this.imagefile);
                    _this.Animate();
                    if(_this.loadargs.autoplay)
                        setTimeout(function() { PUMPER.Globals.PumpGame.Play(); }, 1000);
                };
                oReq.send();
            }else
                PUMPER.debug("No UCS file specifed!");        
            break;
        case PUMPER.TypeJPAKNX:
            if(this.loadargs.jpak !== undefined && this.loadargs.level !== undefined && this.loadargs.mode !== undefined)   {
                var gameCanvas = document.getElementById(_this.canvasname);
                if(PUMPER.Globals.WebGL)    {
                    this.buffcnv    =   PUMPER.createBuffCanvas(gameCanvas.width, gameCanvas.height);
                    this.ctx        =   this.buffcnv.getContext("2d");
                    this.gl         =   (PUMPER.Globals.WebGL) ? ( (PUMPER.Globals.glExperimental)? gameCanvas.getContext("experimental-webgl") : gameCanvas.getContext("webgl") ) : undefined;
                    this.Renderer   =   new PUMPER.GL.Renderer({"canvas": gameCanvas, "gl": this.gl});
                    var img = this.gl.createTexture();
                    img.width = gameCanvas.width; 
                    img.height = gameCanvas.height;
                    img.rwidth = gameCanvas.width;
                    img.rheight = gameCanvas.height;     
                    this.loadobject = new PUMPER.AnimatedObject({"image" : img, "gl" : this.gl});
                }else
                    this.ctx = gameCanvas.getContext("2d");
                this.jpakloader = new JPAK.jpakloader({"file" : this.loadargs.jpak});
                this.jpakloader.onload = function()   {
                    var NXFileName  = (_this.loadargs.location=="ARCADE")?"/CHARTS/"+_this.loadargs.mode+"/"+_this.loadargs.level+".NX":"/CHARTS/"+_this.loadargs.location+"/"+_this.loadargs.mode+"/"+_this.loadargs.level+".NX";
                    var NXFile      = this.GetFileArrayBuffer(NXFileName);
                    var AudioURL    = (_this.compcodec.audio.indexOf("mp3") > -1)?this.GetFileURL("/MUSIC.MP3", "audio/mpeg"):this.GetFileURL("/MUSIC.OGG", "audio/ogg");
                    var TitleIMG    = this.GetFileURL("/TITLE.JPG", "image/jpeg");
                    if(NXFile !== undefined && AudioURL !== undefined && TitleIMG !== undefined)    {
                        _this.soundfile = AudioURL;
                        _this.imagefile = TitleIMG;
                        _this._NX = PUMPER.NXParser(NXFile);
                        var gameCanvas = document.getElementById(_this.canvasname);
                        PUMPER.Globals.PumpGame = new PUMPER.Game({"notedata" : _this._NX, "musicfile" : _this.soundfile, "canvas": gameCanvas, "stats" : _this.gamestats});
                        PUMPER.Globals.PumpGame.AddBackground(_this.imagefile);
                        _this.Animate();                  
                        if(_this.loadargs.autoplay)
                            setTimeout(function() { PUMPER.Globals.PumpGame.Play(); }, 1000);
                    }else
                        alert("Fail to load step - Not valid step file!");
                    
                };
                this.jpakloader.onprogress = function(progress)    {
                    _this.HasProgress = true;
                    _this.LoadedPercent = progress.percent;
                    _this.LoadedBytes = progress.loaded;
                    _this.TotalBytes = progress.total;
                    _this.DrawLoading();
                };
                this.jpakloader.Load();            
            }
            break;
        case PUMPER.TypeSSC:
            if(this.loadargs["sscfile"] !== undefined && this.loadargs["level"] !== undefined) {
                //this.soundfile = (this.compcodec.audio.indexOf("mp3") > -1)?"ucs/mp3/"+this.loadargs.songid+".mp3":"ucs/ogg/"+this.loadargs.songid+".ogg";
                this.soundfile = "ssc/ogg/"+this.loadargs.sscname+".ogg";
                this.imagefile = "ssc/img/"+this.loadargs.sscname+".png";
                $.ajax({
                    url : _this.loadargs.sscfile,
                    dataType: "text",
                        success : function (data) {
                            console.log("LOADED");
                            _this._SSC = PUMPER.SSCParser(data);
                            var gameCanvas = document.getElementById(_this.canvasname);
                            PUMPER.Globals.PumpGame = new PUMPER.Game({"notedata" : _this._SSC, "musicfile" : _this.soundfile, "canvas": gameCanvas, "stats" : _this.gamestats});
                            PUMPER.Globals.PumpGame.AddBackground(_this.imagefile);
                            _this.Animate();
                        }
                });
            }else
                PUMPER.debug("No UCS file specifed!");               
            break;
        case PUMPER.TypeSM:
        case PUMPER.TypeSMA:
            PUMPER.debug("Not implemented!");
            break;
    
        default:    
            PUMPER.debug("Invalid load type! Cannot load :(");
    }
};

PUMPER.GameLoader.prototype.Animate =   function()  {
    PUMPER.Globals.HighSpeedAnimation = this.HighSpeedAnimation;
    PUMPER.GameLoaderAnimate();
};
PUMPER.GameLoader.prototype.DrawLoading = function()    {
    this.ctx.font = "bold 56px sans-serif";
    this.ctx.textAlign = 'center';
    this.ctx.clearRect(0,0,640,480);
    this.ctx.fillStyle = "rgb(255, 255, 255)";
    if(this.HasProgress)    {
        this.ctx.fillText("Loaded: "+this.LoadedPercent+"%", 320, 260);
        this.ctx.fillText(((this.LoadedBytes/1024)>>0)+"/"+((this.TotalBytes/1024)>>0)+" KB", 320, 320);    
    }else{
        this.ctx.fillText("Loading", 320, 240);
    }
    if(PUMPER.Globals.WebGL)    {
        this.gl.bindTexture(this.gl.TEXTURE_2D, this.loadobject.image);
        this.gl.pixelStorei(this.gl.UNPACK_FLIP_Y_WEBGL, true);
        this.gl.texImage2D(this.gl.TEXTURE_2D, 0, this.gl.RGBA, this.gl.RGBA, this.gl.UNSIGNED_BYTE, this.buffcnv);
        this.gl.texParameteri(this.gl.TEXTURE_2D, this.gl.TEXTURE_WRAP_S, this.gl.CLAMP_TO_EDGE);
        this.gl.texParameteri(this.gl.TEXTURE_2D, this.gl.TEXTURE_WRAP_T, this.gl.CLAMP_TO_EDGE);
        this.gl.texParameteri(this.gl.TEXTURE_2D, this.gl.TEXTURE_MIN_FILTER, this.gl.LINEAR);
        this.gl.texParameteri(this.gl.TEXTURE_2D, this.gl.TEXTURE_MAG_FILTER, this.gl.LINEAR);
        this.loadobject.GLUpdate();
        this.Renderer.RenderObject(this.loadobject);  
    }
};
PUMPER.GameLoaderAnimate = function()   {
    if( PUMPER.Globals.HighSpeedAnimation)
        PUMPER.HighSpeedRequest(PUMPER.GameLoaderAnimate);
    else
    	requestAnimFrame(PUMPER.GameLoaderAnimate);
	PUMPER.Globals.PumpGame.Looper.loop();

};
