PUMP_SELECTOR.OneTimeEvent  = PUMP_SELECTOR.OneTimeEvent  || function ( parameters )  {
    this.taskfunction = parameters.taskfunction;
    this.taskfunctionargs = parameters.taskfunctionargs;
    this.conditionfunction = parameters.conditionfunction;
    this.interval = parameters.interval || 100;
    var _this = this;
    //console.log("PUMP_SELECTOR::OneTimeEvent created.");
    this.__Task = function()    {
        if(_this.conditionfunction())   
            _this.taskfunction(_this.taskfunctionargs);
        else
            _this.timeout = setTimeout(_this.__Task, _this.interval);
    };
    this.timeout = setTimeout(this.__Task, this.interval);
};
