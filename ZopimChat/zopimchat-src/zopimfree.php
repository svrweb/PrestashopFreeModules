<?php

class zopimfree extends Module {
	function __construct(){
		$this->name = 'zopimfree';
		$this->tab = 'front_office_features';
		$this->version = '1.4';
        $this->author= '';
        $this->dir = '/modules/zopimfree/';
		parent::__construct();
		$this->displayName = $this->l('Zopim Chat Free');
		$this->description = $this->l('Add Zopim Chat Widget to your website for free. An easiest and the best way to create awesome support chat tool.');
        $this->where = array (
        array('id'=>'0', 'name'=>$this->l('Old widget')),
        array('id'=>'1', 'name'=>$this->l('New beta widget'))
        );
        $this->mkey="nlc";       
        if (@file_exists('../modules/'.$this->name.'/key.php'))
            @require_once ('../modules/'.$this->name.'/key.php');
        else if (@file_exists(dirname(__FILE__) . $this->name.'/key.php'))
            @require_once (dirname(__FILE__) . $this->name.'/key.php');
        else if (@file_exists('modules/'.$this->name.'/key.php'))
            @require_once ('modules/'.$this->name.'/key.php');                        
        $this->checkforupdates();
	}
    
    function checkforupdates(){
            if (isset($_GET['controller']) OR isset($_GET['tab'])){
                if (Configuration::get('update_'.$this->name) < (date("U")>86400)){
                    $actual_version = zopimfreeUpdate::verify($this->name,$this->mkey,$this->version);
                }
                if (zopimfreeUpdate::version($this->version)<zopimfreeUpdate::version(Configuration::get('updatev_'.$this->name))){
                    $this->warning=$this->l('New version available, check http://MyPresta.eu for more informations');
                }
            }
    }    
    

	function install(){
        if (parent::install() == false
            OR !$this->registerHook('header')
            OR !Configuration::updateValue('zcf_widge_type',$this->l('0'))
            OR !Configuration::updateValue('zcf_lasttab',$this->l('1'))
            OR !Configuration::updateValue('zcf_widgetid',$this->l('Enter your zopim widget id here'))
        ){
            return false;
        }
	return true;
	}

    public function getconf(){
        $array['zcf_widgetid']=Configuration::get('zcf_widgetid');
        return $array;
    }
    
	public function psversion() {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
		return $exp[1];
	}
    
    public function advert(){
        return '<iframe src="//apps.facepages.eu/somestuff/whatsgoingon.html" width="100%" height="150" border="0" style="border:none;"></iframe>';
    }
    
    public function getContent(){
        $output="";
        if (Tools::isSubmit('selecttab')){
            Configuration::updateValue('zcf_lasttab',"$_POST[selecttab]");
        }
        if (Tools::isSubmit('submit_settings')){
            Configuration::updatevalue('zcf_widgetid',$_POST['zcf_widgetid']);
            Configuration::updatevalue('zcf_widget_type',$_POST['zcf_widget_type']);
            $output.='<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Settings Saved').'" />'.$this->l('Settings Saved').'</div>';
        }      
        return $output.$this->displayForm();
    }
        
	public function displayForm(){
	   $where="";
        foreach ($this->where AS $k=>$v){
            if (Configuration::get('zcf_widget_type')==$v['id']){
                $selected='selected';
            } else {
                $selected='';
            }
            $where.="<option $selected value=\"{$v['id']}\">{$v['name']}</option>";
        } 
	    $var=$this->getconf();
        $selected1="";
        $selected2="";
        if (Configuration::get('zcf_lasttab')==1){$selected1="active";}else{$selected1="";}
        if (Configuration::get('zcf_lasttab')==2){$selected2="active";}else{$selected2="";}
        
       
$cssforms='
<form name="selectform1" id="selectform1" action="'.$_SERVER['REQUEST_URI'].'" method="post"><input type="hidden" name="selecttab" value="1"></form>
<form name="selectform2" id="selectform2" action="'.$_SERVER['REQUEST_URI'].'" method="post"><input type="hidden" name="selecttab" value="2"></form>
'."
<style>
#cssmenu, #cssmenu ul, #cssmenu ul li, #cssmenu ul li a{
    padding: 0;
	margin: 0;
    margin-bottom:10px;
	font-family: 'arial', sans-serif;
}

#cssmenu:before, #cssmenu:after, #cssmenu > ul:before, #cssmenu > ul:after {
	content: '';
	display: table;
}


#cssmenu:after, #cssmenu > ul:after {
	clear: both;
}

#cssmenu {
	zoom:1;
	height: 69px;
	background: url(../modules/zopimfree/img/bottom-bg.png) repeat-x center bottom;
	border-radius: 2px;
}

#cssmenu ul{
	background: url(../modules/zopimfree/img/nav-bg.png) repeat-x 0px 4px;
	height: 69px;
}

#cssmenu ul li{
	float: left;
	list-style: none;
}

