/************************* Looper **************************/
PUMPER.Looper = PUMPER.Looper || function ( parameters )  {
    this.Drawer     = parameters.drawer;
    this.NoteData   = parameters.notedata;
    this.Skin       = parameters.skin; 
};

PUMPER.Looper.prototype.loop = function()  {
    PUMPER.Globals.Sentinel.Update();
    if(this.Drawer !== undefined)   { 
        this.Drawer.Update();
        if(PUMPER.Globals.DataToLoad > PUMPER.Globals.LoadedData || !PUMPER.Globals.LoadStarted) {
            PUMPER.Globals.Loaded = false;
            this.Drawer.DrawLoading();
        }else{
            if(PUMPER.Globals.Sentinel.OK())   {
                PUMPER.Globals.Loaded = true;
                this.NoteData.Update(PUMPER.Globals.Music.GetTime());
                this.Skin.Update(PUMPER.Globals.Music.GetTime());
                this.Drawer.NoteBlock = this.NoteData.GetBeatBlock(PUMPER.ScreenHeight);
                this.Drawer.DrawLayers();
                $("#time").html(PUMPER.Globals.Music.GetTime());
                $("#beat").html(PUMPER.Globals.NoteData.CurrentBeat);
            }
        }
    }
    PUMPER.UpdateInfoHead();
    if(PUMPER.Globals.FPSStats != undefined)
        PUMPER.Globals.FPSStats.update();
};

