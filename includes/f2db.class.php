<?php


class F2DB  {
    
    private $addrank                    =   "INSERT INTO `ranking`(`userid`,`songid`,`difficult`,`mode`,`grade`,`score`,`count`,`date`) VALUES(?,?,?,?,?,?,?,NOW())";
    private $adduser                    =   "INSERT INTO `users`(`username`,`facebookid`,`password`,`name`,`email`) VALUES(?,?,UNHEX(SHA1(?)),?,?)";
    
    private $addplayer                  =   "INSERT INTO `drives` VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE `avatarid`=VALUES(`avatarid`), `level`=VALUES(`level`), `calories`=VALUES(`calories`), `vo2`=VALUES(`vo2`), `steps`=VALUES(`steps`), `games`=VALUES(`games`), `exp`=VALUES(`exp`), `score`=VALUES(`score`), `missions`=VALUES(`missions`), `coop`=VALUES(`coop`), `bwins`=VALUES(`bwins`), `bloses`=VALUES(`bloses`), `bdraws`=VALUES(`bdraws`),`version`=VALUES(`version`),`cpu`=VALUES(`cpu`),`motherboard`=VALUES(`motherboard`),`gfx`=VALUES(`gfx`),`hdd`=VALUES(`hdd`),`totalram`=VALUES(`totalram`),`haspkey`=VALUES(`haspkey`)";
    private $adduserprofile             =   "INSERT INTO `userprofiles`(`name`,`users_id`) VALUES(?,?)";
    private $adducs                     =   "INSERT INTO `usercustomstep`(`title`,`filename`,`level`,`mode`,`data`,`ucsid`,`users_id`,`date`) VALUES(?,?,?,?,?,?,?,NOW())";
    
    private $updateucs                  =   "UPDATE `usercustomstep` SET `level` = ?, `title` = ? WHERE `id` = ? AND `users_id` = ?";
    private $incucscount                =   "UPDATE `usercustomstep` SET `downloadcount` = `downloadcount`+1 WHERE `id` = ?";
    private $incucsvcount               =   "UPDATE `usercustomstep` SET `viewcount` = `viewcount`+1 WHERE `id` = ?";
    
    private $getuserdata                =   "SELECT * FROM `users` WHERE `facebookid` = ?";
    private $getuserdatasha             =   "SELECT * FROM `users` WHERE SHA1(`facebookid`) = ?";
    
    private $checkrankq                 =   "SELECT * FROM `ranking` WHERE `userid` = ? AND `songid` = ? AND `difficult` = ? AND `mode` = ? AND `score` >= ? ORDER BY `date` DESC";
    private $geturanks                  =   "SELECT  `entry`, `userid`,`songid` ,`difficult`,`mode`,`grade`,MAX(  `score` ) AS  `score`,`count`,`date` FROM  `ranking`    WHERE `userid` = ? GROUP BY  `songid`,`difficult`,`mode` ";
    private $getudata                   =   "SELECT * FROM `drives` WHERE `name` = ?";
    private $getusranks                 =   "SELECT * FROM `ranking` WHERE `userid` = ? AND `songid` = ? ORDER BY `score` DESC";
    
    private $getuserucs                 =   "SELECT `id`,`title`,`filename`,`level`,`mode`,`ucsid`,`users_id`,`date`,`downloadcount`,`viewcount` FROM `usercustomstep` WHERE `users_id` = ? ORDER BY `date` DESC";
    private $getucs                     =   "SELECT `usercustomstep`.*,`users`.`facebookid`,`users`.`username`,`users`.`name` FROM `usercustomstep` LEFT JOIN `users` ON `users`.`id` = `usercustomstep`.`users_id` HAVING `id` = ?";
    private $getallucs                  =   "SELECT `title`,`filename`,`level`,`mode`,`ucsid`,`users_id`,`date` FROM `usercustomstep` ORDER BY `date` DESC";
    private $getlastucs                 =   "SELECT * FROM `last_ucs`";
    private $getmostdownucs             =   "SELECT * FROM `most_downloaded_ucs`";
    private $getmostviewucs             =   "SELECT * FROM `most_viewed_ucs`";
    private $getfbid                    =   "SELECT `facebookid` FROM `users` WHERE `id` = ?";
    