#cssmenu ul li a{
	display: block;
	height: 37px;
	padding: 22px 30px 0;
	margin: 4px 2px 0;
	border-radius: 2px 2px 0 0;
	text-decoration: none;
    font-weight:bold;
    font-size:16px;
	color: white;
	text-shadow: 0 1px 1px rgba(0, 0, 0, .75);
	opacity: .9;
}

#cssmenu ul li:first-child a{
	
	margin: 4px 2px 0 0;
	
}

#cssmenu ul li a:hover, #cssmenu ul li.active a{
	background: url(../modules/zopimfree/img/color.png) center bottom;
	display: block;
	height: 37px;
	margin-top: 0px;
	padding-top: 26px;
	color: #9b4106;
	text-shadow: 0 1px 1px rgba(255, 255, 255, .35);
	opacity: 1;
	
}
</style>
<div id='cssmenu'>
<ul>
   <li class='$selected1'><a href='#' onclick='selectform1.submit();'><span>Widget settings</span></a></li>
   <li class='$selected2'><a href='#' onclick='selectform2.submit();'><span>Dashboard</span></a></li>
   <li style='position:relative; display:inline-block; float:right; '><a href='http://mypresta.eu' target='_blank' title='prestashop modules'><img src='../modules/zopimfree/logo-white.png' alt='prestashop modules' style=\"position:absolute; top:17px; right:16px;\"/></a></li>
</ul>
</div>";

        if (Configuration::get('zcf_lasttab')=="1"){
        $form='<div id="module_block_settings">        
            <fieldset id="fieldset_module_block_settings">
                <legend style="display:inline-block;"><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
                <form action="'.$_SERVER['REQUEST_URI'].'" method="post">                             
                    <label>'.$this->l('Zopim Widget ID').'</label>
                    <div class="margin-form">
                        <input type="text" name="zcf_widgetid" value="'.$var['zcf_widgetid'].'" style="width:300px;"/>
                        <p class="clear">'.$this->l('Enter here your widget ID').' '.$this->l('Read now: ').' <a href="http://mypresta.eu/en/art/know-how/how-to-get-zopim-widget-id-for-our-free-prestashop-module.html" target="_blank" style="text-decoration:underline;">'.$this->l('how can i get Zopim Widget ID?').'</a></p>
                    </div>
                            <div style="display:block; clear:both; text-align:center; overflow:hidden;">
        		                <div style="display:block; clear:both; margin-top:20px;">
        							<label>'.$this->l('Widget type').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <select name="zcf_widget_type">'.$where.'</select>
        							</div>	
        		                </div>
        	               </div>                                                                                                                                    
                    <center><input type="submit" name="submit_settings" value="'.$this->l('Save Settings').'" class="button" /></center>
                </form>
            </fieldset>
            </div>';
        }
        
        if (Configuration::get('zcf_lasttab')=="2"){
            $form='<div style="width:100%; height:600px; "><iframe src="//dashboard.zopim.com" style="width:100%; height:100%; border:none;"></iframe></div>';
        }
        
        return $this->advert().$cssforms.$form.'<table><td>'.$this->l('follow us!').'</td><td><iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Ffacebook.com%2Fmypresta&amp;send=false&amp;layout=button_count&amp;width=120&amp;show_faces=true&amp;font=verdana&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=276212249177933" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe></td><td>'."<div class=\"g-follow\" data-annotation=\"bubble\" data-height=\"15\" data-href=\"//plus.google.com/116184657854665082523\" data-rel=\"publisher\"></div>
<script type=\"text/javascript\">
  window.___gcfg = {lang: 'en-GB'};
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>".'</td></table></div>';
	}    
	
	function hookheader($params){
        $var=$this->getconf();
        global $smarty;
        $smarty->assign('widgetid', $var['zcf_widgetid']);
        if (Configuration::get('zcf_widget_type')==1){
            return $this->display(__FILE__, 'header-new-widget.tpl');
        } else {
            return $this->display(__FILE__, 'header.tpl');
        }
	}
}


class zopimfreeUpdate extends zopimfree {  
    public static function version($version){
        $version=(int)str_replace(".","",$version);
        if (strlen($version)==3){$version=(int)$version."0";}
        if (strlen($version)==2){$version=(int)$version."00";}
        if (strlen($version)==1){$version=(int)$version."000";}
        if (strlen($version)==0){$version=(int)$version."0000";}
        return (int)$version;
    }
    
    public static function encrypt($string){
        return base64_encode($string);
    }
    
    public static function verify($module,$key,$version){
        if (ini_get("allow_url_fopen")) {
             if (function_exists("file_get_contents")){
                $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module='.$module."&version=".self::encrypt($version)."&lic=$key&u=".self::encrypt(_PS_BASE_URL_.__PS_BASE_URI__));
             }
        }
        Configuration::updateValue("update_".$module,date("U"));
        Configuration::updateValue("updatev_".$module,$actual_version); 
        return $actual_version;
    }
}
?>
