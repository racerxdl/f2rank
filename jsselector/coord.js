PUMP_SELECTOR.CoordSlicer = function( parameters ) {
    var image = parameters.image;
    var coord = parameters.coord;
    var name  = parameters.name;
    var obj = {};
    
    if( image === undefined)    {
        //PUMPER.debug("PUMPER::CoordSlicer() - Cannot slice image, image missing.");
        return undefined;
    }else if (coord === undefined) {
        //PUMPER.debug("PUMPER::CoordSlicer() - Warn: No coordinate data given. Returning image.");
        return obj[name] = obj;
    }else{
        var coords = coord.split("\n");
        coords.clean("");
        for(var i=0;i<coords.length;i++) {
            var coordinate = coords[i].split(" ");
            obj[coordinate[0]] = PUMP_SELECTOR.CropImage(image,coordinate[1],coordinate[2],coordinate[3],coordinate[4]);
        }
        return obj;
    }
};
