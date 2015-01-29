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

include("includes/config.php");
include("includes/f2score.class.php");
include("includes/f2save.class.php");
include("includes/f2db.class.php");
include("includes/f2gen.class.php");
include("includes/f2fb.class.php");

session_start();
$db = new F2DB($host,$user,$pass,$db);
$db->InitBlacklist($blacklist);
$ptitle		=	"Home";
$pdesc		=	"Fiesta 2 Save Ranking";
$pimg		=	$siteurl."img/logo.png";
$pimgsize	=	array("width"=>420,"height"=>185);

$fb   		= 	new F2FB($fbappid,$fbappsec,$fbadminid,$fbpostpage,$fbstuff);

if($fb->user)	{
	$islogged = true;
	$fbloginurl = $siteurl."?page=logout";
	$fbloginname = "Facebook Logout";
	if(!$db->CheckFacebookID($fb->user))	{
		$profile = $fb->GetUserProfile();
		$uid = $db->AddUser($profile["username"],$profile["id"],"",$profile["first_name"]." ".$profile["last_name"],$profile["email"]);
		$userdata = array(
				"username"	=>	$profile["username"],
				"name"		=>	$profile["first_name"]." ".$profile["last_name"],
				"id"		=>	$profile["id"],
				"uid"		=>	$uid,
				"email"		=>	$profile["email"]
		);
	}else{
		$userdata = $db->GetUserData($fb->user);
	}
}else{
	$islogged = false;
	$fbloginurl = $fb->GetLoginURL();
	$fbloginname = "Facebook Login";
}

// if needed directories are not configured
if(!file_exists($tempdirname) && !is_dir($tempdirname)) {
	mkdir($tempdirname);
	touch($tempdirname . "/" . "index.html");
}
if(!file_exists($logdirname) && !is_dir($logdirname)) {
	mkdir($logdirname);
	touch($logdirname . "/" . "index.html");
}
if(!is_dir($tempdirname) && !is_dir($logdirname))
	exit_with_error_msg($starthtml . $errorhtml, "Required directories not configured. Contact the admin at " . $adminwebmail, $endhtml);

if(isset($_REQUEST["action"]))	{
	//	Action
	switch($_REQUEST["action"])	{
		case "changename":
			if($islogged && !empty($_REQUEST["name"]))	{
				$db->UpdateName($userdata["uid"], $_REQUEST["name"]);
				$userdata["name"] = $_REQUEST["name"];
			}
			break;
		default:
	}
}

