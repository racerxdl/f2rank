
/************************* SSCParser **************************/
PUMPER.SSCData = {
   "0" : PUMPER.NoteNull,
   "1" : PUMPER.NoteTap,
   "2" : PUMPER.NoteHoldHead,
   //"H" : PUMPER.NoteHoldBody,
   "3" : PUMPER.NoteHoldTail 
};

PUMPER.SSCTools = {};
PUMPER.SSCTools.CleanLines = function(data)       {
    if(data instanceof Array)   {
        for(var n in data)
            if(data.hasOwnProperty(n))
                data[n] = data[n].trim();
        return data.filter(function(n){ return n != '' });
    }else
        return data.trim();
}

PUMPER.SSCTools.LineObject = function(data)  {
    if(data instanceof Array)   {
        for(var n in data)  {
            if(data.hasOwnProperty(n))
                data[n] = PUMPER.SSCTools.LineObject(data[n]);
        }
        return data.filter(function(n){ return n != '' });
    }else
        return PUMPER.SSCTools.CleanLines(data.split("\n"));
}

PUMPER.SSCTools.ParseSSCLines = function(data)    {
    var reg = /\#([\s\S]*?)\;/g;
    var songdata = {
        "charts" : []
    };
    var chart = -1;
    
    var match;
    while (match = reg.exec(data))  {
        var variable    =   match[1].split(":",1)[0];
        var content     =   match[1].replace(variable+":","");
        variable        =   variable.trim();
        if(variable == "NOTEDATA")  {
            chart += 1;
            songdata["charts"][chart] = {};
        }else{
            if(chart > -1)  {   //  Looking for chart data
                switch(variable)    {
                    case        "NOTES"             :   songdata["charts"][chart][variable]     =   PUMPER.SSCTools.LineObject(content.split(",")); break;
                    case        "BPMS"              :
                    case        "STOPS"             :
                    case        "DELAYS"            :
                    case        "WARPS"             :
                    case        "TIMESIGNATURES"    :
                    case        "TICKCOUNTS"        :
                    case        "COMBOS"            :
                    case        "SPEEDS"            :
                    case        "LABELS"            :
                    case        "SCROLLS"           :   songdata["charts"][chart][variable]     =   PUMPER.SSCTools.CleanLines(content.split(",")); break;
                    default                         :   songdata["charts"][chart][variable]     =   PUMPER.SSCTools.CleanLines(content); 
                }
            }else{              //  Looking for song data
                switch(variable)    {
                    case        "BPMS"              :
                    case        "STOPS"             :
                    case        "DELAYS"            :
                    case        "WARPS"             :
                    case        "TIMESIGNATURES"    :
                    case        "TICKCOUNTS"        :
                    case        "COMBOS"            :
                    case        "SPEEDS"            :
                    case        "LABELS"            :
                    case        "BGCHANGES"         :   
                    case        "SCROLLS"           :   songdata[variable]      =   PUMPER.SSCTools.CleanLines(content.split(",")); break;
                    default                         :   songdata[variable]      =   PUMPER.SSCTools.CleanLines(content);
                }
                
            }
        }
    }
    return songdata;
}

