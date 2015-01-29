<?php

class F2Save    {

    public $lensave = 236616;
    public $lenrank = 65560;
    
    /*  Save fields   */
    public $playerid;
    public $region;
    public $avatarid;
    public $level;
    public $calories;
    public $vo2;
    public $steps;
    public $games;
    public $exp;
    public $score;
    public $missions;
    public $coop;
    public $battlewins;
    public $battleloses;
    public $battledraws;
    
    public $version;
    public $cpu;
    public $motherboard;
    public $gfxcard;
    public $hdd;
    public $totalram;
    public $haspkey;
    
    public $highscores = array();
    
    /*  Functions   */
    function __construct($savefile,$rankfile)   {
        //  Load
        $this->bufsave = file_get_contents($savefile);
        $this->bufrank = file_get_contents($rankfile);
        
        // decrypt both files
        $this->decrypt_save();
        $this->decrypt_rank();  

        if($this->CheckSave())  {
            $this->playerid     =   explode("\x00",$this->ssave(0x08,0x0C))[0];
            $this->region       =   unpack("V",$this->ssave(0x14,0x04))[1];
            $this->avatarid     =   unpack("V",$this->ssave(0x18,0x04))[1];
            $this->level        =   unpack("V",$this->ssave(0x1C,0x04))[1];
            $this->calories     =   unpack("f",$this->ssave(0x20,0x04))[1];
            $this->vo2          =   unpack("f",$this->ssave(0x24,0x04))[1];
            $this->steps        =   unpack("I",$this->ssave(0x28,0x08))[1];
            $this->games        =   unpack("I",$this->ssave(0x30,0x08))[1];
            $this->missions     =   unpack("V",$this->ssave(0x48,0x04))[1];
            $this->coop         =   unpack("V",$this->ssave(0x4C,0x04))[1];
            $this->battlewins   =   unpack("V",$this->ssave(0x50,0x04))[1];
            $this->battleloses  =   unpack("V",$this->ssave(0x54,0x04))[1];
            $this->battledraws  =   unpack("V",$this->ssave(0x58,0x04))[1];
            
            $this->version      =   explode("\x00",$this->ssave(0x120,0x08))[0];
            $this->cpu          =   explode("\x00",$this->ssave(0x128,0x80))[0];
            $this->motherboard  =   explode("\x00",$this->ssave(0x1A8,0x80))[0];
            $this->gfxcard      =   explode("\x00",$this->ssave(0x228,0x80))[0];
            $this->hdd          =   explode("\x00",$this->ssave(0x2A8,0x80))[0];
            $this->totalram     =   unpack("V",$this->ssave(0x2C8,0x04))[1];
            $this->haspkey      =   unpack("V",$this->ssave(0x2CC,0x04))[1];
            
            $this->exp1         =   unpack("V",$this->ssave(0x38,0x04))[1];
            $this->exp2         =   unpack("V",$this->ssave(0x3C,0x04))[1];
            $this->sco1         =   unpack("V",$this->ssave(0x40,0x04))[1];
            $this->sco2         =   unpack("V",$this->ssave(0x44,0x04))[1];
            $this->steps1       =   unpack("V",$this->ssave(0x28,0x04))[1];
            $this->steps2       =   unpack("V",$this->ssave(0x2C,0x04))[1];
            $this->games1       =   unpack("V",$this->ssave(0x30,0x04))[1];
            $this->games2       =   unpack("V",$this->ssave(0x34,0x04))[1];
            
            
            $this->exp          =   ($this->exp2*0xFFFFFFFF)+$this->exp1;
            $this->score        =   ($this->sco2*0xFFFFFFFF)+$this->sco1;
            $this->games        =   ($this->games2*0xFFFFFFFF)+$this->games1;
            $this->steps        =   ($this->steps2*0xFFFFFFFF)+$this->steps1;

            $offset             =   0x25970;
            while($offset < 0x39C34)    {
                $count          =   unpack("V",$this->ssave($offset+0x0C,0x04))[1];
                if($count > 0)  {
                    $songid         =   unpack("V",$this->ssave($offset+0x00,0x04))[1];
                    $difficult      =   unpack("C",$this->ssave($offset+0x04,0x01))[1];
                    $mode           =   unpack("C",$this->ssave($offset+0x05,0x01))[1];
                    $grade          =   unpack("C",$this->ssave($offset+0x06,0x01))[1];
                    $score          =   unpack("V",$this->ssave($offset+0x08,0x04))[1];
                    $exp            =   unpack("V",$this->ssave($offset+0x10,0x04))[1];
                    $this->highscores[] =   new F2Score($songid,$difficult,$mode,$grade,$score,$count,$exp);
                }
                $offset += 0x14;
            }
        }
    }