// if the form wasn't submitted
if(!isset($_FILES["save"]) && !isset($_FILES["rank"]) && !isset($_FILES["ucs"])) {
	$base 		= 	GetTPL("base");
	$menu 		= 	GetTPL("menu");
	$alerts 	= 	"";
	
	if(isset($_REQUEST["page"]))	{
		switch($_REQUEST["page"])	{
			case "logout":
				$fb_key = 'fbsr_'.$fbappid;
				setcookie($fb_key, '', time()-3600);
				$fb->destroySession();
				header("Location: ".$siteurl);
				break;
			case "rank":
				if(isset($_REQUEST["userid"]))	{
					$udata = $db->GetUData($_REQUEST["userid"]);
					if($udata)	{
						$urank = $db->GetURanks($_REQUEST["userid"]);
						$db->LoadSongList("songlist.csv");
						$body = GetTPL("urank");
						$udata["avatar"] = str_pad($db->GetAvatarID($udata["avatar"]+1),3,'0', STR_PAD_LEFT);
						foreach($udata as $key => $value)	
							$body	=	str_ireplace("{".$key."}",$value,$body);					
						$rkd = "";

						foreach($urank as $rank)	{
							if(strpos(strtoupper(dechex($rank["songid"])),"CF") === false && strpos(strtoupper(dechex($rank["songid"])),"AF") === false && strpos(strtoupper(dechex($rank["songid"])),"BF") === false && strpos(strtoupper(dechex($rank["songid"])),"E000") === false && strpos(strtoupper(dechex($rank["songid"])),"EF") === false && strpos(strtoupper(dechex($rank["songid"])),"DF") === false)	{
								$songdata = $db->GetSongData(strtoupper(dechex($rank["songid"])));
								$rkd .= "\t\t\t<tr>\n";
								$rkd .= "\t\t\t\t<td>".strtoupper(dechex($rank["songid"]))."</td>\n";
								$rkd .= "\t\t\t\t<td>\n";
								$rkd .= "\t\t\t\t\t<a href=\"?page=songrank&songid=".$rank["songid"]."&level=".$rank["difficult"]."&mode=".$rank["mode"]."&userid=".$_REQUEST["userid"]."\">\n";
								$rkd .= "\t\t\t\t\t\t".$songdata["Artist0"]." - ".$songdata["Name0"]." - ".$db->GetSongActiveMode($songdata["ActiveMode"])."\n";
								$rkd .= "\t\t\t\t\t</a>\n";
								$rkd .= "\t\t\t\t</td>\n";
								$rkd .= "\t\t\t\t<td class=\"text-center\"><img src=\"{SITE_URL}?page=api&cmd=genball&level=".$rank["difficult"]."&mode=".$db->GetSongMode($rank["mode"])."\" width=32 height=32/></td>\n";
								$rkd .= "\t\t\t\t<td class=\"text-center\"><img src=\"{SITE_URL}img/grades/".$db->GetGrade($rank["grade"]).".png\" width=32 height=32/></td>\n";
								$rkd .= "\t\t\t\t<td class=\"text-right\">".$rank["score"]."</td>\n";
								$rkd .= "\t\t\t\t<td class=\"text-center\">".$rank["count"]."</td>\n";
								$rkd .= "\t\t\t\t<td class=\"text-center\">".$rank["date"]."</td>\n";
								$rkd .= "\t\t\t</tr>\n";
							}
						}
						$body = str_ireplace("{RANKINGS}",$rkd,$body);
						$ptitle =	$_REQUEST["userid"]." rank";
						$pimg		=	$siteurl."img/avatar/CH_".$udata["avatar"].".PNG";
						$pimgsize["width"]	=	128;
						$pimgsize["height"]	=	128;
					}else{
						header("HTTP/1.0 404 Not Found");
						$body	=	GetTPL("problem");
						$body	=	str_ireplace("{PROBLEM_TITLE}","Profile not found",$body);
						$body	=	str_ireplace("{MSG}","HUE? GIBE YOUR PROFILE OR I REPORT U HEUEAHUEHAUEHUAEH",$body);
						$ptitle =	"404 - Page not found";
					}
					

				}else
					header("Location: ".$siteurl);
			break;
            
			case "top100exp":
				$body = GetTPL("top100");
				$data = $db->GetArcadeEXPTop100();
				$rkd = "";
				$c=1;
				foreach($data as $key=>$rank)	{
					if(!in_array($rank["name"],$blacklist))	{
						$rkd .= "\t\t\t<tr>\n";
						$rkd .= "\t\t\t\t<td><B>".($c)."</B></td>\n";
						$rkd .= "\t\t\t\t<td><img src=\"{SITE_URL}img/avatar/CH_".str_pad($db->GetAvatarID($rank["avatar"]+1),3,'0', STR_PAD_LEFT).".PNG\" height=64/></td>\n";
						$rkd .= "\t\t\t\t<td><a href=\"{SITE_URL}?userid=".$rank["name"]."&page=rank\">".$rank["name"]."</a></td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["level"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["exp"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["score"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["calories"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["missions"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["games"]."</td>\n";
						$rkd .= "\t\t\t</tr>\n";
						$c++;
					}
				}
				$body	=	str_ireplace("{RANKINGS}",$rkd,$body);
				$body	=	str_ireplace("{TITLE}","Arcade Experience Top 100",$body);
				$ptitle =	"Arcade Experience TOP 100";
				break;
			case "top100score":
				$body = GetTPL("top100");
				$data = $db->GetArcadeScoreTop100();
				$rkd = "";
				$c=1;
				foreach($data as $key=>$rank)	{
					if(!in_array($rank["name"],$blacklist))	{
						$rkd .= "\t\t\t<tr>\n";
						$rkd .= "\t\t\t\t<td><B>".($c)."</B></td>\n";
						$rkd .= "\t\t\t\t<td><img src=\"{SITE_URL}img/avatar/CH_".str_pad($db->GetAvatarID($rank["avatar"]+1),3,'0', STR_PAD_LEFT).".PNG\" height=64/></td>\n";
						$rkd .= "\t\t\t\t<td><a href=\"{SITE_URL}?userid=".$rank["name"]."&page=rank\">".$rank["name"]."</a></td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["level"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["exp"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["score"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["calories"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["missions"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["games"]."</td>\n";
						$rkd .= "\t\t\t</tr>\n";
						$c++;
					}
				}
				$body	=	str_ireplace("{RANKINGS}",$rkd,$body);
				$body	=	str_ireplace("{TITLE}","Arcade Score Top 100",$body);
				$ptitle =	"Arcade Score TOP 100";
				break;
			break;
			case "top100mission":
				$body = GetTPL("top100");
				$data = $db->GetArcadeMissionsTop100();
				$rkd = "";
				$c=1;
				foreach($data as $key=>$rank)	{
					if(!in_array($rank["name"],$blacklist))	{
						$rkd .= "\t\t\t<tr>\n";
						$rkd .= "\t\t\t\t<td><B>".($c)."</B></td>\n";
						$rkd .= "\t\t\t\t<td><img src=\"{SITE_URL}img/avatar/CH_".str_pad($db->GetAvatarID($rank["avatar"]+1),3,'0', STR_PAD_LEFT).".PNG\" height=64/></td>\n";
						$rkd .= "\t\t\t\t<td><a href=\"{SITE_URL}?userid=".$rank["name"]."&page=rank\">".$rank["name"]."</a></td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["level"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["exp"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["score"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["calories"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["missions"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["games"]."</td>\n";
						$rkd .= "\t\t\t</tr>\n";
						$c++;
					}
				}
				$body	=	str_ireplace("{RANKINGS}",$rkd,$body);
				$body	=	str_ireplace("{TITLE}","Arcade Mission Top 100",$body);
				$ptitle =	"Arcade Mission TOP 100";
				break;
			break;
			case "top100calories":
				$body = GetTPL("top100");
				$data = $db->GetArcadeCaloriesTop100();
				$rkd = "";
				$c=1;
				foreach($data as $key=>$rank)	{
					if(!in_array($rank["name"],$blacklist))	{
						$rkd .= "\t\t\t<tr>\n";
						$rkd .= "\t\t\t\t<td><B>".($c)."</B></td>\n";
						$rkd .= "\t\t\t\t<td><img src=\"{SITE_URL}img/avatar/CH_".str_pad($db->GetAvatarID($rank["avatar"]+1),3,'0', STR_PAD_LEFT).".PNG\" height=64/></td>\n";
						$rkd .= "\t\t\t\t<td><a href=\"{SITE_URL}?userid=".$rank["name"]."&page=rank\">".$rank["name"]."</a></td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["level"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["exp"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["score"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["calories"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["missions"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["games"]."</td>\n";
						$rkd .= "\t\t\t</tr>\n";
						$c++;
					}
				}
				$body	=	str_ireplace("{RANKINGS}",$rkd,$body);
				$body	=	str_ireplace("{TITLE}","Arcade Calories Top 100",$body);
				$ptitle =	"Arcade Calories TOP 100";
				break;
				
			break;
			case "mostplayedsongs":
				$body = GetTPL("mostplayedsongs");
				$data = $db->GetMostPlayedSongs();
				$g = new F2Gen("./img/");
				$db->LoadSongList("songlist.csv");
				$rkd = "";
				$c=1;
				foreach($data as $key=>$rank)	{
						$songdata = $db->GetSongData(strtoupper(dechex($rank["songid"])));
						$rkd .= "\t\t\t<tr>\n";
						$rkd .= "\t\t\t\t<td><B>".($c)."</B></td>\n";
						if($g->CheckTitle(strtoupper(dechex($rank["songid"]))))
							$rkd .= "\t\t\t\t<td><img src=\"{SITE_URL}img/titles/".strtoupper(dechex($rank["songid"])).".jpg\" height=96/></td>\n";
						else
							$rkd .= "\t\t\t\t<td><img src=\"{SITE_URL}img/titles/notitle.jpg\" height=96/></td>\n";
						$rkd .= "\t\t\t\t<td>".$songdata["Artist0"]."</a></td>\n";
						$rkd .= "\t\t\t\t<td>".$songdata["Name0"]."</td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["totalc"]."</td>\n";
						$rkd .= "\t\t\t</tr>\n";
						$c++;
				}
				$body	=	str_ireplace("{RANKINGS}",$rkd,$body);
				$ptitle =	"Most played songs";
				break;
			case "mostplayedcharts":
					$body = GetTPL("mostplayedcharts");
					$data = $db->GetMostPlayedCharts();
					$g = new F2Gen("./img/");
					$db->LoadSongList("songlist.csv");
					$rkd = "";
					$c=1;
					foreach($data as $key=>$rank)	{
						$songdata = $db->GetSongData(strtoupper(dechex($rank["songid"])));
						$rkd .= "\t\t\t<tr>\n";
						$rkd .= "\t\t\t\t<td><B>".($c)."</B></td>\n";
						if($g->CheckTitle(strtoupper(dechex($rank["songid"]))))
							$rkd .= "\t\t\t\t<td><a href=\"?page=songrank&songid=".$rank["songid"]."&level=".$rank["difficult"]."&mode=".$rank["mode"]."\"><img src=\"{SITE_URL}img/titles/".strtoupper(dechex($rank["songid"])).".jpg\" height=96/></a></td>\n";
						else
							$rkd .= "\t\t\t\t<td><a href=\"?page=songrank&songid=".$rank["songid"]."&level=".$rank["difficult"]."&mode=".$rank["mode"]."\"><img src=\"{SITE_URL}img/titles/notitle.jpg\" height=96/></a></td>\n";
						$rkd .= "\t\t\t\t<td>".$songdata["Artist0"]."</a></td>\n";
						$rkd .= "\t\t\t\t<td>".$songdata["Name0"]."</td>\n";
						$rkd .= "\t\t\t\t<td><img src=\"{SITE_URL}?page=api&cmd=genball&level=".$rank["difficult"]."&mode=".$db->GetSongMode($rank["mode"])."\" height=96/></td>\n";
						$rkd .= "\t\t\t\t<td>".$rank["totalc"]."</td>\n";
						$rkd .= "\t\t\t</tr>\n";
						$c++;
					}
					$body	=	str_ireplace("{RANKINGS}",$rkd,$body);
				$ptitle =	"Most played charst";
					break;
			case "songrank":
				
				if(isset($_REQUEST["songid"]) && isset($_REQUEST["level"]) && isset($_REQUEST["mode"]))	{
					$songrank = $db->GetSongTop25($_REQUEST["songid"],$_REQUEST["level"],$_REQUEST["mode"]);
					$body	=	GetTPL("songrank");
					$rkd	=	"";
					$c		=	1;
					$db->LoadSongList("songlist.csv");
					$songdata = $db->GetSongData(strtoupper(dechex($_REQUEST["songid"])));
					if(count($songrank) == 0)	{
						$body	=	GetTPL("problem");
						$body	=	str_ireplace("{PROBLEM_TITLE}","No ranking data",$body);
						$body	=	str_ireplace("{MSG}","Maybe you selected wrong data.",$body);
					}else{
						foreach($songrank as $rank)	{
							if(!in_array($rank["user"]["name"],$blacklist))	{
								$rkd	.=	"\t\t\t<tr>\n";
								$rkd	.=	"\t\t\t\t<td>".$c."</td>\n";
								$rkd	.=	"\t\t\t\t<td><img src=\"{SITE_URL}img/avatar/CH_".str_pad($db->GetAvatarID($rank["user"]["avatar"]+1),3,'0', STR_PAD_LEFT).".PNG\" height=64/></td>\n";
								$rkd	.=	"\t\t\t\t<td><a href=\"{SITE_URL}?userid=".$rank["user"]["name"]."&page=rank\">".$rank["user"]["name"]."</a></td>\n";
								$rkd	.=	"\t\t\t\t<td><img src=\"{SITE_URL}img/grades/".$db->GetGrade($rank["grade"]).".png\" width=32 height=32/></td>\n";
								$rkd	.=	"\t\t\t\t<td>".$rank["score"]."</td>\n";
								$rkd	.=	"\t\t\t\t<td>".$rank["count"]."</td>\n";
								$rkd	.=	"\t\t\t\t<td>".$rank["date"]."</td>\n";
								$c++;
							}
						}
						if(isset($_REQUEST["userid"]))	{
							$rank = $db->GetSongUserHighScore($_REQUEST["songid"],$_REQUEST["level"],$_REQUEST["mode"],$_REQUEST["userid"]);
							if($rank)	{
								$rkd	.=	"\t\t\t<tr class=\"success\">\n";
								$rkd	.=	"\t\t\t\t<td><B>YOU</B></td>\n";
								$rkd	.=	"\t\t\t\t<td><img src=\"{SITE_URL}img/avatar/CH_".str_pad($db->GetAvatarID($rank["user"]["avatar"]+1),3,'0', STR_PAD_LEFT).".PNG\" height=64/></td>\n";
								$rkd	.=	"\t\t\t\t<td><a href=\"{SITE_URL}?userid=".$rank["user"]["name"]."&page=rank\">".$rank["user"]["name"]."</a></td>\n";
								$rkd	.=	"\t\t\t\t<td><img src=\"{SITE_URL}img/grades/".$db->GetGrade($rank["grade"]).".png\" width=32 height=32/></td>\n";
								$rkd	.=	"\t\t\t\t<td>".$rank["score"]."</td>\n";
								$rkd	.=	"\t\t\t\t<td>".$rank["count"]."</td>\n";
								$rkd	.=	"\t\t\t\t<td>".$rank["date"]."</td>\n";
							}
						}
						$body	=	str_ireplace("{RANKINGS}",$rkd,$body);
						$body	=	str_ireplace("{TITLE}"," Top 25 (".$songdata["Artist0"]." - ".$songdata["Name0"]." <img src=\"{SITE_URL}?page=api&cmd=genball&level=".$rank["difficult"]."&mode=".$db->GetSongMode($rank["mode"])."\" width=32 height=32/>)",$body);
						//$body	=	print_r($songrank,true);
						$ptitle =	$songdata["Artist0"]." - ".$songdata["Name0"]." top 25" ;
					}
				}else{
					header("HTTP/1.0 404 Not Found");
					$body	=	GetTPL("problem");
					$body	=	str_ireplace("{PROBLEM_TITLE}","Invalid request",$body);
					$body	=	str_ireplace("{MSG}","HUE? GIBE YOUR ARGUMENTS OR I REPORT U HEUEAHUEHAUEHUAEH",$body);
					$ptitle =	"404 - Page not found";
				}
				break;
                
			case "songselect":
				$body		=	GetTPL("selectsong");
				$db->LoadSongList("songlist.csv");
				$db->SongSortByName();
				$songs		=	$db->GetSongs();
				$songselect	=	"";
				foreach($songs as $key=>$song)
					if($song["ActiveMode"] != 0 && strpos(strtoupper($key),"CF") === false && strpos(strtoupper($key),"AF") === false && strpos(strtoupper($key),"BF") === false && strpos(strtoupper($key),"E000") === false && strpos(strtoupper($key),"EF") === false && strpos(strtoupper($key),"DF") === false)
						$songselect .= "\t\t\t\t\t<option value=\"".hexdec($key)."\">".$song["Artist0"]." - ".$song["Name0"]." [".$key."](".$db->GetSongActiveMode($song["ActiveMode"]).")</option>\n";
				
				//$body		=	print_r($songs,true);
				$body		=	str_ireplace("{SONGSELECT}",$songselect,$body);
				$body		=	str_ireplace("{SONGDATA}","",$body);
				$ptitle 	=	"Song Select";
				break;
			case "info":
				$body = GetTPL("info");
				$blacklisted = "";
				foreach($blacklist as $blk)	
					$blacklisted .= "\t\t\t\t<li><B>".$blk."</B></li>\n";
				
				$body	=	str_ireplace("{BLACKLISTED}",$blacklisted,$body);
				$ptitle 	=	"Information";
				break;
			case "404":
				header("HTTP/1.0 404 Not Found");
				$body	=	GetTPL("problem");
				$body	=	str_ireplace("{PROBLEM_TITLE}","404 - Page Not Found",$body);
				$body	=	str_ireplace("{MSG}","HUE? GIBE YOUR PAGE OR I REPORT U HEUEAHUEHAUEHUAEH",$body);
				$ptitle =	"404 - Page not found";
				break;
			case "apiinfo":
				$body		=	GetTPL("apiinfo");
				$ptitle 	=	"API Information";
				break;
			case "senducs":
				$body		=	GetTPL("senducs");
				$ptitle 	=	"Send UCS";
				break;
			case "profilebackup":
				if($islogged)	{
					$body		=	GetTPL("profilebackups");
					$userlink 	= 	$siteurl."pfbackups/".sha1($userdata["id"]);
					$body		=	str_ireplace("{BACKUPS_LINK}",$userlink,$body);
					$profs		= 	$db->GetStoredProfiles($backupdir, $userdata["id"]);
					$profiles	=	"";
					foreach($profs as $profile)	{
						$profiles	.=	"\t\t\t<tr>\n";
						$profiles	.=	"\t\t\t<td><img src=\"{SITE_URL}img/avatar/CH_".str_pad($db->GetAvatarID($db->GetProfileAvatarID($profile["profilename"])+1),3,'0', STR_PAD_LEFT).".PNG\" height=64/></td>\n";
						$profiles	.=	"\t\t\t<td><a href=\"".$siteurl."?userid=".$profile["profilename"]."&page=rank\">".$profile["profilename"]."</a>\n";
						$profiles	.=	"\t\t\t<td>".$profile["lastupdate_hr"]."\n";
						$profiles	.=	"\t\t\t<td><a href=\"".$userlink."/".$profile["profilename"]."/fiesta2_save.bin\">Download</a>\n";
						$profiles	.=	"\t\t\t<td><a href=\"".$userlink."/".$profile["profilename"]."/fiesta2_rank.bin\">Download</a>\n";
						$profiles	.=	"\t\t\t</tr>\n";
					}
					$body		=	str_ireplace("{PROFILES}",$profiles,$body);
					$ptitle 	=	"Profile Backup";
					break;
				}else{
					$body		=	GetTPL("problem");
					$body		=	str_ireplace("{PROBLEM_TITLE}","You're not logged in!",$body);
					$body		=	str_ireplace("{MSG}","HUE? LOGIN OR I REPORT U HEUEAHUEHAUEHUAEH",$body);
					$ptitle 	=	"You're not logged in!";
					break;					
				}
				break;
			case "mostdownloadeducs":
				$body		=	GetTPL("top100ucs");
				$ucslist	=	$db->GetMostDownloadedUCS();
				$modes = array(
						"SINGLE"			=>	"S",
						"SINGLEPERFORMANCE"	=>	"SP",
						"S-PERFORMANCE"		=>	"SP",
						"DOUBLE"			=>	"D",
						"DOUBLEPERFORMANCE"	=>	"DP",
						"D-PERFORMANCE"		=>	"DP"
				);				
				$ucspart = "";
				$c=1;
				foreach($ucslist as $ucs)	{
					$point =  "<tr>";
					$point .= "	<td><B>".$c."</B></td>";
					$point .= " <td><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\"><img src=\"{SITE_URL}/ucs/img/".$ucs["ucsid"].".jpg\" height=64 border=0 /></a></td>\n";
					$point .= "	<td>".$ucs["ucsid"]."</td>";
					$point .= "	<td><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\">".$ucs["title"]."</a></td>";
					$point .= "	<td><img src=\"{SITE_URL}?page=api&cmd=genball&level=".$ucs["level"]."&mode=".$modes[strtoupper($ucs["mode"])]."\" width=64 height=64/></td>";
					$point .= "	<td>".$ucs["username"]."</td>";
					$point .= "	<td class=\"text-center\">".$ucs["downcount"]."</td>";
					$point .= "	<td class=\"text-center\">".$ucs["viewcount"]."</td>";
					$point .= "	<td>".$ucs["date"]."</td>";
					$point .= "	<td class=\"text-center\"><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\"><span class=\"glyphicon glyphicon-eye-open\"></span></a></td>";
					$point .= "	<td class=\"text-center\"><a href=\"{SITE_URL}?page=api&cmd=getucsfile&id=".$ucs["id"]."\"><span class=\"glyphicon glyphicon-download-alt\"></span></a></td>";
					$point .= "</tr>";
					
					$ucspart .= $point;					
					$c++;
				}
				$body	=	str_ireplace("{RANKINGS}",$ucspart,$body);
				$body	=	str_ireplace("{TITLE}","Most downloaded UCS",$body);
				$ptitle =	"Most Downloaded UCS";
				break;
			case "mostvieweducs":
					$body		=	GetTPL("top100ucs");
					$ucslist	=	$db->GetMostViewedUCS();
					$modes = array(
							"SINGLE"			=>	"S",
							"SINGLEPERFORMANCE"	=>	"SP",
							"S-PERFORMANCE"		=>	"SP",
							"DOUBLE"			=>	"D",
							"DOUBLEPERFORMANCE"	=>	"DP",
							"D-PERFORMANCE"		=>	"DP"
					);
					$ucspart = "";
					$c=1;
					foreach($ucslist as $ucs)	{
						$point =  "<tr>";
						$point .= "	<td><B>".$c."</B></td>";
						$point .= " <td><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\"><img src=\"{SITE_URL}/ucs/img/".$ucs["ucsid"].".jpg\" height=64 border=0 /></a></td>\n";
						$point .= "	<td>".$ucs["ucsid"]."</td>";
						$point .= "	<td><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\">".$ucs["title"]."</a></td>";
						$point .= "	<td><img src=\"{SITE_URL}?page=api&cmd=genball&level=".$ucs["level"]."&mode=".$modes[strtoupper($ucs["mode"])]."\" width=64 height=64/></td>";
						$point .= "	<td>".$ucs["username"]."</td>";
						$point .= "	<td class=\"text-center\">".$ucs["downcount"]."</td>";
						$point .= "	<td class=\"text-center\">".$ucs["viewcount"]."</td>";
						$point .= "	<td>".$ucs["date"]."</td>";
						$point .= "	<td class=\"text-center\"><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\"><span class=\"glyphicon glyphicon-eye-open\"></span></a></td>";
						$point .= "	<td class=\"text-center\"><a href=\"{SITE_URL}?page=api&cmd=getucsfile&id=".$ucs["id"]."\"><span class=\"glyphicon glyphicon-download-alt\"></span></a></td>";
						$point .= "</tr>";
							
						$ucspart .= $point;
						$c++;
					}
					$body	=	str_ireplace("{RANKINGS}",$ucspart,$body);
					$body	=	str_ireplace("{TITLE}","Most viewed UCS",$body);
					$ptitle =	"Most Viewed UCS";
					break;
			case "lastucs":
					$body		=	GetTPL("top100ucs");
					$ucslist	=	$db->GetLastUCS();
					$modes = array(
							"SINGLE"			=>	"S",
							"SINGLEPERFORMANCE"	=>	"SP",
							"S-PERFORMANCE"		=>	"SP",
							"DOUBLE"			=>	"D",
							"DOUBLEPERFORMANCE"	=>	"DP",
							"D-PERFORMANCE"		=>	"DP"
					);
					$ucspart = "";
					$c=1;
					foreach($ucslist as $ucs)	{
						$point =  "<tr>";
						$point .= "	<td><B>".$c."</B></td>";
						$point .= " <td><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\"><img src=\"{SITE_URL}/ucs/img/".$ucs["ucsid"].".jpg\" height=64 border=0 /></a></td>\n";
						$point .= "	<td>".$ucs["ucsid"]."</td>";
						$point .= "	<td><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\">".$ucs["title"]."</a></td>";
						$point .= "	<td><img src=\"{SITE_URL}?page=api&cmd=genball&level=".$ucs["level"]."&mode=".$modes[strtoupper($ucs["mode"])]."\" width=64 height=64/></td>";
						$point .= "	<td><a href=\"{SITE_URL}?page=userucs&user=".sha1($db->GetFacebookID($ucs["uid"]))."\">".$ucs["username"]."</a></td>";
							
						$point .= "	<td class=\"text-center\">".$ucs["downcount"]."</td>";
						$point .= "	<td class=\"text-center\">".$ucs["viewcount"]."</td>";
						$point .= "	<td>".$ucs["date"]."</td>";
						$point .= "	<td class=\"text-center\"><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\"><span class=\"glyphicon glyphicon-eye-open\"></span></a></td>";
						$point .= "	<td class=\"text-center\"><a href=\"{SITE_URL}?page=api&cmd=getucsfile&id=".$ucs["id"]."\"><span class=\"glyphicon glyphicon-download-alt\"></span></a></td>";
						$point .= "</tr>";
							
						$ucspart .= $point;
						$c++;
					}
					$body	=	str_ireplace("{RANKINGS}",$ucspart,$body);
					$body	=	str_ireplace("{TITLE}","Last 20 sent UCS",$body);
					$ptitle =	"Last 20 sent UCS";
					break;
			case "userucs":
				if(false != ($udata = $db->GetUserDataSHA($_REQUEST["user"])) )	{
					$body		=	GetTPL("userucs");
					$ucsl		=	$db->GetUserUCS($udata["uid"]);
					$replacelist	=	array(
						"name"		=>	$udata["name"],
						"ucslist"	=>	"",
						"editth"	=>	"",
						"huid"		=>	$_REQUEST["user"]
					);
					//if($islogged && $_REQUEST["user"] == sha1($userdata["id"]))	{
					//	$replacelist["editth"] = "<th>Edit</th>";
					//}
					$modes = array(
						"SINGLE"			=>	"S",
						"SINGLEPERFORMANCE"	=>	"SP",
						"S-PERFORMANCE"		=>	"SP",
						"DOUBLE"			=>	"D",
						"DOUBLEPERFORMANCE"	=>	"DP",
						"D-PERFORMANCE"		=>	"DP"
					);
					foreach($ucsl as $ucs)	{
						$point =  "<tr>";
						$point .= " <td><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\"><img src=\"{SITE_URL}/ucs/img/".$ucs["ucsid"].".jpg\" height=64 border=0 /></a></td>\n";
						$point .= "	<td>".$ucs["ucsid"]."</td>\n";
						$point .= "	<td><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\">".$ucs["title"]."</a></td>\n";
						$point .= "	<td><img src=\"{SITE_URL}?page=api&cmd=genball&level=".$ucs["level"]."&mode=".$modes[strtoupper($ucs["mode"])]."\" width=64 height=64/></td>\n";
						$point .= "	<td>".$ucs["date"]."</td>";
						$point .= "	<td class=\"text-center\"><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\"><span class=\"glyphicon glyphicon-eye-open\"></span></a></td>\n";
						$point .= "	<td class=\"text-center\"><a href=\"{SITE_URL}?page=api&cmd=getucsfile&id=".$ucs["id"]."\"><span class=\"glyphicon glyphicon-download-alt\"></span></a></td>\n";
						$point .= "	<td>".$ucs["downcount"]."</td>\n";
						$point .= "	<td>".$ucs["viewcount"]."</td>\n";
						//if($islogged && $_REQUEST["user"] == sha1($userdata["id"]))
						//	$point .= "	<td><a href=\"{SITE_URL}?page=ucsedit&id=".$ucs["id"]."\"><span class=\"glyphicon glyphicon-edit\"></span> Edit</a></td>";
						$point .= "</tr>";
						
						$replacelist["ucslist"] .= $point;
					}
					foreach($replacelist as $key=>$val)
						$body	=	str_ireplace("{".$key."}",$val,$body);
					$ptitle =	$udata["name"]." UCS";
					break;
				}else{
					$body		=	GetTPL("problem");
					$body		=	str_ireplace("{PROBLEM_TITLE}","User not found!",$body);
					$body		=	str_ireplace("{MSG}","HUE? LOGIN OR I REPORT U HEUEAHUEHAUEHUAEH",$body);
					$ptitle 	=	"User not found!";
					break;
				}
			case "ucsview":
				if(!isset($_REQUEST["id"]))	{
					header("HTTP/1.0 404 Not Found");
					$body		=	GetTPL("problem");
					$body		=	str_ireplace("{PROBLEM_TITLE}","UCS not found!",$body);
					$body		=	str_ireplace("{MSG}","HUE? GIBE YOUR UCS PLOS OR I REPORT U HEUEAHUEHAUEHUAEH",$body);
					$ptitle 	=	"UCS not found";
				}else if(false == ($ucs 	=	$db->GetUCS($_REQUEST["id"])))	{
					header("HTTP/1.0 404 Not Found");
					$body		=	GetTPL("problem");
					$body		=	str_ireplace("{PROBLEM_TITLE}","UCS not found!",$body);
					$body		=	str_ireplace("{MSG}","HUE? GIBE YOUR UCS PLOS OR I REPORT U HEUEAHUEHAUEHUAEH",$body);
					$ptitle 	=	"UCS not found";
				}else{
					$body			=	GetTPL("ucsview");
					$modes = array(
						"SINGLE"			=>	"S",
						"SINGLEPERFORMANCE"	=>	"SP",
						"S-PERFORMANCE"		=>	"SP",
						"DOUBLE"			=>	"D",
						"DOUBLEPERFORMANCE"	=>	"DP",
						"D-PERFORMANCE"		=>	"DP"
					);
					$replacelist	=	array(
						"username"		=>	$ucs["username"],
						"name"			=>	$ucs["name"],
						"title"			=>	$ucs["title"],
						"filename"		=>	$ucs["filename"],
						"level"			=>	$ucs["level"],
						"date"			=>	$ucs["date"],
						"mode"			=>	$modes[strtoupper($ucs["mode"])],
						"ucs_id"		=>	$_REQUEST["id"],
						"song_id"		=>	$ucs["ucsid"],
						"downcount"		=>	$ucs["downcount"],
						"viewcount"		=>	$ucs["viewcount"]+1,
						"disable_webgl"	=>	"false",
						"huid"			=>	sha1($ucs["fbid"])
					);
					if(isset($_REQUEST["disable_webgl"]))
						$replacelist["disable_webgl"] = "true";
					foreach($replacelist as $key=>$val)
						$body	=	str_ireplace("{".$key."}",$val,$body);
					$db->IncUCSViewCount($_REQUEST["id"]);
					$ptitle 	=	$ucs["title"]." - ".$modes[strtoupper($ucs["mode"])].($ucs["level"]<0?"!!":($ucs["level"]==50?"??":$ucs["level"]))." by ".$ucs["name"];
					$pimg		=	$siteurl."ucs/img/".$ucs["ucsid"].".jpg";
					$pimgsize["width"]	=	640;
					$pimgsize["height"]	=	480;
				}
				
				break;
			case "upprof":
				$body	=	GetTPL("upprof");
				$ptitle =	"Profile Upload";
				break;
			case "userprofile":
				$body	=	GetTPL("uprofile");
				$body	=	str_ireplace("{NAME}",$userdata["name"],$body);
				$ptitle	=	"Your Profile";
				break;
			case "selector":
				$body	=	GetTPL("selector");
				$body	=	str_ireplace("{USER_ID}",addslashes($_REQUEST["userid"]),$body);
				$ptitle =	"PIU Visual Selector";
				break;
			case "api":
				$ptitle 	=	"API";
				if(isset($_REQUEST["cmd"]))	{
					switch($_REQUEST["cmd"])	{
						case "ucs":
							if(isset($_REQUEST["id"]))	{
								$ucs = $db->GetUCS($_REQUEST["id"]);
								if($ucs)	
									$result	=	array("status"=>"OK","data"=>$ucs);
								else
									$result = array("status"=>"NOT_FOUND");
							}else
								$result = array("status"=>"NOT_FOUND");
							break;
						case "genball":
							header ("Content-type: image/png");
							$g = new F2Gen("./img/");
							if(isset($_REQUEST["level"]) && isset($_REQUEST["mode"]))	{
								imagepng($g->GenerateBall($_REQUEST["mode"],$_REQUEST["level"]));
								exit(0);
							}else{
								imagepng($g->GenerateDummy());
								exit(0);								
							}
							break;
						case "exptop100":
							$result = array("status"=>"OK","data"=>$db->GetArcadeEXPTop100());
							break;
						case "scoretop100":
							$result = array("status"=>"OK","data"=>$db->GetArcadeScoreTop100());
							break;
						case "caloriestop100":
							$result = array("status"=>"OK","data"=>$db->GetArcadeCaloriesTop100());
							break;
						case "missiontop100":
							$result = array("status"=>"OK","data"=>$db->GetArcadeMissionsTop100());
							break;
						case "mostplayedsongs":
							$data = $db->GetMostPlayedSongs();
							$db->LoadSongList("songlist.csv");
							$c=1;
							$output = array();
							foreach($data as $key=>$rank)	{
								$songdata = $db->GetSongData(strtoupper(dechex($rank["songid"])));
								$output[] = array(
									"place"			=>	$c,
									"songid"		=>	strtoupper(dechex($rank["songid"])),
									"name"			=>	$songdata["Name0"],
									"artist"		=>	$songdata["Artist0"],
									"totalplays"	=>	$rank["totalc"]			
								);
								$c++;
							}
							$result = array("status"=>"OK","data"=>$output);
							break;
						case "mostplayedcharts":
							$data = $db->GetMostPlayedCharts();
							$db->LoadSongList("songlist.csv");
							$c=1;
							$output = array();
							foreach($data as $key=>$rank)	{
								$songdata = $db->GetSongData(strtoupper(dechex($rank["songid"])));
								$output[] = array(
										"place"			=>	$c,
										"songid"		=>	strtoupper(dechex($rank["songid"])),
										"name"			=>	$songdata["Name0"],
										"artist"		=>	$songdata["Artist0"],
										"level"			=>	$rank["difficult"],
										"mode"			=>	$db->GetSongMode($rank["mode"]),
										"totalplays"	=>	$rank["totalc"]
								);
								$c++;
							}
							$result = array("status"=>"OK","data"=>$output);
							break;
						case "getucsfile":
							if(isset($_REQUEST["id"]))	{
								$ucs = $db->GetUCS($_REQUEST["id"]);
								if($ucs)	{
									$db->IncUCSDownCount($_REQUEST["id"]);
									$result	=	array("status"=>"OK","data"=>$ucs);
									$f = $tempdirname . "/".$ucs["ucsid"]. "-" . md5(microtime()) . mt_rand(100, 999) . ".ucs";
									$handle = fopen($f, "w");
									fwrite($handle, $ucs["data"]);
									fclose($handle);
										
									header('Content-Type: application/octet-stream');
									header('Content-Disposition: attachment; filename='.basename($ucs["ucsid"].".ucs"));
									header('Expires: 0');
									header('Cache-Control: must-revalidate');
									header('Pragma: public');
									header('Content-Length: ' . filesize($f));
									readfile($f);
									unlink($f);
									exit;
								}else{
									header("HTTP/1.0 404 Not Found");
									$result	=	GetTPL("problem");
									$result	=	str_ireplace("{PROBLEM_TITLE}","404 - UCS Not Found",$result	);
									$result	=	str_ireplace("{MSG}","HUE? GIBE YOUR UCS OR I REPORT U HEUEAHUEHAUEHUAEH",$result	);
									$ptitle =	"UCS not found";
								}
							}else{
								header("HTTP/1.0 404 Not Found");
								$result	=	GetTPL("problem");
								$result	=	str_ireplace("{PROBLEM_TITLE}","404 - Page Not Found",$result	);
								$result	=	str_ireplace("{MSG}","HUE? GIBE YOUR PAGE OR I REPORT U HEUEAHUEHAUEHUAEH",$result	);
								$ptitle =	"404 - Page not found";
							}
							break;
						case "profile":
							$udata = $db->GetUData($_REQUEST["userid"]);
							if($udata)	
								$result	=	array("status"=>"OK","data"=>$udata);
							else
								$result =	array("status"=>"ERROR","code"=>5,"msg"=>"No such profile name ".$_REQUEST["userid"]);
							break;
						case "savedecode":		//	Save the data, decode and return JSON
							// if at least one of the fields was left blank
							if(empty($_FILES["f2_save"]["name"]) || empty($_FILES["f2_rank"]["name"]))
								$result	=	array("status"=>"ERROR","code"=>1, "msg"=>"Please send both Fiesta 2 Profile Files (rank and save)");
								
							// if the upload failed
							if(($_FILES["f2_save"]["error"] != 0) || ($_FILES["f2_rank"]["error"] != 0))
								$result	=	array("status"=>"ERROR","code"=>2, "msg"=>"There was an error uploading the files. Try again.");
							
							// if the file sizes don't correspond to those of normal F2 saves
							if(($_FILES["f2_save"]["size"] != $lensave) || ($_FILES["f2_rank"]["size"] != $lenrank))
								$result	=	array("status"=>"ERROR","code"=>3, "msg"=>"The file sizes are invalid. Did you sent the files in correct order?");
							
							// temporary file names
							$tmpsavename = $tempdirname . "/" . md5(microtime()) . mt_rand(100, 999) . ".bin";
							$tmprankname = $tempdirname . "/" . md5(microtime()) . mt_rand(100, 999) . ".bin";
							
							// save both files temporarily
							move_uploaded_file($_FILES["f2_save"]["tmp_name"], $tmpsavename);
							move_uploaded_file($_FILES["f2_rank"]["tmp_name"], $tmprankname);
							
							$x = new F2Save($tmpsavename,$tmprankname);

							if(!$x->CheckSave())
								$result = array("status"=>"ERROR","code"=>4,"Invalid Files CRC");
							else{
								//$db->WriteSave($x);
								$data = $x->ToArray();
								$result = array("status"=>"OK","data"=>$data);
							}
							// delete temporary files
							unlink($tmpsavename);
							unlink($tmprankname);
							break;
						case "getsteplist":
							$db->LoadSongList("songlist.csv");
							$db->LoadChartList("steplist.csv");
							$db->FillStepList();
							$json = array();
							$typemask = array(
									0x02 => array("double","single"),
									0x01 => array("performance","")
									//0x10 => array("another",""),
									//0x20 => array("newsong",""),
									//0x40 => array("hidden","")
							);
							foreach($db->stepcharts as $song)	{
								if($song["ActiveMode"] != "0" && hexdec($song["ID"]) <= 16384 )  {
									$eye = "img/eye/".$song["ID"].".PNG";
									$preview = "img/preview/".$song["ID"].".jpg";	
									$jssong = array(
											"name" => $song["ID"],
											"songid" => $song["ID"],
											"songname" => $song["Name0"],
											"songartist" => $song["Artist0"],
											"bpm" => $song["BPM0"],
											"eye" => $eye,
											"previewimage" => $preview,
											"mission" => false ,
											"training" => false,
											"game" => $db->GetGame($song["GameVersion"]),
											"mode" => $db->GetSongActiveMode($song["ActiveMode"]),
											"levellist" => array()
									);		
									if($song["charts"] != null)	{	
										foreach($song["charts"] as $chart)  {
											if($chart["Level"]!="0" && $chart["StepchartType"] != "64")    {
												$jssong["eye"] = str_ireplace("{ID}",$chart["SongIndexOffset"],$jssong["eye"]);
												$jssong["previewimage"] = str_ireplace("{ID}",$chart["SongIndexOffset"],$jssong["previewimage"]);
												$jssong["songid"] = $chart["SongIndexOffset"];
												$type = "";
												$ctype = (int)$chart["StepchartType"];
												$level = ($chart["Level"]=="50")?"??":(int)$chart["Level"];
												foreach($typemask as $mask => $ntype)   {
													if($ctype & $mask)
														$type .= $ntype[0];
										 			else
														$type .= $ntype[1];
												}
												$mode = 0;
												switch($type)	{	// GAMBI, DAS BOAS
													case "single":				$mode 	= 	0;		break;
													case "double":				$mode 	= 	128; 	break;
													case "singleperformance":	$mode	=	64; 	break;
													case "doubleperformance":	$mode	=	192;	break;	
												}
												if(isset($_REQUEST["userid"]))
													$userscore = (int)$db->GetSongUserHighScore(hexdec($song["ID"]),$chart["Level"],$mode,$_REQUEST["userid"])["score"];
												else
													$userscore = 0;
												$machinescore = $db->GetSongHighScore(hexdec($song["ID"]),$chart["Level"],$mode);
												array_push($jssong["levellist"], array("level" => $level, "type" => $type, "reallevel" => (int)$chart["Level"],"myname" => $_REQUEST["userid"], "myscore" => $userscore, "machinename"=>($machinescore["user"]!==null)?$machinescore["user"]["name"]:"HUEBR TEAM", "machinescore" => (int)$machinescore["score"]));
											}	
										}	
									}	
									if(count($jssong["levellist"]) > 0)
										array_push($json, $jssong);		
								}	
							}
							$result = array("status"=>"OK","data"=>$json);
							break;
						default:
							$result = array("status"=>"NOCMD");
					}
				}else
					$result = array("status"=>"NOCMD");
				if(is_array($result))	
					echo json_encode($result);
				else{
					$base	=	str_ireplace("{BODY}",$result,$base);
					$base	=	str_ireplace("{MENU}",$menu,$base);
					$base	=	str_ireplace("{ALERTS}",$alerts,$base);
					$base = str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
					$base = str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
					if($islogged)	{
						$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
						$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
						$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
						$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
						$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
					}else{
						$base	=	str_ireplace("{LOGDATA}","",$base);
						$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
						$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
						$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
					}
					$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
					$base	=	str_ireplace("{VERSION}",$version,$base);
					$base	=	str_ireplace("{UPDATED}",$updated,$base);
					echo $base;
				}
				exit(0);
				break;
			default:
				header("HTTP/1.0 404 Not Found");
				$body	=	GetTPL("problem");
				$body	=	str_ireplace("{PROBLEM_TITLE}","404 - Page Not Found",$body);
				$body	=	str_ireplace("{MSG}","HUE? GIBE YOUR PAGE OR I REPORT U HEUEAHUEHAUEHUAEH",$body);
				$ptitle =	"404 - Page not found";
				break;
		}
	}else{	//	Main Page
		$body 		=	GetTPL("main");
		$lastucs 	=	$db->GetLastUCS();
		$top100exp	=	$db->GetArcadeEXPTop100();
		$top100sco	=	$db->GetArcadeScoreTop100();
		$top100son	=	$db->GetMostPlayedSongs();

		$modes = array(
				"SINGLE"			=>	"S",
				"SINGLEPERFORMANCE"	=>	"SP",
				"S-PERFORMANCE"		=>	"SP",
				"DOUBLE"			=>	"D",
				"DOUBLEPERFORMANCE"	=>	"DP",
				"D-PERFORMANCE"		=>	"DP"
		);		
		$ucspart	=	"";
		foreach($lastucs as $key=>$ucs)	{
			$ucspart .= "\t\t<tr>\n";
			$ucspart .= "\t\t\t<td><img src=\"{SITE_URL}/ucs/img/".$ucs["ucsid"].".jpg\" height=32 /></td>\n";
			$ucspart .= "\t\t\t<td><a href=\"{SITE_URL}?page=ucsview&id=".$ucs["id"]."\">".$ucs["title"]."</a></td>\n";
			$ucspart .= "\t\t\t<td><img src=\"{SITE_URL}?page=api&cmd=genball&level=".$ucs["level"]."&mode=".$modes[strtoupper($ucs["mode"])]."\" width=32 height=32/></td>\n";
			$ucspart .= "\t\t\t<td><a href=\"{SITE_URL}?page=userucs&user=".sha1($db->GetFacebookID($ucs["uid"]))."\">".$ucs["username"]."</a></td>\n";
			$ucspart .= "\t\t\t<td>".$ucs["date"]."</td>\n";
			$ucspart .= "\t\t</tr>\n";
			if($key > 3)
				break;
		}
		$body	=	str_ireplace("{LAST_UCS}",$ucspart,$body);		

		$rkd = "";
		$c = 1;
		foreach($top100exp as $key=>$rank)	{
			if(!in_array($rank["name"],$blacklist))	{
				$rkd .= "\t\t<tr>\n";
				$rkd .= "\t\t\t\t<td>".$c."</td>\n";
				$rkd .= "\t\t\t\t<td><a href=\"{SITE_URL}?userid=".$rank["name"]."&page=rank\"><img src=\"{SITE_URL}img/avatar/CH_".str_pad($db->GetAvatarID($rank["avatar"]+1),3,'0', STR_PAD_LEFT).".PNG\" border=0 height=32/></a></td>\n";
				$rkd .= "\t\t\t\t<td><a href=\"{SITE_URL}?userid=".$rank["name"]."&page=rank\">".$rank["name"]."</a></td>\n";
				$rkd .= "\t\t\t\t<td>".$rank["level"]."</td>\n";
				$rkd .= "\t\t\t\t<td>".$rank["exp"]."</td>\n";
				$rkd .= "\t\t</tr>\n";
				$c++;
				if($c > 4)
					break;
			}				
		}
		$body	=	str_ireplace("{TOP_100_EXP}",$rkd,$body);	
		
		$rkd = "";
		$c = 1;
		foreach($top100sco as $key=>$rank)	{
			if(!in_array($rank["name"],$blacklist))	{
				$rkd .= "\t\t<tr>\n";
				$rkd .= "\t\t\t\t<td>".$c."</td>\n";
				$rkd .= "\t\t\t\t<td><a href=\"{SITE_URL}?userid=".$rank["name"]."&page=rank\"><img src=\"{SITE_URL}img/avatar/CH_".str_pad($db->GetAvatarID($rank["avatar"]+1),3,'0', STR_PAD_LEFT).".PNG\" border=0 height=32/></a></td>\n";
				$rkd .= "\t\t\t\t<td><a href=\"{SITE_URL}?userid=".$rank["name"]."&page=rank\">".$rank["name"]."</a></td>\n";
				$rkd .= "\t\t\t\t<td>".$rank["level"]."</td>\n";
				$rkd .= "\t\t\t\t<td>".$rank["score"]."</td>\n";
				$rkd .= "\t\t</tr>\n";
				$c++;
				if($c > 4)
					break;
			}
		}
		$body	=	str_ireplace("{TOP_100_SCORE}",$rkd,$body);
		
		$g = new F2Gen("./img/");
		$db->LoadSongList("songlist.csv");
		$rkd = "";
		$c=1;
		foreach($top100son as $key=>$rank)	{
			$songdata = $db->GetSongData(strtoupper(dechex($rank["songid"])));
			$rkd .= "\t\t\t<tr>\n";
			$rkd .= "\t\t\t\t<td><B>".($c)."</B></td>\n";
			if($g->CheckTitle(strtoupper(dechex($rank["songid"]))))
				$rkd .= "\t\t\t\t<td><img src=\"{SITE_URL}img/titles/".strtoupper(dechex($rank["songid"])).".jpg\" height=32/></td>\n";
			else
				$rkd .= "\t\t\t\t<td><img src=\"{SITE_URL}img/titles/notitle.jpg\" height=32/></td>\n";
			$rkd .= "\t\t\t\t<td>".$songdata["Artist0"]." - ".$songdata["Name0"]."</a></td>\n";
			$rkd .= "\t\t\t\t<td>".$rank["totalc"]."</td>\n";
			$rkd .= "\t\t\t</tr>\n";
			$c++;
			if($c > 4)
				break;
		}
		$body	=	str_ireplace("{MOST_PLAYED_SONGS}",$rkd,$body);
	}
	$base	=	str_ireplace("{BODY}",$body,$base);
	$base	=	str_ireplace("{MENU}",$menu,$base);
	$base	=	str_ireplace("{ALERTS}",$alerts,$base);
	if($islogged)	{
		$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
		$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
		$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
		$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
		$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
		$base	=	str_ireplace("{HASH_UID}",sha1($userdata["id"]),$base);
	}else{
		$base	=	str_ireplace("{LOGDATA}","",$base);
		$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
		$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
		$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
	}
	$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
	$base	=	str_ireplace("{VERSION}",$version,$base);
	$base	=	str_ireplace("{UPDATED}",$updated,$base);
	$base	=	str_ireplace("{PTITLE}",$ptitle,$base);
	$base	=	str_ireplace("{PDESC}",$pdesc,$base);
	$base	=	str_ireplace("{PIMG}",$pimg,$base);
	$base	=	str_ireplace("{PIMG_W}",$pimgsize["width"],$base);
	$base	=	str_ireplace("{PIMG_H}",$pimgsize["height"],$base);
	$base 	= 	str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
	$base 	= 	str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
	echo $base;
}
else {
	if(!isset($_FILES["ucs"]))	{	//	Save Files
		// if at least one of the fields was left blank
		if(empty($_FILES["save"]["name"]) || empty($_FILES["rank"]["name"])){
			$base = GetTPL("base");
			$menu = GetTPL("menu");
			$alerts = "";
			
			$body	=	GetTPL("problem");
			$body	=	str_ireplace("{PROBLEM_TITLE}","Missing files",$body);
			$body	=	str_ireplace("{MSG}","Please upload both files (fiesta2_rank.bin and fiesta2_save.bin)",$body);
		
			$base	=	str_ireplace("{BODY}",$body,$base);
			$base	=	str_ireplace("{MENU}",$menu,$base);
			$base	=	str_ireplace("{ALERTS}",$alerts,$base);
			$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
			$base	=	str_ireplace("{VERSION}",$version,$base);
			$base	=	str_ireplace("{UPDATED}",$updated,$base);
			if($islogged)	{
				$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
				$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
				$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
				$base	=	str_ireplace("{HASH_UID}",sha1($userdata["id"]),$base);
			}else{
				$base	=	str_ireplace("{LOGDATA}","",$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
				$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
			}
			$base	=	str_ireplace("{PTITLE}",$ptitle,$base);
			$base	=	str_ireplace("{PDESC}",$pdesc,$base);
			$base	=	str_ireplace("{PIMG}",$pimg,$base);
			$base	=	str_ireplace("{PIMG_W}",$pimgsize["width"],$base);
			$base	=	str_ireplace("{PIMG_H}",$pimgsize["height"],$base);
			$base 	= 	str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
			$base 	= 	str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
			echo $base;
			exit(0);
		}
		// if the upload failed
		if(($_FILES["save"]["error"] != 0) || ($_FILES["rank"]["error"] != 0))	{
			$base = GetTPL("base");
			$menu = GetTPL("menu");
			$alerts = "";
			
			$body	=	GetTPL("problem");
			$body	=	str_ireplace("{PROBLEM_TITLE}","Error uploading files",$body);
			$body	=	str_ireplace("{MSG}","There was some error when uploading files. Try again.",$body);
			
			$base	=	str_ireplace("{BODY}",$body,$base);
			$base	=	str_ireplace("{MENU}",$menu,$base);
			$base	=	str_ireplace("{ALERTS}",$alerts,$base);
			$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
			$base	=	str_ireplace("{VERSION}",$version,$base);
			$base	=	str_ireplace("{UPDATED}",$updated,$base);
			if($islogged)	{
				$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
				$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
				$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
				$base	=	str_ireplace("{HASH_UID}",sha1($userdata["id"]),$base);
			}else{
				$base	=	str_ireplace("{LOGDATA}","",$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
				$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
			}
			$base	=	str_ireplace("{PTITLE}",$ptitle,$base);
			$base	=	str_ireplace("{PDESC}",$pdesc,$base);
			$base	=	str_ireplace("{PIMG}",$pimg,$base);
			$base	=	str_ireplace("{PIMG_W}",$pimgsize["width"],$base);
			$base	=	str_ireplace("{PIMG_H}",$pimgsize["height"],$base);
			$base 	= 	str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
			$base 	= 	str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
			echo $base;
			exit(0);
		}
	
		// if the file sizes don't correspond to those of normal F2 saves
		if(($_FILES["save"]["size"] != $lensave) || ($_FILES["rank"]["size"] != $lenrank))	{
			$base = GetTPL("base");
			$menu = GetTPL("menu");
			$alerts = "";
			
			$body	=	GetTPL("problem");
			$body	=	str_ireplace("{PROBLEM_TITLE}","Incorrect file sizes.",$body);
			$body	=	str_ireplace("{MSG}","Incorrect file sizes. Did you mix them up?",$body);
	
			$base	=	str_ireplace("{BODY}",$body,$base);
			$base	=	str_ireplace("{MENU}",$menu,$base);
			$base	=	str_ireplace("{ALERTS}",$alerts,$base);
			$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
			$base	=	str_ireplace("{VERSION}",$version,$base);
			$base	=	str_ireplace("{UPDATED}",$updated,$base);
			if($islogged)	{
				$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
				$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
				$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
				$base	=	str_ireplace("{HASH_UID}",sha1($userdata["id"]),$base);
			}else{
				$base	=	str_ireplace("{LOGDATA}","",$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
				$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
			}
			$base	=	str_ireplace("{PTITLE}",$ptitle,$base);
			$base	=	str_ireplace("{PDESC}",$pdesc,$base);
			$base	=	str_ireplace("{PIMG}",$pimg,$base);
			$base	=	str_ireplace("{PIMG_W}",$pimgsize["width"],$base);
			$base	=	str_ireplace("{PIMG_H}",$pimgsize["height"],$base);
			$base 	= 	str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
			$base 	= 	str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
			echo $base;
			exit(0);
		}
	
		// temporary file names
		$tmpsavename = $tempdirname . "/" . md5(microtime()) . mt_rand(100, 999) . ".bin";
		$tmprankname = $tempdirname . "/" . md5(microtime()) . mt_rand(100, 999) . ".bin";
	
		// save both files temporarily
		move_uploaded_file($_FILES["save"]["tmp_name"], $tmpsavename);
		move_uploaded_file($_FILES["rank"]["tmp_name"], $tmprankname);
	
		
		$x = new F2Save($tmpsavename,$tmprankname);
		
		
		if($islogged)	{	//	Make a profile backup if is logged in
			if(!file_exists($backupdir) && !is_dir($backupdir)) {
				mkdir($backupdir);
				touch($backupdir . "/" . "index.html");
			}
			$userdir = $backupdir."/".sha1($userdata["id"]);
			if(!file_exists($userdir) && !is_dir($userdir)) {
				mkdir($userdir);
			}
			$profiledir = $userdir."/".$x->playerid;
			if(!file_exists($profiledir) && !is_dir($profiledir)) {
				mkdir($profiledir);
			}		
			copy($tmpsavename,$profiledir."/fiesta2_save.bin");
			copy($tmprankname,$profiledir."/fiesta2_rank.bin");
		}
		
		if($x->CheckSave())	{
			$db->WriteSave($x);
			if($islogged)	
				$db->AddUserProfile($userdata["uid"],$x->playerid);
			header("Location: ".$siteurl."/?page=rank&userid=".$x->playerid);
		}else{
			$base = GetTPL("base");
			$menu = GetTPL("menu");
			$alerts = "";
			
			$body	=	GetTPL("problem");
			$body	=	str_ireplace("{PROBLEM_TITLE}","Corrupted profile",$body);
			$body	=	str_ireplace("{MSG}","Your profile data is corrupted!",$body);	

			$base	=	str_ireplace("{BODY}",$body,$base);
			$base	=	str_ireplace("{MENU}",$menu,$base);
			$base	=	str_ireplace("{ALERTS}",$alerts,$base);
			$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
			$base	=	str_ireplace("{VERSION}",$version,$base);
			$base	=	str_ireplace("{UPDATED}",$updated,$base);
			$base = str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
			$base = str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
			if($islogged)	{
				$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
				$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
				$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
				$base	=	str_ireplace("{HASH_UID}",sha1($userdata["id"]),$base);
			}else{
				$base	=	str_ireplace("{LOGDATA}","",$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
				$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
			}
			$base	=	str_ireplace("{PTITLE}",$ptitle,$base);
			$base	=	str_ireplace("{PDESC}",$pdesc,$base);
			$base	=	str_ireplace("{PIMG}",$pimg,$base);
			$base	=	str_ireplace("{PIMG_W}",$pimgsize["width"],$base);
			$base	=	str_ireplace("{PIMG_H}",$pimgsize["height"],$base);
			$base 	= 	str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
			$base 	= 	str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
			if($islogged)	{
				$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
			}else{
				$base	=	str_ireplace("{LOGDATA}","",$base);
			}
			echo $base;
		}
		// delete temporary files
		unlink($tmpsavename);
		unlink($tmprankname);
	}else{ // UCS
		// if at least one of the fields was left blank
		if(empty($_FILES["ucs"]["name"])){
			$base = GetTPL("base");
			$menu = GetTPL("menu");
			$alerts = "";
				
			$body	=	GetTPL("problem");
			$body	=	str_ireplace("{PROBLEM_TITLE}","Missing files",$body);
			$body	=	str_ireplace("{MSG}","Please upload ucs file",$body);

			$base	=	str_ireplace("{BODY}",$body,$base);
			$base	=	str_ireplace("{MENU}",$menu,$base);
			$base	=	str_ireplace("{ALERTS}",$alerts,$base);
			$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
			$base	=	str_ireplace("{VERSION}",$version,$base);
			$base	=	str_ireplace("{UPDATED}",$updated,$base);
			if($islogged)	{
				$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
				$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
				$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
				$base	=	str_ireplace("{HASH_UID}",sha1($userdata["id"]),$base);
			}else{
				$base	=	str_ireplace("{LOGDATA}","",$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
				$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
			}
			$base	=	str_ireplace("{PTITLE}",$ptitle,$base);
			$base	=	str_ireplace("{PDESC}",$pdesc,$base);
			$base	=	str_ireplace("{PIMG}",$pimg,$base);
			$base	=	str_ireplace("{PIMG_W}",$pimgsize["width"],$base);
			$base	=	str_ireplace("{PIMG_H}",$pimgsize["height"],$base);
			$base 	= 	str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
			$base 	= 	str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
			echo $base;
			exit(0);
		}
		// if the upload failed
		if(($_FILES["ucs"]["error"] != 0))	{
			$base = GetTPL("base");
			$menu = GetTPL("menu");
			$alerts = "";
				
			$body	=	GetTPL("problem");
			$body	=	str_ireplace("{PROBLEM_TITLE}","Error uploading files",$body);
			$body	=	str_ireplace("{MSG}","There was some error when uploading files. Try again.",$body);

			$base	=	str_ireplace("{BODY}",$body,$base);
			$base	=	str_ireplace("{MENU}",$menu,$base);
			$base	=	str_ireplace("{ALERTS}",$alerts,$base);
			$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
			$base	=	str_ireplace("{VERSION}",$version,$base);
			$base	=	str_ireplace("{UPDATED}",$updated,$base);
			if($islogged)	{
				$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
				$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
				$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
				$base	=	str_ireplace("{HASH_UID}",sha1($userdata["id"]),$base);
			}else{
				$base	=	str_ireplace("{LOGDATA}","",$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
				$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
			}
			$base	=	str_ireplace("{PTITLE}",$ptitle,$base);
			$base	=	str_ireplace("{PDESC}",$pdesc,$base);
			$base	=	str_ireplace("{PIMG}",$pimg,$base);
			$base	=	str_ireplace("{PIMG_W}",$pimgsize["width"],$base);
			$base	=	str_ireplace("{PIMG_H}",$pimgsize["height"],$base);
			$base 	= 	str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
			$base 	= 	str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
			echo $base;
			exit(0);
		}
		
		if(($_FILES["ucs"]["size"] > $ucssizelimit))	{
			$base = GetTPL("base");
			$menu = GetTPL("menu");
			$alerts = "";
				
			$body	=	GetTPL("problem");
			$body	=	str_ireplace("{PROBLEM_TITLE}","Incorrect file sizes.",$body);
			$body	=	str_ireplace("{MSG}","Your ucs file is too big!",$body);
				
			$base	=	str_ireplace("{BODY}",$body,$base);
			$base	=	str_ireplace("{MENU}",$menu,$base);
			$base	=	str_ireplace("{ALERTS}",$alerts,$base);
			$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
			$base	=	str_ireplace("{VERSION}",$version,$base);
			$base	=	str_ireplace("{UPDATED}",$updated,$base);
			if($islogged)	{
				$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
				$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
				$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
				$base	=	str_ireplace("{HASH_UID}",sha1($userdata["id"]),$base);
			}else{
				$base	=	str_ireplace("{LOGDATA}","",$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
				$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
			}
			$base	=	str_ireplace("{PTITLE}",$ptitle,$base);
			$base	=	str_ireplace("{PDESC}",$pdesc,$base);
			$base	=	str_ireplace("{PIMG}",$pimg,$base);
			$base	=	str_ireplace("{PIMG_W}",$pimgsize["width"],$base);
			$base	=	str_ireplace("{PIMG_H}",$pimgsize["height"],$base);
			$base 	= 	str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
			$base 	= 	str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
			echo $base;
			exit(0);
		}
		
		if((substr($_FILES["ucs"]["name"],0,2) != "CS") || strlen($_FILES["ucs"]["name"]) < 5)	{
			$base = GetTPL("base");
			$menu = GetTPL("menu");
			$alerts = "";
			
			$body	=	GetTPL("problem");
			$body	=	str_ireplace("{PROBLEM_TITLE}","Wrong UCS Name.",$body);
			$body	=	str_ireplace("{MSG}","Your ucs filename should have at least CSXXX at beginning!",$body);

			$base	=	str_ireplace("{BODY}",$body,$base);
			$base	=	str_ireplace("{MENU}",$menu,$base);
			$base	=	str_ireplace("{ALERTS}",$alerts,$base);
			$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
			$base	=	str_ireplace("{VERSION}",$version,$base);
			$base	=	str_ireplace("{UPDATED}",$updated,$base);
			
			if($islogged)	{
				$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
				$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
				$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
				$base	=	str_ireplace("{HASH_UID}",sha1($userdata["id"]),$base);
			}else{
				$base	=	str_ireplace("{LOGDATA}","",$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
				$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
			}
			$base	=	str_ireplace("{PTITLE}",$ptitle,$base);
			$base	=	str_ireplace("{PDESC}",$pdesc,$base);
			$base	=	str_ireplace("{PIMG}",$pimg,$base);
			$base	=	str_ireplace("{PIMG_W}",$pimgsize["width"],$base);
			$base	=	str_ireplace("{PIMG_H}",$pimgsize["height"],$base);
			$base 	= 	str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
			$base 	= 	str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
			echo $base;
			exit(0);			
		}
		$ucsname = substr($_FILES["ucs"]["name"],0,5);
		$original_name = $_FILES["ucs"]["name"];
		// temporary file names
		$tmpucsfile = $tempdirname . "/" . md5(microtime()) . mt_rand(100, 999) . ".ucs";		
		move_uploaded_file($_FILES["ucs"]["tmp_name"], $tmpucsfile);
		$ucsdata = file_get_contents ( $tmpucsfile );

		if(substr($ucsdata,0,7) != ":Format" && substr($ucsdata,0,7) != ":format" )	{
			$base = GetTPL("base");
			$menu = GetTPL("menu");
			$alerts = "";
				
			$body	=	GetTPL("problem");
			$body	=	str_ireplace("{PROBLEM_TITLE}","Invalid UCS File.",$body);
			$body	=	str_ireplace("{MSG}","Your ucs file doesnt appear to be valid! Code 1",$body);
		
			$base	=	str_ireplace("{BODY}",$body,$base);
			$base	=	str_ireplace("{MENU}",$menu,$base);
			$base	=	str_ireplace("{ALERTS}",$alerts,$base);
			$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
			$base	=	str_ireplace("{VERSION}",$version,$base);
			$base	=	str_ireplace("{UPDATED}",$updated,$base);
			
			if($islogged)	{
				$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
				$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
				$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
				$base	=	str_ireplace("{HASH_UID}",sha1($userdata["id"]),$base);
			}else{
				$base	=	str_ireplace("{LOGDATA}","",$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
				$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
			}
			$base	=	str_ireplace("{PTITLE}",$ptitle,$base);
			$base	=	str_ireplace("{PDESC}",$pdesc,$base);
			$base	=	str_ireplace("{PIMG}",$pimg,$base);
			$base	=	str_ireplace("{PIMG_W}",$pimgsize["width"],$base);
			$base	=	str_ireplace("{PIMG_H}",$pimgsize["height"],$base);
			$base 	= 	str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
			$base 	= 	str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
			echo $base;
			exit(0);			
		}
		if($islogged)
			$userid = $userdata["uid"];
		else
			$userid = 0;
		
		$ok = true;
		$c = 0;
		$tmp = explode ( "\n", $ucsdata,  12 );
		$mode = "HUE_FAIL";
		while(true)	{
			if(substr($tmp[$c],0,5) == ":Mode")	{
				$data = explode("=",$tmp[$c]);
				$mode = $data[1];
				break;
			}
			$c++;
			if($c > 10)	{
				$ok = false;
				break;	
			}
		}
		
		if(!$ok)	{
			$base = GetTPL("base");
			$menu = GetTPL("menu");
			$alerts = "";
			
			$body	=	GetTPL("problem");
			$body	=	str_ireplace("{PROBLEM_TITLE}","Invalid UCS File.",$body);
			$body	=	str_ireplace("{MSG}","Your ucs file doesnt appear to be valid! Code 2",$body);
		
			$base	=	str_ireplace("{BODY}",$body,$base);
			$base	=	str_ireplace("{MENU}",$menu,$base);
			$base	=	str_ireplace("{ALERTS}",$alerts,$base);
			$base	=	str_ireplace("{SITE_URL}",$siteurl,$base);
			$base	=	str_ireplace("{VERSION}",$version,$base);
			$base	=	str_ireplace("{UPDATED}",$updated,$base);
			if($islogged)	{
				$base	=	str_ireplace("{LOGDATA}","Welcome ".$userdata["name"],$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're logged in! That means you will have a profile backup :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're logged in! That means you will have a list of your sent UCS files!",$base);
				$base	=	str_ireplace("{USER_MENU}",GetTPL("usermenu"),$base);
				$base	=	str_ireplace("{LOGGED_USER}",$userdata["name"],$base);
				$base	=	str_ireplace("{HASH_UID}",sha1($userdata["id"]),$base);
			}else{
				$base	=	str_ireplace("{LOGDATA}","",$base);
				$base	=	str_ireplace("{PROFILE_MESSAGE}","You're not logged in! Login to make profile backups! :D",$base);
				$base	=	str_ireplace("{UCS_MESSAGE}","You're not logged in! Login to make a list of sent UCS Files! :D",$base);
				$base	=	str_ireplace("{USER_MENU}","<li><a href=\"{FBLOGIN_URL}\"><img src=\"{SITE_URL}img/FB-f-Logo__blue_29.png\" width=14/>&nbsp;&nbsp;{FBLOGIN_NAME}</a></li>",$base);
			}
			$base	=	str_ireplace("{PTITLE}",$ptitle,$base);
			$base	=	str_ireplace("{PDESC}",$pdesc,$base);
			$base	=	str_ireplace("{PIMG}",$pimg,$base);
			$base	=	str_ireplace("{PIMG_W}",$pimgsize["width"],$base);
			$base	=	str_ireplace("{PIMG_H}",$pimgsize["height"],$base);
			$base 	= 	str_ireplace("{FBLOGIN_URL}",$fbloginurl,$base);
			$base 	= 	str_ireplace("{FBLOGIN_NAME}",$fbloginname,$base);
			echo $base;
			exit(0);			
		}
		$regid = $db->AddUserCustomStep($userid, $_REQUEST["title"], $original_name, $_REQUEST["level"], $mode, $ucsdata, $ucsname);
		header("Location: ".$siteurl."?page=ucsview&id=".$regid);
		unlink($tmpucsfile);
		
	}
}

?>