<?php

class socialnetworklinksUpdate extends socialnetworklinks {
    
    public static function _isCurl(){
        return function_exists('curl_version');
    }
    
    public static function version($version){
        $version=(int)str_replace(".","",$version);
        if (strlen($version)==3){$version=(int)$version."0";}
        if (strlen($version)==2){$version=(int)$version."00";}
        if (strlen($version)==1){$version=(int)$version."000";}
        if (strlen($version)==0){$version=(int)$version."0000";}
        return (int)$version;
    }
    
    public static function verify($module){
        if (self::_isCurl()){
            $ch = @curl_init("http://dev.mypresta.eu/update/get.php?module=$module");
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $actual_version = @curl_exec($ch); curl_close($ch);
        } else {
            if (ini_get("allow_url_fopen")) {
                if (function_exists("file_get_contents")){
                    $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module='.$module);
                }
            }
        }
        Configuration::updateValue("update_".$module,date(U));
        Configuration::updateValue("updatev_".$module,$actual_version); 
        return $actual_version;
    }
    
    public static function installation($key,$module){
        if (self::_isCurl()){
            $ch = curl_init("http://dev.mypresta.eu/lic/get.php?module=$module&lic=$key&u=".self::encrypt(_PS_BASE_URL_.__PS_BASE_URI__));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $actual_version = curl_exec($ch); curl_close($ch);
        } else {
            if (ini_get("allow_url_fopen")) {
                if (function_exists("file_get_contents")){
                    $actual_version = file_get_contents("http://dev.mypresta.eu/lic/get.php?module=$module&lic=$key&u=".self::encrypt(_PS_BASE_URL_.__PS_BASE_URI__,$key));
                }
            }
        }
        return true;    
    }
    
    public static function encrypt($string){
        return base64_encode($string);
    }
}

?>