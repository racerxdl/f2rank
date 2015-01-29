<?
require("includes/facebook.php");

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
