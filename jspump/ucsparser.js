
/************************* UCSParser **************************/
PUMPER.UCSData = {
   "." : PUMPER.NoteNull,
   "X" : PUMPER.NoteTap,
   "M" : PUMPER.NoteHoldHead,
   "H" : PUMPER.NoteHoldBody,
   "W" : PUMPER.NoteHoldTail 
};

PUMPER.UCSParser = PUMPER.UCSParser || function ( UCSText ) {
	var lines = UCSText.split('\n');
	var lastblock = -1;
	var inBlock = false;
	var beat = 0;
	var time = 0;
	var linen = 0;
	var lastbeat = 0;
    var UCSData = new PUMPER.StepData({});
    var lineslength = lines.length;
    var i = 0;
    var CurSplit;
    var CurBlock;
    var HeaderData = 0;
	var inblock = false;
	var LongTmp = [ {}, {}, {}, {}, {} ]
    while(i<lineslength)    {
        if(lines[i][0] == ":")  {
            if(!inblock)    {
                if(CurSplit != undefined)   {
                    CurSplit.LastBeat = beat;
                    CurSplit.EndTime = time;
                }
                CurSplit = new PUMPER.StepSplit( {} );
                CurSplit.StartTime = time;
                CurSplit.StartBeat = beat;
                HeaderData = 0;
                inblock = true;
            }
            command = lines[i].replace(":","").split('=');  
            switch(command[0])  {
                case "Format"   :   UCSData.Format = parseInt(command[1]);break;
                case "Mode"     :   UCSData.Mode   = command[1].trim();		break;
                case "BPM"      :   HeaderData++; CurSplit.BPM   = parseFloat(command[1].replace(",",".")); CurSplit.BPS = CurSplit.BPM/60.0; break;
                case "Delay"    :   HeaderData++; CurSplit.Delay = parseFloat(command[1].replace(",",".")) / 1000; break;
                case "Beat"     :   HeaderData++; break;  //  Ignore Beat
                case "Split"    :   HeaderData++; CurSplit.beatsplit = parseInt(command[1]); CurSplit.mysteryblock  = 1 / CurSplit.beatsplit; break;
                default         :   PUMPER.log("UCS: Command not know: "+command[0]+" with value "+command[1]); break;
            }     
            if(HeaderData == 4) {
                var bpmchange = { "BPM" : CurSplit.BPM, "Start" : time };
                UCSData.AddBPMChange(bpmchange);
                UCSData.AddSplit(CurSplit);
                //PUMPER.debug("UCS: Split header done! Adding Split.\n- BPM: "+CurSplit.BPM+"\n- Split: "+CurSplit.beatsplit);
                if(UCSData.currentbpm == 0) {
                    UCSData.currentbpm = CurSplit.BPM;
                    PUMPER.Globals.CurrentBPM = CurSplit.BPM;
                }
                time += CurSplit.Delay;
                beat += CurSplit.Delay * CurSplit.BPS
            }
        }else{
            inblock = false;
            var note = lines[i].split(""),
                lennote = note.length,
                n = 0,
                row = new PUMPER.StepRow({"rowbeat" : beat, "rowtime" : time}),
                addrow = false;
            while(n<lennote)    {
                if(note[n] != "\r") {
                    addrow |= (note[n]!=".");
                    if(note[n] in PUMPER.UCSData)
                        row.AddNote(new PUMPER.StepNote({"type" : PUMPER.UCSData[note[n]]}));
                    else{
                        PUMPER.log("Note type not know: "+note[n]+" code  "+note[n].charCodeAt(0));
                        row.AddNote(new PUMPER.StepNote({"type" : PUMPER.UCSData["."]}));
                    }
			    }
                ++n;
            }
            beat +=  1.0 / CurSplit.beatsplit;
			time +=  1.0 / (CurSplit.beatsplit * CurSplit.BPS);
            if(addrow)
                CurSplit.AddRow(0, row);
        } 
        ++i;       
    }
    UCSData.GenerateCacheData();
    if(UCSData.Mode == "D-Performance")
    	UCSData.Mode = "Double";
    if(UCSData.Mode == "S-Performance")
    	UCSData.Mode = "Single";
	return UCSData;
};

