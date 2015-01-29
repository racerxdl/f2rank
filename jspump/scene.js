/*********************** SceneLayer ************************/
PUMPER.SceneLayer = PUMPER.SceneLayer || function ( parameters )    {
    this.Objects = [];
    this.width = parameters.width || 640;
    this.height = parameters.height || 480;
    this.blendtype = parameters.blendtype || "source-over";
};
PUMPER.SceneLayer.prototype.InitLayer       =   function ( )        {
    this.canvas = document.createElement('canvas');
    this.canvas.width = this.width;
    this.canvas.height = this.height;
    this.ctx = this.canvas.getContext('2d');
    this.ForceRedraw = false;
};
PUMPER.SceneLayer.prototype.AddObject       =   function ( obj )    {
    this.Objects.push(obj);
};
PUMPER.SceneLayer.prototype.RemoveObject    =   function ( objname ) {
    var i           =   0, 
        len         =   this.Objects.length;
    while(i<len)    {
        if(this.Objects[i].id == objname)   {
            this.Objects.slice(i,1);
            break;
        }
        ++i;
    }
};
PUMPER.SceneLayer.prototype.NeedsUpdate     =   function ( )    {
    if(this.ForceRedraw)    
        return true;
    else{
        var i           =   0, 
            len         =   this.Objects.length,
            need        =   false;
        while(i<len)    {
            need |= this.Objects[i].NeedsRedraw;
            if(need) break;
            ++i;
        }
        return need;
    }
}; 

PUMPER.SceneLayer.prototype.GetCanvas       =   function ( )    { return this.canvas; };
PUMPER.SceneLayer.prototype.GetContext      =   function ( )    { return this.ctx };
PUMPER.SceneLayer.prototype.Update          =   function ( timeDelta )    {
    var i           =   0, 
        len         =   this.Objects.length;
    while(i<len)    {
        this.Objects[i].Update(timeDelta);
        ++i;       
    }
};
PUMPER.SceneLayer.prototype.UpdateCanvas    =   function ( )    {
    var i           =   0, 
        len         =   this.Objects.length;
    if(this.NeedsUpdate())  {
        this.ctx.clearRect(0,0,this.width,this.height);
        while(i<len)    {
            this.Objects[i].Draw(this.ctx);
            ++i;       
        }
    }
};

PUMPER.SceneLayer.prototype.ClearCanvas     =   function ( )    {
    this.ctx.clearRect(0,0,this.width,this.height);
};
