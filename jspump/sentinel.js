//	Here the sentinel is disabled

PUMPER.Serialize = function(obj) {
  var str = [];
  for(var p in obj)
     str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
  return str.join("&");
};

PUMPER.Sentinel = function()    {};
PUMPER.Sentinel.prototype.OK           =    function()  { return true;};

PUMPER.Sentinel.prototype.Update        =   function() {};

PUMPER.Sentinel.prototype.InitSession   =   function()  {};
PUMPER.Sentinel.prototype.GetHash       =   function()  {};