    private $get_profile_avatarid       =   "SELECT `avatarid` FROM `drives` WHERE `name` = ? ";
    
    private $get_arcade_score_top100    =   "SELECT * FROM `arcade_score_top100`";
    private $get_arcade_exp_top100      =   "SELECT * FROM `arcade_exp_top100`";
    private $get_arcade_calories_top100 =   "SELECT * FROM `arcade_calories_top100`";
    private $get_arcade_missions_top100 =   "SELECT * FROM `arcade_missions_top100`";
    
    private $get_scoretop25_song        =   "SELECT `ranking`.* FROM  `ranking` JOIN (    SELECT  `userid` , MAX(  `score` ) AS  `score` ,  `songid`     FROM  `ranking`    WHERE  `songid` = ? AND `difficult` = ? AND `mode` = ?    GROUP BY  `userid` ) `rk2` ON `ranking`.`userid` = `rk2`.`userid` AND `ranking`.`score` = `rk2`.`score` WHERE  `ranking`.`songid` = ? AND `difficult` = ? AND `mode` = ? {BLACKLIST} ORDER BY `score` DESC LIMIT 25";
    private $get_userhighscore_song     =   "SELECT * FROM  `ranking` WHERE `songid` = ? AND `difficult` = ? AND `mode` = ? AND `userid` = ? ORDER BY `score` DESC LIMIT 1";
    private $get_songhighscore          =   "SELECT * FROM  `ranking` WHERE `songid` = ? AND `difficult` = ? AND `mode` = ? ORDER BY `score` DESC LIMIT 1";
    
    private $get_mostplayed_songs       =   "SELECT * FROM `most_played_songs`";
    private $get_mostplayed_charts      =   "SELECT * FROM `most_played_charts`";
    
    private $update_name                =   "UPDATE `users` SET `name` = ? WHERE `id` = ?";
    
    private $typemask = array(
        0   =>  "S",
        64  =>  "SP",
        128 =>  "D",
        192 =>  "DP"
    );
    private $grade  =   array(
        0   =>  "SS",
        1   =>  "Gold S",
        2   =>  "Silver S",
        3   =>  "A",
        4   =>  "B",
        5   =>  "C",
        6   =>  "D",
        7   =>  "F"
    );
    private $songmode = array(
        0   => "Censored",
        1   => "Shortcut",
        2   => "Normal",
        3   => "Remix",
        4   => "Full Song"
    );
    
    private $gamelist  = array(
            "0"  => "All Tunes",
            "1"  => "1st-3rd",      //  1st Dance Floor
            "2"  => "1st-3rd",      //  2nd Dance Floor
            "3"  => "1st-3rd",      //  3rd Dance Floor
            "4"  => "se-extra",     //  The O.B.G.
            "5"  => "se-extra",     //  Perfect Collection
            "6"  => "se-extra",     //  Extra
            "7"  => "rebirth-prex3",//  Rebirth
            "8"  => "rebirth-prex3",//  The Premiere 2
            "9"  => "rebirth-prex3",//  The Prex 2
            "10" => "rebirth-prex3",//  The Premiere 3
            "11" => "rebirth-prex3",//  Prex 3
            "12" => "exceed-zero",  //  Exceed
            "13" => "exceed-zero",  //  Exceed 2
            "14" => "exceed-zero",  //  Zero
            "15" => "nxnx2",        //  NX
            "16" => "nxnx2",        //  NX2
            "17" => "NX Absolute",  //  NXA
            "18" => "Fiesta",       //  Fiesta
            "19" => "Fiesta Ex",    //  Fiesta Ex
            "20" => "Fiesta 2",     //  Piu Pro/Pro2
            "21" => "Fiesta 2"      //  Fiesta 2
    );
    
    private $blacklist  =   "";
    private $blacklist_array = array();
    
    public $stepcharts = array();
    
    function __construct($host,$user,$pass,$db) {
        $this->conn =   mysqli_connect($host,$user,$pass,$db);
    }
    
