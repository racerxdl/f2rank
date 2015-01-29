<?php

class F2Score   {
    
    public $songid;
    public $difficult;
    public $mode;
    public $grade;
    public $score;
    public $count;
    public $exp;
    
    function __construct($songid,$difficult,$mode,$grade,$score,$count,$exp)    {
        $this->songid       =   $songid;
        $this->difficult    =   $difficult;
        $this->mode         =   $mode;
        $this->grade        =   $grade;
        $this->score        =   $score;
        $this->count        =   $count;
        $this->exp          =   $exp;
    }
    
    function ToString() {
        return "SongID: ".dechex($this->songid)." Difficult: ".$this->difficult." Mode: ".$this->mode." Grade: ".$this->grade." Score: ".$this->score." Count: ".$this->count." EXP: ".$this->exp;
    }
    function ToArray()  {
        return array(
                "songid"    =>  $this->songid,
                "level"     =>  $this->level,
                "mode"      =>  $this->mode,
                "grade"     =>  $this->grade,
                "score"     =>  $this->score,
                "count"     =>  $this->count,
                "exp"       =>  $this->exp
        );
    }
}