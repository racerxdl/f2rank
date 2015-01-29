<?php

class F2Gen {
    
    public static $BallCoordinate   =   array(
        "S"     =>  array(
            "x" =>  0,
            "y" =>  0,
            "w" =>  114,
            "h" =>  114
            ),
        "SP"    =>  array(
            "x" =>  114,
            "y" =>  0,
            "w" =>  114,
            "h" =>  114
            ),
        "D" =>  array(
            "x" =>  0,
            "y" =>  114,
            "w" =>  114,
            "h" =>  114
            ),
        "DP"    =>  array(
            "x" =>  114,
            "y" =>  114,
            "w" =>  114,
            "h" =>  114
            ),
        
        );
    
    public static $FontCoordinate   =   array(
        0   =>  array( 
            "x" =>  0,
            "y" =>  0,
            "w" =>  39,
            "h" =>  39
            ),
        1   =>  array(
            "x" =>  39,
            "y" =>  0,
            "w" =>  39,
            "h" =>  39
            ),
        2   =>  array(
            "x" =>  78,
            "y" =>  0,
            "w" =>  39,
            "h" =>  39
            ),
        3   =>  array(
            "x" =>  117,
            "y" =>  0,
            "w" =>  39,
            "h" =>  39
            ),
        4   =>  array(
            "x" =>  156,
            "y" =>  0,
            "w" =>  39,
            "h" =>  39
            ),
        "!" =>  array(
            "x" =>  195,
            "y" =>  0,
            "w" =>  39,
            "h" =>  39
            ),      
        5   =>  array( 
            "x" =>  0,
            "y" =>  39,
            "w" =>  39,
            "h" =>  39
            ),
        6   =>  array(
            "x" =>  39,
            "y" =>  39,
            "w" =>  39,
            "h" =>  39
            ),
        7   =>  array(
            "x" =>  78,
            "y" =>  39,
            "w" =>  39,
            "h" =>  39
            ),
        8   =>  array(
            "x" =>  117,
            "y" =>  39,
            "w" =>  39,
            "h" =>  39
            ),
        9   =>  array(
            "x" =>  156,
            "y" =>  39,
            "w" =>  39,
            "h" =>  39
            ),
        "?" =>  array(
            "x" =>  195,
            "y" =>  39,
            "w" =>  39,
            "h" =>  39
            )
        );

    public static $TextOffset = array( "x" => 20, "y" => 44);

    function __construct($imgfolder)    {
        $this->imgfolder    =   $imgfolder;
        $this->Balls        =   F2Gen::LoadPNG($imgfolder."/DG_03_EN.PNG");
        $this->Font         =   F2Gen::LoadPNG($imgfolder."/FB_02.PNG");
    }

    static function LoadPNG($imgname)
    {
        $im = @imagecreatefrompng($imgname); /* Attempt to open */
        if (!$im) { /* See if it failed */
            $im  = imagecreatetruecolor(114, 114); /* Create a blank image */
            $bgc = imagecolorallocatealpha($im, 255, 255, 255,0);
            $tc  = imagecolorallocate($im, 0, 0, 0);
            imagefilledrectangle($im, 0, 0, 114, 114, $bgc);
            /* Output an errmsg */
            imagestring($im, 1, 5, 5, "Error loading $imgname", $tc);
        }
        return $im;
    }
    function CheckTitle($songid)    {
        return file_exists($this->imgfolder."/titles/".$songid.".jpg");
    }
    function GenerateDummy()    {
        return $this->GenerateBall("DP","!!");
    }

    function GenerateBall($mode,$level) {
        $level = (string) $level;
        if($level == 50)
            $level = "??";
        if($level < 0)
            $level = "!!";
        $level = str_pad($level,2,'0', STR_PAD_LEFT);
        
        $img = imagecreatetruecolor ( 114 , 114 );
        imagealphablending( $img, false );
        imagesavealpha($img, true);
        $bgc = imagecolortransparent($img);
        $tc  = imagecolorallocate($img, 0, 0, 0);
        
        imagefilledrectangle($img, 0, 0, 114, 114, $bgc);
        if(array_key_exists($mode,self::$BallCoordinate))   {
            $ball = self::$BallCoordinate[$mode];
            imagecopy ( $img , $this->Balls, 0, 0, $ball["x"], $ball["y"] , $ball["w"] , $ball["h"] );
            
            imagealphablending( $img, true );   
            for($i=0;$i<strlen($level);$i++)    {
                imagecopy($img, $this->Font, $i*39 + self::$TextOffset["x"], self::$TextOffset["y"], self::$FontCoordinate[$level[$i]]["x"], self::$FontCoordinate[$level[$i]]["y"], self::$FontCoordinate[$level[$i]]["w"], self::$FontCoordinate[$level[$i]]["h"]);
            }
        }
        imagealphablending( $img, false );
        imagesavealpha($img, true);
        return $img;
    }

}