    function GetStoredProfiles($bkpdir,$fbid)   {
        $profiles = array();
        $profdir = $bkpdir."/".sha1($fbid);
        if(!file_exists($profdir) && !is_dir($profdir))
            return $profiles;
        
        
        if($handle = opendir($profdir)) {
            while(($file = readdir($handle)) !== false) {
                if($file != "." && $file != "..")   {
                    $profiles[] = array(
                            "profilename"   =>  $file,
                            "lastupdate"    =>  filemtime($profdir."/".$file."/fiesta2_save.bin"),
                            "lastupdate_hr" =>  date ("d/m/Y H:i:s", filemtime($profdir."/".$file."/fiesta2_save.bin"))
                    );
                }
            }
        }
        
        return $profiles;
    }
    
    function InitBlacklist($blacklist)  {
        foreach($blacklist as $name)    
            $this->blacklist .= " AND `ranking`.`userid` != \"".$name."\"";
        $this->blacklist_array = $blacklist;
    }
    function GetArrayLine($f)   {
        $data = fgetcsv($f);
        if($data !== FALSE) {
            return $data;
        }else
            return false;
    }
    function GetGrades()    {
        return $this->grade;
    }
    function GetModes() {
        return $this->songmode;
    }
    function GetTypes() {
        return $this->typemask;
    }
    function GetAvatarID($id)   {
        return ($id < 0 || $id > 260)?0:$id;
    }
    function GetGame($version)  {
        return (array_key_exists($version,$this->gamelist))?$this->gamelist[$version]:"UNKNOWN";
    }
    function GetSongActiveMode($mode)   {
        if(array_key_exists($mode,$this->songmode))
            return $this->songmode[$mode];
        else
            return $mode;
    }
    function SongSortByName()   {
        function cmp($a, $b) {
            $namea = $a["Artist0"]." - ".$a["Name0"];
            $nameb = $b["Artist0"]." - ".$b["Name0"];
            if ($namea == $nameb) {
                return 0;
            }
            return ($namea < $nameb) ? -1 : 1;
        }
        uasort($this->songs,"cmp");
    }
    function GetMostPlayedSongs()   {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->get_mostplayed_songs);
        $cmd->execute();
        $cmd->store_result();
        $ranks = array();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($songid,$totalc);
            while($cmd->fetch())    
                $ranks[]    =   array(
                        "songid"    =>  $songid,
                        "totalc"    =>  $totalc
                );
        }
        return $ranks;
        $cmd->close();      
    }
    function GetMostPlayedCharts()  {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->get_mostplayed_charts);
        $cmd->execute();
        $cmd->store_result();
        $ranks = array();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($mode,$difficult,$songid,$totalc);
            while($cmd->fetch())
                $ranks[]    =   array(
                        "songid"    =>  $songid,
                        "mode"      =>  $mode,
                        "difficult" =>  $difficult,
                        "totalc"    =>  $totalc
                );
        }
        return $ranks;
        $cmd->close();
    }
    
    function GetSongHighScore($songid,$difficult,$mode) {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->get_songhighscore);
        $cmd->bind_param("iii",$songid,$difficult,$mode);
        $cmd->execute();
        $cmd->store_result();
        $ranks = false;
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($entry,$userid,$songid,$difficult,$mode,$grade,$score,$count,$date);
            $cmd->fetch();
            $ranks  =   array(
                    "user"      =>  $this->GetUData($userid),
                    "songid"    =>  $songid,
                    "difficult" =>  $difficult,
                    "mode"      =>  $mode,
                    "grade"     =>  $grade,
                    "score"     =>  $score,
                    "count"     =>  $count,
                    "date"      =>  $date
            );
        }
        return $ranks;
        $cmd->close();
    }   
    function GetSongUserHighScore($songid,$difficult,$mode,$userid) {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->get_userhighscore_song);
        $cmd->bind_param("iiis",$songid,$difficult,$mode,$userid);
        $cmd->execute();
        $cmd->store_result();
        $ranks = false;
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($entry,$userid,$songid,$difficult,$mode,$grade,$score,$count,$date);
            $cmd->fetch();
            $ranks  =   array(
                    "user"      =>  $this->GetUData($userid),
                    "songid"    =>  $songid,
                    "difficult" =>  $difficult,
                    "mode"      =>  $mode,
                    "grade"     =>  $grade,
                    "score"     =>  $score,
                    "count"     =>  $count,
                    "date"      =>  $date
            );
        }
        return $ranks;
        $cmd->close();
    }
    function GetSongTop25($songid,$difficult,$mode) {
        $cmd    =   $this->conn->stmt_init();
        $tmpq   =   str_ireplace("{BLACKLIST}",$this->blacklist,$this->get_scoretop25_song);
        $cmd->prepare($tmpq);
        $cmd->bind_param("iiiiii",$songid,$difficult,$mode,$songid,$difficult,$mode);
        $cmd->execute();
        $cmd->store_result();
        $ranks = array();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($entry,$userid,$songid,$difficult,$mode,$grade,$score,$count,$date);
            while($cmd->fetch())
                $ranks[]    =   array(
                    "user"      =>  $this->GetUData($userid),
                    "songid"    =>  $songid,
                    "difficult" =>  $difficult,
                    "mode"      =>  $mode,
                    "grade"     =>  $grade,
                    "score"     =>  $score,
                    "count"     =>  $count,
                    "date"      =>  $date
                );
        }
        return $ranks;
        $cmd->close();
    }
    function LoadSongList($songfile)    {
        $f = fopen($songfile,"r");
        $head = $this->GetArrayLine($f);
        $this->songs = array();
        while( ($data = $this->GetArrayLine($f)) !== false)   {
            if($data[0] != "FFFFFFFF" && $data[1] != "NO_SONG")  {
                $song = array();
                foreach( $data as $key => $val )    {
                    if(!empty($head[$key]))
                        $song[$head[$key]] = $val;
                }
                $this->songs[$song["ID"]] = $song;
            }
        }
    }
    function LoadChartList($chartfile)  {
        $f = fopen($chartfile,"r"); 
        
        $head = $this->GetArrayLine($f);
        $this->charts = array();
        while( ($data = $this->GetArrayLine($f)) !== false)   {
            if($data[0] != "FFFFFFFF" && $data[1] != "NO_SONG")  {
                $chart = array();
                foreach( $data as $key => $val )    {
                    if(!empty($head[$key]))
                        $chart[$head[$key]] = $val;
                }
                array_push($this->charts, $chart);
            }
        }
    }
    
    function FillStepList() {
        $this->stepcharts = $this->songs;
        foreach($this->charts as $step)    {
            if($this->stepcharts[$step["PreviewChartOffset"]]["charts"] == null)
                $this->stepcharts[$step["PreviewChartOffset"]]["charts"] = array();
            array_push($this->stepcharts[$step["PreviewChartOffset"]]["charts"], $step);
        }
    }
    
    
    function GetGrade($grade)   {
        if(array_key_exists($grade,$this->grade))
            return $this->grade[$grade];
        else
            return $grade;
    }
    function GetSongs()     {
        return $this->songs;
    }
    function GetSongData($songid)   {
        if(array_key_exists($songid,$this->songs))  {
            return $this->songs[$songid];
        }else{
            return array(   
            "ID"                => "NOT_FOUND",
            "Name0"             => $songid,
            "Name1"             => "NOT_FOUND",
            "Name2"             => "NOT_FOUND",
            "Name3"             => "NOT_FOUND",
            "Name4"             => "NOT_FOUND",
            "Artist0"           => "NOT_FOUND",
            "Artist1"           => "NOT_FOUND",
            "Artist2"           => "NOT_FOUND",
            "Artist3"           => "NOT_FOUND",
            "Artist4"           => "NOT_FOUND",
            "BPM0"              => 0,
            "BPM1"              => 0,
            "BPM2"              => 0,
            "BPM3"              => 0,
            "BPM4"              => 0,
            "ParentSongIndex"   => 4294967295,
            "GameVersion"       => 0,
            "Channel"           => 0,
            "ActiveMode"        => 0,
            "SomeBoolean"       => 0,
            "Length"            => 0,
            "Padding1"          => 4294967295,
            "Padding2"          => 4294967295
            );          
        }
    }
    function GetSongMode($ctype)    {
        if(array_key_exists($ctype,$this->typemask))
            return $this->typemask[$ctype];
        else 
            return $ctype;
    }
    function GetUData($userid)  {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getudata);
        $cmd->bind_param("s",$userid);
        $cmd->execute();
        $cmd->store_result();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($name,$regionid,$avatarid,$level,$calories,$vo2,$steps,$games,$exp,$score,$missions,$coop,$bwins,$bloses,$bdraws,$version,$cpu,$motherboard,$gfx,$hdd,$totalram,$haspkey);
            $cmd->fetch();
            $data   =   array(
                    "name"          =>  $name,
                    "region"        =>  $regionid,
                    "avatar"        =>  $avatarid,
                    "level"         =>  $level,
                    "calories"      =>  $calories,
                    "vo2"           =>  $vo2,
                    "steps"         =>  $steps,
                    "games"         =>  $games,
                    "exp"           =>  $exp,
                    "score"         =>  $score,
                    "missions"      =>  $missions,
                    "coop"          =>  $coop,
                    "battle_wins"   =>  $bwins,
                    "battle_loses"  =>  $bloses,
                    "battle_draws"  =>  $bdraws,
                    "version"       =>  $version,
                    "cpu"           =>  $cpu,
                    "motherboard"   =>  $motherboard,
                    "gfx"           =>  $gfx,
                    "hdd"           =>  $hdd,
                    "totalram"      =>  $totalram,
                    //"haspkey"     =>  $haspkey
            );
            
            $cmd->close();
            return $data;
        }
        $cmd->close();
        return false;   
    }
    
    
    
    function GetURanks($userid) {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->geturanks);
        $cmd->bind_param("s",$userid);
        $cmd->execute();
        $cmd->store_result();
        $ranks = array();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($entry,$userid,$songid,$difficult,$mode,$grade,$score,$count,$date);
            while($cmd->fetch())
            $ranks[]    =   array(
                "songid"    =>  $songid,
                "difficult" =>  $difficult,
                "mode"      =>  $mode,
                "grade"     =>  $grade,
                "score"     =>  $score,
                "count"     =>  $count,
                "date"      =>  $date
            );
        }   
        $cmd->close();
        return $ranks;  
    }
    
    function GetUSRanks($userid,$songid)    {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getusranks);
        $cmd->bind_param("si",$userid,$songid);
        $cmd->execute();
        $cmd->store_result();
        $ranks = array();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($entry,$userid,$songid,$difficult,$mode,$grade,$score,$count,$date);
            $ranks[]    =   array(
                    "songid"    =>  $songid,
                    "difficult" =>  $difficult,
                    "mode"      =>  $mode,
                    "grade"     =>  $grade,
                    "score"     =>  $score,
                    "count"     =>  $count,
                    "date"      =>  $date
            );
        }
        $cmd->close();
        return $ranks;      
    }
    
    function GetArcadeXTop100($q)   {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($q);
        $cmd->execute();
        $cmd->store_result();
        $top100 = array();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($name,$regionid,$avatarid,$level,$calories,$vo2,$steps,$games,$exp,$score,$missions,$coop,$bwins,$bloses,$bdraws,$version,$cpu,$motherboard,$gfx,$hdd,$totalram,$haspkey);
            $c = 1;
            while($cmd->fetch())    {
                if(!in_array($name,$this->blacklist_array)) {
                    $top100[]   =   array(
                            "name"          =>  $name,
                            "region"        =>  $regionid,
                            "avatar"        =>  $avatarid,
                            "level"         =>  $level,
                            "calories"      =>  $calories,
                            "vo2"           =>  $vo2,
                            "steps"         =>  $steps,
                            "games"         =>  $games,
                            "exp"           =>  $exp,
                            "score"         =>  $score,
                            "missions"      =>  $missions,
                            "coop"          =>  $coop,
                            "battle_wins"   =>  $bwins,
                            "battle_loses"  =>  $bloses,
                            "battle_draws"  =>  $bdraws,
                            "version"       =>  $version,
                            "cpu"           =>  $cpu,
                            "motherboard"   =>  $motherboard,
                            "gfx"           =>  $gfx,
                            "hdd"           =>  $hdd,
                            "totalram"      =>  $totalram,
                            //"haspkey"     =>  $haspkey
                    );
                    
                    $c++;
                    if($c>100)
                        break;
                }
            }
        }
        return $top100;
        $cmd->close();      
    }
    function GetArcadeScoreTop100() {
        return $this->GetArcadeXTop100($this->get_arcade_score_top100);
    }   
    function GetArcadeEXPTop100()   {
        return $this->GetArcadeXTop100($this->get_arcade_exp_top100);
    }
    
    function GetArcadeCaloriesTop100()  {
        return $this->GetArcadeXTop100($this->get_arcade_calories_top100);
    }
    
    function GetArcadeMissionsTop100()  {
        return $this->GetArcadeXTop100($this->get_arcade_missions_top100);
    }
    
    function CheckRank($userid,$songid,$difficult,$mode,$score) {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->checkrankq);
        $cmd->bind_param("siiii",$userid,$songid,$difficult,$mode,$score);
        $cmd->execute();
        $cmd->store_result();
        $n = $cmd->num_rows;
        $cmd->close();
        return $n > 0;      
    }
    
    function CheckFacebookID($fbid) {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getuserdata);
        $cmd->bind_param("s",$fbid);
        $cmd->execute();
        $cmd->store_result();
        $n = $cmd->num_rows;
        $cmd->close();
        return $n > 0;
    }
    
    function GetUserData($fbid) {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getuserdata);
        $cmd->bind_param("s",$fbid);
        $cmd->execute();
        $cmd->store_result();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($id,$username,$facebookid,$password,$name,$email);
            $cmd->fetch();  
            $userdata = array(
                "username"  =>  $username,
                "name"      =>  $name,
                "id"        =>  $facebookid,
                "uid"       =>  $id,
                "email"     =>  $email
            );
        }else
            $userdata = false;
        $cmd->close();
        return $userdata;
    }
    
    function GetUserDataSHA($fbid)  {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getuserdatasha);
        $cmd->bind_param("s",$fbid);
        $cmd->execute();
        $cmd->store_result();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($id,$username,$facebookid,$password,$name,$email);
            $cmd->fetch();
            $userdata = array(
                    "username"  =>  $username,
                    "name"      =>  $name,
                    "id"        =>  $facebookid,
                    "uid"       =>  $id,
                    "email"     =>  $email
            );
        }else
            $userdata = false;
        $cmd->close();
        return $userdata;
    }
    
    function GetAllUCS()    {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getallucs);
        $cmd->execute();
        $cmd->store_result();
        $ucs = array();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($title,$filename,$level,$mode,$ucsid,$users_id,$date,$downcount,$viewcount);
            while($cmd->fetch())    {
                if(!in_array($name,$this->blacklist_array)) {
                    $ucs    =   array(
                            "title"         =>  empty($title)?"Untitled":$title,
                            "filename"      =>  $filename,
                            "level"         =>  $level,
                            "mode"          =>  $mode,
                            "ucsid"         =>  $ucsid,
                            "uid"           =>  $users_id,
                            "date"          =>  $date,
                            "downcount"     =>  $downcount,
                            "viewcount"     =>  $viewcount
                    );  
                }
            }
        }
        $cmd->close();
        return $ucs;
    }
    function GetFacebookID($uid)    {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getfbid);
        $cmd->bind_param("s",$uid);
        $cmd->execute();
        $cmd->store_result();
        $fbid = -1;
        $cmd->bind_result($fbid);
        $cmd->fetch();
        $cmd->close();
        return $fbid;
    
    }
    function GetUserUCS($uid)   {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getuserucs);
        $cmd->bind_param("s",$uid);
        $cmd->execute();
        $cmd->store_result();
        $ucs = array();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($id,$title,$filename,$level,$mode,$ucsid,$users_id,$date,$downcount,$viewcount);
            while($cmd->fetch())    {
                $ucs[]  =   array(
                        "id"            =>  $id,
                        "title"         =>  empty($title)?"Untitled":$title,
                        "filename"      =>  $filename,
                        "level"         =>  $level,
                        "mode"          =>  $mode,
                        "ucsid"         =>  $ucsid,
                        "uid"           =>  $users_id,
                        "fbid"          =>  $fbid,
                        "date"          =>  $date,
                        "downcount"     =>  $downcount,
                        "viewcount"     =>  $viewcount
                );
            }
        }
        $cmd->close();
        return $ucs;
    }
    
    function GetMostDownloadedUCS() {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getmostdownucs);
        $cmd->execute();
        $cmd->store_result();
        $ucs = array();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($id,$title,$filename,$level,$mode,$data,$ucsid,$users_id,$date,$downcount,$viewcount,$username);
            while($cmd->fetch())    {
                $ucs[]  =   array(
                        "id"            =>  $id,
                        "title"         =>  empty($title)?"Untitled":$title,
                        "filename"      =>  $filename,
                        "level"         =>  $level,
                        "mode"          =>  $mode,
                        "ucsid"         =>  $ucsid,
                        "uid"           =>  $users_id,
                        "date"          =>  $date,
                        "username"      =>  $username,
                        "downcount"     =>  $downcount,
                        "viewcount"     =>  $viewcount
                );
            }
        }
        $cmd->close();
        return $ucs;
    }
    
    function GetMostViewedUCS() {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getmostviewucs);
        $cmd->execute();
        $cmd->store_result();
        $ucs = array();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($id,$title,$filename,$level,$mode,$data,$ucsid,$users_id,$date,$downcount,$viewcount,$username);
            while($cmd->fetch())    {
                $ucs[]  =   array(
                        "id"            =>  $id,
                        "title"         =>  empty($title)?"Untitled":$title,
                        "filename"      =>  $filename,
                        "level"         =>  $level,
                        "mode"          =>  $mode,
                        "ucsid"         =>  $ucsid,
                        "uid"           =>  $users_id,
                        "date"          =>  $date,
                        "username"      =>  $username,
                        "downcount"     =>  $downcount,
                        "viewcount"     =>  $viewcount
                );
            }
        }
        $cmd->close();
        return $ucs;
    }
        
    function GetLastUCS()   {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getlastucs);
        $cmd->execute();
        $cmd->store_result();
        $ucs = array();
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($id,$title,$filename,$level,$mode,$data,$ucsid,$users_id,$date,$downcount,$viewcount,$username);
            while($cmd->fetch())    {
                $ucs[]  =   array(
                        "id"            =>  $id,
                        "title"         =>  empty($title)?"Untitled":$title,
                        "filename"      =>  $filename,
                        "level"         =>  $level,
                        "mode"          =>  $mode,
                        "ucsid"         =>  $ucsid,
                        "uid"           =>  $users_id,
                        "date"          =>  $date,
                        "username"      =>  $username,
                        "downcount"     =>  $downcount,
                        "viewcount"     =>  $viewcount
                );
            }
        }
        $cmd->close();
        return $ucs;
    }
    
    function IncUCSDownCount($id)   {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->incucscount);
        $cmd->bind_param("i",$id);
        $cmd->execute();
        $cmd->close();  
    }
    
    function IncUCSViewCount($id)   {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->incucsvcount);
        $cmd->bind_param("i",$id);
        $cmd->execute();
        $cmd->close();
    }
    
    function GetUCS($id)    {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->getucs);
        $cmd->bind_param("i",$id);
        $cmd->execute();
        $cmd->store_result();
        $ucs = false;
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($id,$title,$filename,$level,$mode,$data,$ucsid,$users_id,$date,$downcount,$viewcount,$fbid,$username,$name);
            while($cmd->fetch())    {
                if(!in_array($name,$this->blacklist_array)) {
                    $ucs    =   array(
                            "title"         =>  empty($title)?"Untitled":$title,
                            "filename"      =>  $filename,
                            "level"         =>  $level,
                            "mode"          =>  $mode,
                            "data"          =>  $data,
                            "ucsid"         =>  $ucsid,
                            "uid"           =>  $users_id,
                            "username"      =>  $username,
                            "name"          =>  $name,
                            "date"          =>  $date,
                            "fbid"          =>  $fbid,
                            "downcount"     =>  $downcount,
                            "viewcount"     =>  $viewcount
                    );
                }
            }
        }
        $cmd->close();
        return $ucs;
    }

    function GetProfileAvatarID($name)  {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->get_profile_avatarid);
        $cmd->bind_param("s",$name);
        $cmd->execute();
        $cmd->store_result();
        $ucs = array();
        $id  = 0;
        if($cmd->num_rows() > 0)    {
            $cmd->bind_result($id);
            $cmd->fetch();
        }
        $cmd->close();
        return $id;
    }
    
    function AddUser($username,$fbid,$password,$name,$email)    {
        if(empty($username))
            $username = sha1($fbid.$password.$name.$email.time());
        if(empty($password))
            $password = sha1(time().openssl_random_pseudo_bytes(10).$email.$password.sha1(time())); //  Just for junk
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->adduser);  
        $cmd->bind_param('sssss',$username,$fbid,$password,$name,$email);
        $cmd->execute();    
        $ok = $cmd->insert_id;
        $cmd->close();
        return $ok;
    }
    
    function AddUserProfile($userid,$profilename)   {
        error_log("UID: ".$userid." - ".$profilename);
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->adduserprofile);
        $cmd->bind_param('si',$profilename,$userid);
        $ok = $cmd->execute();
        $cmd->close();
        return $ok;
    }
    
    function StripString($str)  {
        return str_ireplace("\n","",str_ireplace("\r","",$str));
    }
    function AddUserCustomStep($userid,$title,$filename,$level,$mode,$data,$ucsid)  {
        $mode = $this->StripString($mode);
        $title = $this->StripString($title);
        $filename = $this->StripString($filename);
        if(empty($level))
            $level = "50";
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->adducs);
        $cmd->bind_param('ssisssi',$title,$filename,$level,$mode,$data,$ucsid,$userid);
        $cmd->execute();
        $ok = $cmd->insert_id;
        $cmd->close();
        return $ok;
    }
    //
    
    function UpdateUCS($id,$userid,$title,$level)   {
        //SET `level` = ?, `title` = ? WHERE `id` = ? AND `users_id` = ?";
        $title = $this->StripString($title);
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->updateucs);
        $cmd->bind_param('isii',$level,$title,$id,$userid);
        $cmd->execute();
        $ok = $cmd->affected_rows > 0;
        $cmd->close();
        return $ok;
    }
    
    function UpdateName($id,$name)  {
        $title = $this->StripString($title);
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->update_name);
        $cmd->bind_param('si',$name,$id);
        $cmd->execute();
        $ok = $cmd->affected_rows > 0;
        $cmd->close();
        return $ok;
    }
        
    function WriteSave(&$savedata)  {
        if($this->WritePlayer($savedata))
        foreach($savedata->highscores as $score)    
            $this->WriteRank($savedata->playerid, $score);
    }

    function WritePlayer(&$savedata)    {
        $cmd    =   $this->conn->stmt_init();
        $cmd->prepare($this->addplayer);
        $cmd->bind_param('siiiiissssiiiiisssssii', 
                $savedata->playerid,
                $savedata->region,
                $savedata->avatarid,
                $savedata->level,
                $savedata->calories,
                $savedata->vo2,
                $savedata->steps,
                $savedata->games,
                $savedata->exp,
                $savedata->score,
                $savedata->missions,
                $savedata->coop,
                $savedata->battlewins,
                $savedata->battleloses,
                $savedata->battledraws,
                
                $savedata->version,
                $savedata->cpu,
                $savedata->motherboard,
                $savedata->gfxcard,
                $savedata->hdd,
                $savedata->totalram,
                $savedata->haspkey);
        $ok = $cmd->execute();
        error_log($cmd->error);
        $cmd->close();      
        return $ok;
    }
    
    function WriteRank($userid, &$rank) {
        if(!$this->CheckRank($userid,$rank->songid,$rank->difficult,$rank->mode,$rank->score))  {
            $cmd    =   $this->conn->stmt_init();
            $cmd->prepare($this->addrank);
            $cmd->bind_param('siiiiii', $userid, $rank->songid,$rank->difficult,$rank->mode,$rank->grade, $rank->score, $rank->count);
            $cmd->execute();
            $cmd->close();
        }
    }
}