    function ToArray()  {
        $data = array(
                "playerid"      =>  $this->playerid,
                "level"         =>  $this->level,
                "avatarid"      =>  $this->avatarid,
                "region"        =>  $this->region,
                "calories"      =>  $this->calories,
                "vo2"           =>  $this->vo2,
                "steps"         =>  $this->steps,
                "credits"       =>  $this->games,
                "exp"           =>  $this->exp,
                "score"         =>  $this->score,
                "missions"      =>  $this->missions,
                "coop"          =>  $this->coop,
                "battle"        =>  array(
                        "wins"      =>  $this->battlewins,
                        "loses"     =>  $this->battleloses,
                        "draws"     =>  $this->battledraws
                ),
                "machine"       =>  array(
                        "version"       =>  $this->version,
                        "cpu"           =>  $this->cpu,
                        "motherboard"   =>  $this->motherboard,
                        "graphicscard"  =>  $this->gfxcard,
                        "hdd"           =>  $this->hdd,
                        "totalram"      =>  $this->totalram
                ),
                "highscores"    =>  array()
        );

        foreach($this->highscores as $score)    
            $data["highscores"][] = $score->ToArray();
        
        return $data;
    }
        
    function ToString() {
        $out = "";
        $out .= "PlayerID: ".$this->playerid." Avatar: ".$this->avatar." Level: ".$this->level."<BR>\n";
        $out .= "Game Version: ".$this->version."<BR>\n";
        $out .= "   CPU: ".$this->cpu."<BR>\n";
        $out .= "   Motherboard: ".$this->motherboard."<BR>\n";
        $out .= "   GFX: ".$this->gfx."<BR>\n";
        $out .= "   HDD: ".$this->hdd."<BR>\n";
        $out .= "   RAM: ".$this->totalram."<BR>\n";
        
        foreach($this->highscores as $score)    
            $out .= $score->ToString()."<BR>\n";
        return $out;
    }
    
    static function adler32($adler, &$buf, $offset, $len) {
        $sum2 = ($adler >> 16) & 0xffff;
        $adler &= 0xffff;
    
        for($i = 0; $i < $len; ++$i) {
            $adler = ($adler + ord($buf[$offset + $i])) % 65521;
            $sum2 = ($sum2 + $adler) % 65521;
        }
    
        return ($sum2 << 16) | $adler;
    }
    
    
    function CheckSave() {
        $saveadlerstr = substr($this->bufsave, 0, 4);
        $saveadler = unpack("V", $saveadlerstr)[1];
        $rankadlerstr = substr($this->bufrank, 0, 4);
        $rankadler = unpack("V", $rankadlerstr)[1];
        $adlerseedsavestr = substr($this->bufsave, 4, 4);
        $adlerseedsave = unpack("V", $adlerseedsavestr)[1];
        $adlerseedrankstr = substr($this->bufrank, 16, 4);
        $adlerseedrank = unpack("V", $adlerseedrankstr)[1];
        $chksaveadler = F2Save::adler32($adlerseedsave, $this->bufsave, 4, $this->lensave - 4);
        $chkrankadler = F2Save::adler32($adlerseedrank, $this->bufrank, 4, $this->lenrank - 4);

        return ($saveadler == $chksaveadler) && ($rankadler == $chkrankadler);
    }
    
    
    function ssave($start,$length)  {
        return substr($this->bufsave,$start,$length);
    }

    
    function decrypt_rank() {
        $seed = 0xEBADA1;
        for($i = 0; $i < $this->lenrank; $i++) {
            $bufsmall = ord($this->bufrank[$i]);
            $this->bufrank[$i] = $this->bufrank[$i] ^ chr($seed >> 8);
            $seed = (0x68993 * ($bufsmall + $seed) + 0x4FDCF) & 0xFFFFFFFF;
        }
    }
    
    function decrypt_save() {
        $seed = 0xEBADA1;
        for($i = 0; $i < $this->lensave; $i++) {
            $bufsmall = ord($this->bufsave[$i]);
            $this->bufsave[$i] = $this->bufsave[$i] ^ chr($seed >> 8);
            $seed = (0x68993 * ($bufsmall + $seed) + 0x4FDCF) & 0xFFFFFFFF;
        }
    }
}