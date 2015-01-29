<?
require("includes/facebook.php");

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

class F2FB  {
    public $facebook;
    public $user;
    private $isadmin = false;
    private $dir;
    private $pageid;
    
    function __construct($appid,$secret,$adminid=-1,$pageid=-1,$fbstuff="/tmp/")    {
        $this->facebook = new Facebook(array(
          'appId'  => $appid,
          'secret' => $secret,
          'allowSignedRequest' => false
          )); 
        $this->pageid   =   $pageid;
        $this->dir      =   $fbstuff;
        $this->user     =   $this->GetUser();
        $this->isadmin  =   $this->user == $adminid;
        if($this->isadmin)  {
            $this->UpdatePageToken();
        }
    }
    
    function GetUser()  {
        return $this->facebook->getUser();
    }

    function GetUserProfile()   {
        $this->user = $this->GetUser();
        if ($this->user) {
            try {
                return $this->facebook->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        }    
        return null;
    }
    
    function GetLoginURL()  {
        return $this->facebook->getLoginUrl(array(
          'scope'           =>  'email, publish_actions'
          ));
    }
    
    function UpdatePageToken()  {
        if($_SESSION["tokenupdated"] != 1)  {
            try {
                $this->pagetoken = $this->facebook->api('/'.$this->pageid.'/?fields=access_token')["access_token"];
                $handle = fopen($this->dir."/fbtkn_s", "w");
                fwrite($handle, $this->pagetoken);
                fclose($handle);
            } catch (FacebookApiException $e) {
                error_log($e);
            }
            $_SESSION["tokenupdated"] = true;
        }
    }

    function GetLogoutURL()  {
        return $this->facebook->getLogoutUrl();
    }
    
    function destroySession()   {
        $_SESSION["tokenupdate"] = false;
        unset($_SESSION["tokenupdate"]);
        session_unset();
        return $this->facebook->destroySession();   
    }
    
}
