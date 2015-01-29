<?php

/*
Fiesta 2 Unnoficial Ranking
Copyright (C) 2014  HUEBR's Team

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

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