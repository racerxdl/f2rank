var PUMPER = function() {};
PUMPER.Version = 1.1;
if (!String.prototype.trim) {
    String.prototype.trim=function(){return this.replace(/^\s+|\s+$/g, '');};

    String.prototype.ltrim=function(){return this.replace(/^\s+/,'');};

    String.prototype.rtrim=function(){return this.replace(/\s+$/,'');};

    String.prototype.fulltrim=function(){return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');};
};

PUMPER.Globals = {
    DrawAnchors     :   false,
    Drawer          :   undefined,
    Looper          :   undefined,
    Skin            :   undefined,
    CurrentBPM      :   0,
    CurrentCombo    :   0,
    EnableSound     :   true,
    EnableVideo     :   false,
    Double          :   false,
    EffectBlock     :   [],
    DisableLog      :   false,
    EnableDebug     :   true,
    ObjectsLoaded   :   0,
    ObjectsToLoad   :   0,
    AllLoaded       :   false,
    CheckLoaded     :   function()  {
        if(PUMPER.Globals.ObjectsLoaded == PUMPER.Globals.ObjectsToLoad)    
            PUMPER.Globals.AllLoaded = true;
        return PUMPER.Globals.AllLoaded;
    },
    WebGL           :   false,
    glExperimental  :   false
};

function hasWebGL() {
    var canvas = document.createElement('canvas');
    try { gl = canvas.getContext("webgl", { failIfMajorPerformanceCaveat : true }); }catch (x) { gl = null; }
    if (gl === null) {try { gl = canvas.getContext("experimental-webgl", { failIfMajorPerformanceCaveat : true }); PUMPER.Globals.glExperimental = true; }catch (x) { gl = null; }}
    if(gl) 
        return true; 
    else
        return false; 
}

PUMPER.Globals.WebGL = hasWebGL();
//PUMPER.Globals.WebGL = false; // Disabled for now
PUMPER.log    =   function(msg)   {
    if($("#pumperlog") !== undefined)	{
    	$("#pumperlog").html($("#pumperlog").html()+"<span class=\"logstyle\">PUMPER(log):></span><span class=\"logstyle_content\">"+msg+"</span><BR>");
    	$("#pumperlog").stop( true, true ).animate({ scrollTop: $('#pumperlog')[0].scrollHeight}, 100);
    }
    if(!PUMPER.Globals.DisableLog)
        console.log("PUMPER(log):> "+msg);
};
PUMPER.debug  =   function(msg)   {
    if($("#pumperlog") !== undefined)	{
    	$("#pumperlog").html($("#pumperlog").html()+"<span class=\"debugstyle\">PUMPER(debug):></span><span class=\"debugstyle_content\">"+msg+"</span><BR>");
    	$("#pumperlog").stop( true, true ).animate({ scrollTop: $('#pumperlog')[0].scrollHeight}, 100);
	}
    if(PUMPER.Globals.EnableDebug)
        console.log("PUMPER(debug):> "+msg);
};
PUMPER.FixTo3	=	function(val)	{
	return PUMPER.Pad(val.toFixed(3),7);
};
PUMPER.Pad		=	function(num, size) {
    var s = "000000000" + num;
    return s.substr(s.length-size);
};
PUMPER.UpdateInfoHead	=	function()	{
	$("#infohead").html("BPM: "+PUMPER.Globals.CurrentBPM+" BEAT "+PUMPER.FixTo3(PUMPER.Globals.Game.notedata.CurrentBeat)+" TIME: "+PUMPER.FixTo3(PUMPER.Globals.Music.GetTime())+" BLOCK: "+PUMPER.Pad(PUMPER.Globals.Game.notedata.CurrentBPMChange,3)+" SPEED: "+PUMPER.ScrollSpeed+"x");
};

PUMPER.IncreaseSpeed	=	function()	{
	if(PUMPER.ScrollSpeed < 10)	{
		PUMPER.log("Changed ScrollSpeed from "+PUMPER.ScrollSpeed+"x to "+(PUMPER.ScrollSpeed+1)+"x");
		PUMPER.ScrollSpeed+=0.5;
	}
};

PUMPER.DecreaseSpeed	=	function()	{
	if(PUMPER.ScrollSpeed > 0.5)	{
		PUMPER.log("Changed ScrollSpeed from "+PUMPER.ScrollSpeed+"x to "+(PUMPER.ScrollSpeed-1)+"x");
		PUMPER.ScrollSpeed-=0.5;
	}
};

//  Skin Properties
PUMPER.ArrowSize                = 64;
PUMPER.ShowWidth                = 50;
PUMPER.OffsetY                  = 32;

//  Game Properties
PUMPER.ScrollSpeed              = 3;

//  Note Types
PUMPER.NoteNull                 = 0;
PUMPER.NoteTap                  = 1;
PUMPER.NoteHoldHead             = 2;
PUMPER.NoteHoldBody             = 3;
PUMPER.NoteHoldTail             = 4;
PUMPER.NoteFake                 = 5;
PUMPER.NoteItem                 = 6;
PUMPER.NoteEffect               = 7;
PUMPER.NoteItemFake             = 8;
PUMPER.NoteHoldHeadFake         = 9;
PUMPER.NoteHoldBodyFake         = 10;
PUMPER.NoteHoldTailFake         = 11;

//Note Seed
PUMPER.NoteSeedAction           = 0;
PUMPER.NoteSeedShield           = 1;
PUMPER.NoteSeedChange           = 2; 
PUMPER.NoteSeedAcceleration     = 3; 
PUMPER.NoteSeedFlash            = 4; 
PUMPER.NoteSeedMineTap          = 5; 
PUMPER.NoteSeedMineHold         = 6; 
PUMPER.NoteSeedAttack           = 7;
PUMPER.NoteSeedDrain            = 8;
PUMPER.NoteSeedHeart            = 9; 
PUMPER.NoteSeedSpeed2           = 10;
PUMPER.NoteSeedRandom           = 11; 
PUMPER.NoteSeedSpeed3           = 12;
PUMPER.NoteSeedSpeed4           = 13; 
PUMPER.NoteSeedSpeed8           = 14; 
PUMPER.NoteSeedSpeed1           = 15; 
PUMPER.NoteSeedPotion           = 16; 
PUMPER.NoteSeedRotate0          = 17; 
PUMPER.NoteSeedRotate90         = 18; 
PUMPER.NoteSeedRotate180        = 19; 
PUMPER.NoteSeedRotate270        = 20; 
PUMPER.NoteSeedSpeed_           = 21; 
PUMPER.NoteSeedBomb             = 22; 
PUMPER.NoteSeedHyperPotion      = 23; 

//Modifier Type
PUMPER.ModNonStep         =   0;
PUMPER.ModFreedom         =   1;
PUMPER.ModVanish          =   2;
PUMPER.ModAppear          =   3;
PUMPER.ModHighJudge       =   4;
PUMPER.ModStandBreak      =   5;


PUMPER.cloneCanvas = function ( canvas ) {
    var newCanvas = document.createElement('canvas');
    newCanvas.width = canvas.width;
    newCanvas.height = canvas.height;
    context.drawImage(canvas, 0, 0);
    var context = newCanvas.getContext('2d');
    return newCanvas;
}
PUMPER.createBuffCanvas = function ( width, height)   {
    var canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    return canvas;   
}
PUMPER.CropImage = function(image,x,y,width,height)   {
      var canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;
      var context = canvas.getContext('2d');
      context.drawImage(image,x,y,width,height,0,0,width,height);
      return canvas;
}
PUMPER.CropImageTarget = function(image,x,y,width,height,targetx,targety,targetw,targeth)   {
      var canvas = document.createElement('canvas');
      canvas.width = targetw;
      canvas.height = targeth;
      var context = canvas.getContext('2d');
      context.drawImage(image,x,y,width,height,targetx,targety,width,height);
      return canvas;
}
PUMPER.ResizeImage = function(image,width,height)   {
      var canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;
      var context = canvas.getContext('2d');
      context.drawImage(image,0,0,image.width,image.height,0,0,width,height);
      return canvas;
}
PUMPER.PadInt = function (num, size) {
    var s = "000000000000" + num;
    return s.substr(s.length-size);
}
PUMPER.GetCompatibleCodecs = function()  {
    var testEl = document.createElement( "video" ), testEl2 = document.createElement( "audio" ),
    video=[],audio=[];
    if ( testEl.canPlayType ) {
        // Check for MPEG-4 support
        if("" !== testEl.canPlayType( 'video/mp4; codecs="mp4v.20.8"' ))
            video.push("mpeg4");

        // Check for h264 support
        if("" !== ( testEl.canPlayType( 'video/mp4; codecs="avc1.42E01E"' )|| testEl.canPlayType( 'video/mp4; codecs="avc1.42E01E, mp4a.40.2"' ) ))
            video.push("h264");

        // Check for Ogg support
        if("" !== testEl.canPlayType( 'video/ogg; codecs="theora"' ))
            video.push("ogg");

        // Check for Webm support
        if("" !== testEl.canPlayType( 'video/webm; codecs="vp8, vorbis"' ))
            video.push("webm");
        
    }
    if (testEl2.canPlayType)    {
        if("" !== testEl2.canPlayType('audio/mpeg'))
            audio.push("mp3");
        if("" !== testEl2.canPlayType('audio/ogg'))
            audio.push("ogg");
    }
    return { "video": video, "audio": audio };
}

PUMPER.parseHashes = function () {
	HashConfig = { "UCS" : undefined, "Speed" : 2};
	hashes = window.location.hash.split("#");
	hashes = hashes.filter(function(n){return n; });
	for(var hashn in hashes)		{
	    if (hashes.hasOwnProperty(hashn)) {
		    breaked = hashes[hashn].split("=");
		    HashConfig[breaked[0]] = breaked[1];
	    }
	}
	HashConfig.Speed = parseFloat(HashConfig.scrollSpeed);
	if(isNaN(HashConfig.scrollSpeed))	
		HashConfig.scrollSpeed = 2;
	return HashConfig;
};

PUMPER.HighSpeedRequest = (function(){
	  return  function( callback ){
			    window.setTimeout(callback, 1000 / 240);
			  };
	})();