PUMPER.SSCParser = PUMPER.SSCParser || function ( SSCText ) {
    var songdata = PUMPER.SSCTools.ParseSSCLines(SSCText);
    var curchart = songdata.charts[5];
    console.log(songdata);
    
    var SSCData     =   new PUMPER.StepData({});
    var CurSplit    =   new PUMPER.StepSplit( {} );
    
    SSCData.Mode    =   curchart.STEPSTYPE.replace("pump-","");
    
    var BPM, BPS;
    var time = 0;
    var beat = 0;
    
    var beatblock = [];
    var basebeat = 0;
    function FindBeatTime(beat) {
        var n=0,nmax=beatblock.length;
        while(n<nmax) {
            if(beat >= beatblock[n].start && beat < beatblock[n].end)  {
                console.log(beatblock[n],beat,n,beat - beatblock[n].start,beatblock[n].bpm / 240.0);
                return beatblock[n].tstart + (beat - beatblock[n].start) * ( beatblock[n].bpm / (60.0*beatblock[n].bs) );
            }
            ++n;
        }
        return 999999;
    }
    
    var currbblock = {"tstart":0,"start":0,"end":-1,"bpm":0,"bs":4};
    
    BPM = parseFloat(curchart.BPMS[0].split("=")[1]);
    BPS = BPM/60.0;
    
    var CBPM    =   BPM;
    var CBPS    =   CBPM/60.0;
    
    //  Negative because its music offset not beat offset
    time        -=  parseFloat(curchart.OFFSET);
    beat        -=  parseFloat(curchart.OFFSET) * CBPS
    basebeat    =   beat;
    currbblock.bpm      =   BPM;

    var lastsize = 0;
    var row;
    for(var i=0;i<curchart.NOTES.length;i++)    {
        var cnote = curchart.NOTES[i];
        if(cnote.length != lastsize)    {
            if(i>0)     {
                SSCData.AddSplit(CurSplit);

                currbblock.end = beat;
                beatblock.push(currbblock);
                console.log("Saving block: ",currbblock);
                currbblock = {"tstart":time,"start":beat,"end":-1,"bs":cnote.length,"bpm":BPM};
            }
            CurSplit = new PUMPER.StepSplit( {} );
            CurSplit.BPM = BPM;
            CurSplit.BPS = BPS;
            CurSplit.beatsplit = cnote.length / 4.0;
            CurSplit.mysteryblock = 1.0 / cnote.length;
            lastsize = cnote.length;
        }
        
        for(var r=0;r<cnote.length;r++) {
            var note = cnote[r];
            var lnote = note.length;
            var n=0;
            var addrow = false;
            row = new PUMPER.StepRow({"rowbeat" : beat, "rowtime" : time});  
            while(n<lnote)    {
                addrow |= (note[n]!="0");
                if(note[n] in PUMPER.SSCData)
                    row.AddNote(new PUMPER.StepNote({"type" : PUMPER.SSCData[note[n]]}));
                else{
                    PUMPER.log("Note type not know: "+note[n]+" code  "+note[n].charCodeAt(0));
                    row.AddNote(new PUMPER.StepNote({"type" : PUMPER.SSCData["."]}));
                }
                ++n;
            }
            beat +=  1.0 / CurSplit.beatsplit;
			time +=  1.0 / (CurSplit.beatsplit * CurSplit.BPS);
            if(addrow)
                CurSplit.AddRow(0, row); 
        }  
    }
    currbblock.end = beat;
    beatblock.push(currbblock);
    console.log(beatblock);
    
    for(var i=0;i<curchart.BPMS.length;i++) {
        var d = curchart.BPMS[i].split("=");
        BPM = parseFloat(d[1]);
        BPM_TIME = parseFloat(d[0]);
        BPS = BPM/60.0;
        SSCData.AddBPMChange({ "BPM" : BPM, "Start" : FindBeatTime(BPM_TIME) });
        console.log("Added BPM Change to "+BPM+" in "+FindBeatTime(BPM_TIME),BPM_TIME);
    }
    
    for(var i=0;i<curchart.SCROLLS.length;i++)  {
    //{ "SF": speed, "Start": (steptime/1000) + PUMPER.SoundOffset / 1000, "Smooth" : (smoothspeed>0), "StartBeat": beat}
        var d = curchart.SCROLLS[i].split("=");
        var beat = parseFloat(d[0]);
        var speed = parseFloat(d[1]);
        //SSCData.AddScrollFactorChanges({ "SF": speed, "Start": FindBeatTime(beat) + PUMPER.SoundOffset / 1000, "Smooth" : true, "StartBeat": beat});
        SSCData.AddMysteryBlock({"Beat":beat+basebeat , "Ratio":speed/4, "BeatSplit" : 4});
        //console.log("Adding SF: ",{ "SF": speed, "Start": FindBeatTime(beat) + PUMPER.SoundOffset / 1000, "Smooth" : true, "StartBeat": beat});
    }   
    
    SSCData.GenerateCacheData();
	return SSCData;
};


