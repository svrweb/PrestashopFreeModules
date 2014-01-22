<?php 

class socialnetworklinks extends Module {
	function __construct(){
		$this->name = 'socialnetworklinks';
		$this->tab = 'social_networks';
        $this->author = 'MyPresta.eu';
		$this->version = '1.2.9';
		parent::__construct();
		$this->displayName = $this->l('Social Network Links');
		$this->description = $this->l('Module creates animated social network links');
        $this->where = array (
        array('id'=>'0', 'name'=>$this->l('Top of the page')),
        array('id'=>'1', 'name'=>$this->l('Left column')),
        array('id'=>'2', 'name'=>$this->l('Right column')),
        array('id'=>'3', 'name'=>$this->l('Footer'))
        );
        $this->mkey="freelicense";       
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
            if (Configuration::get('update_'.$this->name) < (date("U")-86400)){
                $actual_version = socialnetworklinksUpdate::verify($this->name,$this->mkey,$this->version);
            }
            if (socialnetworklinksUpdate::version($this->version)<socialnetworklinksUpdate::version(Configuration::get('updatev_'.$this->name))){
                $this->warning=$this->l('New version available, check http://MyPresta.eu for more informations');
            }
        }
    }
    
    
    
    
    function install(){
        if (parent::install() == false
        OR $this->registerHook('header') == false 
	    OR $this->registerHook('top') == false
	    OR $this->registerHook('leftColumn') == false
	    OR $this->registerHook('rightColumn') == false
	    OR $this->registerHook('footer') == false
	    OR $this->registerHook('home') == false
        OR Configuration::updateValue('update_'.$this->name,'0') == false
        OR Configuration::updateValue('snl_where', '1') == false
        OR Configuration::updateValue('snl_facebook', '1') == false
        OR Configuration::updateValue('snl_facebook_url', 'http://facebook.com/mypresta') == false
        OR Configuration::updateValue('snl_twitter', '1') == false
        OR Configuration::updateValue('snl_twitter_url', 'https://twitter.com/myprestaeu') == false
        OR Configuration::updateValue('snl_youtube', '1') == false
        OR Configuration::updateValue('snl_youtube_url', 'http://www.youtube.com/user/vekiapl') == false
        OR Configuration::updateValue('snl_google', '1') == false
        OR Configuration::updateValue('snl_google_url', 'https://plus.google.com/116184657854665082523/') == false
        OR Configuration::updateValue('snl_rss', '1') == false
        OR Configuration::updateValue('snl_rss_url', 'http://www.facebook.com/feeds/page.php?format=rss20&id=399888213399907') == false
        OR Configuration::updateValue('snl_instagram', '1') == false
        OR Configuration::updateValue('snl_instagram_url', 'http://instagram.com/mypresta') == false
        OR Configuration::updateValue('snl_vkontakte', '1') == false
        OR Configuration::updateValue('snl_vkontakte_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_odnru', '1') == false
        OR Configuration::updateValue('snl_odnru_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_nk', '1') == false
        OR Configuration::updateValue('snl_nk_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_flickr', '1') == false
        OR Configuration::updateValue('snl_flickr_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_linkedin', '1') == false
        OR Configuration::updateValue('snl_linkedin_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_pinterest', '1') == false
        OR Configuration::updateValue('snl_pinterest_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_myspace', '1') == false
        OR Configuration::updateValue('snl_myspace_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_lastfm', '1') == false
        OR Configuration::updateValue('snl_lastfm_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_yelp', '1') == false
        OR Configuration::updateValue('snl_yelp_url', 'http://mypresta.eu') == false
        
        OR Configuration::updateValue('snl_picsart', '1') == false
        OR Configuration::updateValue('snl_picsart_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_tumblr', '1') == false
        OR Configuration::updateValue('snl_tumblr_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_digg', '1') == false
        OR Configuration::updateValue('snl_digg_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_wordpress', '1') == false
        OR Configuration::updateValue('snl_wordpress_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_deviantart', '1') == false
        OR Configuration::updateValue('snl_deviantart_url', 'http://mypresta.eu') == false
        
        OR Configuration::updateValue('snl_weibo', '1') == false
        OR Configuration::updateValue('snl_weibo_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_qzone', '1') == false
        OR Configuration::updateValue('snl_qzone_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_formspring', '1') == false
        OR Configuration::updateValue('snl_formspring_url', 'http://mypresta.eu') == false
        
        OR Configuration::updateValue('snl_blogger', '1') == false
        OR Configuration::updateValue('snl_blogger_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_ljournal', '1') == false
        OR Configuration::updateValue('snl_ljournal_url', 'http://mypresta.eu') == false
        OR Configuration::updateValue('snl_ask', '1') == false
        OR Configuration::updateValue('snl_ask_url', 'http://mypresta.eu') == false
        
        OR Configuration::updateValue('snl_orkut', '1') == false
        OR Configuration::updateValue('snl_orkut_url', 'http://mypresta.eu') == false
        
        ){
            return false;
        }
        return true;
	}


    public function psversion() {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
		return $exp[1];
	}

    public function getvar(){
        $var=new StdClass();
        $var->snl_facebook=Configuration::get('snl_facebook');
        $var->snl_facebook_url=Configuration::get('snl_facebook_url');
        $var->snl_twitter=Configuration::get('snl_twitter');
        $var->snl_twitter_url=Configuration::get('snl_twitter_url');
        $var->snl_youtube=Configuration::get('snl_youtube');
        $var->snl_youtube_url=Configuration::get('snl_youtube_url');
        $var->snl_google=Configuration::get('snl_google');
        $var->snl_google_url=Configuration::get('snl_google_url');
        $var->snl_rss=Configuration::get('snl_rss');
        $var->snl_rss_url=Configuration::get('snl_rss_url');
        $var->snl_instagram=Configuration::get('snl_instagram');
        $var->snl_instagram_url=Configuration::get('snl_instagram_url');
        $var->snl_vkontakte=Configuration::get('snl_vkontakte');
        $var->snl_vkontakte_url=Configuration::get('snl_vkontakte_url');
        $var->snl_odnru=Configuration::get('snl_odnru');
        $var->snl_odnru_url=Configuration::get('snl_odnru_url');
        $var->snl_nk=Configuration::get('snl_nk');
        $var->snl_nk_url=Configuration::get('snl_nk_url');
        $var->snl_flickr=Configuration::get('snl_flickr');
        $var->snl_flickr_url=Configuration::get('snl_flickr_url');
        $var->snl_linkedin=Configuration::get('snl_linkedin');
        $var->snl_linkedin_url=Configuration::get('snl_linkedin_url');
        $var->snl_pinterest=Configuration::get('snl_pinterest');
        $var->snl_pinterest_url=Configuration::get('snl_pinterest_url');
        $var->snl_myspace=Configuration::get('snl_myspace');
        $var->snl_myspace_url=Configuration::get('snl_myspace_url');
        $var->snl_lastfm=Configuration::get('snl_lastfm');
        $var->snl_lastfm_url=Configuration::get('snl_lastfm_url');
        $var->snl_yelp=Configuration::get('snl_yelp');
        $var->snl_yelp_url=Configuration::get('snl_yelp_url');
        $var->snl_picsart=Configuration::get('snl_picsart');
        $var->snl_picsart_url=Configuration::get('snl_picsart_url');
        $var->snl_tumblr=Configuration::get('snl_tumblr');
        $var->snl_tumblr_url=Configuration::get('snl_tumblr_url');
        $var->snl_digg=Configuration::get('snl_digg');
        $var->snl_digg_url=Configuration::get('snl_digg_url');
        $var->snl_wordpress=Configuration::get('snl_wordpress');
        $var->snl_wordpress_url=Configuration::get('snl_wordpress_url');
        $var->snl_deviantart=Configuration::get('snl_deviantart');
        $var->snl_deviantart_url=Configuration::get('snl_deviantart_url');
        $var->snl_weibo=Configuration::get('snl_weibo');
        $var->snl_weibo_url=Configuration::get('snl_weibo_url');
        $var->snl_qzone=Configuration::get('snl_qzone');
        $var->snl_qzone_url=Configuration::get('snl_qzone_url');
        $var->snl_formspring=Configuration::get('snl_formspring');
        $var->snl_formspring_url=Configuration::get('snl_formspring_url');
        $var->snl_blogger=Configuration::get('snl_blogger');
        $var->snl_blogger_url=Configuration::get('snl_blogger_url');
        $var->snl_ljournal=Configuration::get('snl_ljournal');
        $var->snl_ljournal_url=Configuration::get('snl_ljournal_url');
        $var->snl_ask=Configuration::get('snl_ask');
        $var->snl_ask_url=Configuration::get('snl_ask_url');
        $var->snl_orkut=Configuration::get('snl_orkut');
        $var->snl_orkut_url=Configuration::get('snl_orkut_url');
        return $var;
    }
  
    public function getContent(){
        $output="";
        
        if (isset($_POST['module_settings'])){
            Configuration::updateValue('snl_where',Tools::getValue('snl_where'));
            Configuration::updateValue('snl_facebook',Tools::getValue('snl_facebook'));
            Configuration::updateValue('snl_facebook_url',Tools::getValue('snl_facebook_url'));
            Configuration::updateValue('snl_twitter',Tools::getValue('snl_twitter'));
            Configuration::updateValue('snl_twitter_url',Tools::getValue('snl_twitter_url'));
            Configuration::updateValue('snl_youtube',Tools::getValue('snl_youtube'));
            Configuration::updateValue('snl_youtube_url',Tools::getValue('snl_youtube_url'));
            Configuration::updateValue('snl_google',Tools::getValue('snl_google'));
            Configuration::updateValue('snl_google_url',Tools::getValue('snl_google_url'));
            Configuration::updateValue('snl_rss',Tools::getValue('snl_rss'));
            Configuration::updateValue('snl_rss_url',Tools::getValue('snl_rss_url'));
            Configuration::updateValue('snl_instagram',Tools::getValue('snl_instagram'));
            Configuration::updateValue('snl_instagram_url',Tools::getValue('snl_instagram_url'));
            Configuration::updateValue('snl_vkontakte',Tools::getValue('snl_vkontakte'));
            Configuration::updateValue('snl_vkontakte_url',Tools::getValue('snl_vkontakte_url'));
            Configuration::updateValue('snl_odnru',Tools::getValue('snl_odnru'));
            Configuration::updateValue('snl_odnru_url',Tools::getValue('snl_odnru_url'));
            Configuration::updateValue('snl_nk',Tools::getValue('snl_nk'));
            Configuration::updateValue('snl_nk_url',Tools::getValue('snl_nk_url'));
            Configuration::updateValue('snl_flickr',Tools::getValue('snl_flickr'));
            Configuration::updateValue('snl_flickr_url',Tools::getValue('snl_flickr_url'));
            Configuration::updateValue('snl_pinterest',Tools::getValue('snl_pinterest'));
            Configuration::updateValue('snl_pinterest_url',Tools::getValue('snl_pinterest_url'));
            Configuration::updateValue('snl_myspace',Tools::getValue('snl_myspace'));
            Configuration::updateValue('snl_myspace_url',Tools::getValue('snl_myspace_url'));
            Configuration::updateValue('snl_lastfm',Tools::getValue('snl_lastfm'));
            Configuration::updateValue('snl_lastfm_url',Tools::getValue('snl_lastfm_url'));
            Configuration::updateValue('snl_yelp',Tools::getValue('snl_yelp'));
            Configuration::updateValue('snl_yelp_url',Tools::getValue('snl_yelp_url'));
            Configuration::updateValue('snl_linkedin',Tools::getValue('snl_linkedin'));
            Configuration::updateValue('snl_linkedin_url',Tools::getValue('snl_linkedin_url'));
            Configuration::updateValue('snl_picsart',Tools::getValue('snl_picsart'));
            Configuration::updateValue('snl_picsart_url',Tools::getValue('snl_picsart_url'));
            Configuration::updateValue('snl_tumblr',Tools::getValue('snl_tumblr'));
            Configuration::updateValue('snl_tumblr_url',Tools::getValue('snl_tumblr_url'));
            Configuration::updateValue('snl_digg',Tools::getValue('snl_digg'));
            Configuration::updateValue('snl_digg_url',Tools::getValue('snl_digg_url'));
            Configuration::updateValue('snl_wordpress',Tools::getValue('snl_wordpress'));
            Configuration::updateValue('snl_wordpress_url',Tools::getValue('snl_wordpress_url'));
            Configuration::updateValue('snl_deviantart',Tools::getValue('snl_deviantart'));
            Configuration::updateValue('snl_deviantart_url',Tools::getValue('snl_deviantart_url'));
            Configuration::updateValue('snl_weibo',Tools::getValue('snl_weibo'));
            Configuration::updateValue('snl_weibo_url',Tools::getValue('snl_weibo_url'));
            Configuration::updateValue('snl_qzone',Tools::getValue('snl_qzone'));
            Configuration::updateValue('snl_qzone_url',Tools::getValue('snl_qzone_url'));
            Configuration::updateValue('snl_formspring',Tools::getValue('snl_formspring'));
            Configuration::updateValue('snl_formspring_url',Tools::getValue('snl_formspring_url'));
            Configuration::updateValue('snl_blogger',Tools::getValue('snl_blogger'));
            Configuration::updateValue('snl_blogger_url',Tools::getValue('snl_blogger_url'));
            Configuration::updateValue('snl_ljournal',Tools::getValue('snl_ljournal'));
            Configuration::updateValue('snl_ljournal_url',Tools::getValue('snl_ljournal_url'));
            Configuration::updateValue('snl_ask',Tools::getValue('snl_ask'));
            Configuration::updateValue('snl_ask_url',Tools::getValue('snl_ask_url'));
            Configuration::updateValue('snl_orkut',Tools::getValue('snl_orkut'));
            Configuration::updateValue('snl_orkut_url',Tools::getValue('snl_orkut_url'));
        }
        return $output.$this->displayForm();   
     }

        
    public function displayForm(){        
        $where="";
        foreach ($this->where AS $k=>$v){
            if (Configuration::get('snl_where')==$v['id']){
                $selected='selected';
            } else {
                $selected='';
            }
            $where.="<option $selected value=\"{$v['id']}\">{$v['name']}</option>";
        }
        
        $form='
        <iframe src="http://mypresta.eu/content/uploads/2012/10/facebook_advertise.html" width="100%" height="130" border="0" style="border:none;"></iframe>
        <form id="updateslideform" action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data" >
            <fieldset style="position:relative;">
			<legend>'.$this->l('Social network links').'</legend>
                            <div style="display:block; clear:both; text-align:center; overflow:hidden;">
        		                <div style="display:block; clear:both; margin-top:20px;">
        							<label>'.$this->l('Position').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <select name="snl_where">'.$where.'</select>
        							</div>	
        		                </div>
        	                </div>
                            <div style="display:block; clear:both; text-align:center; overflow:hidden;">
        		                <div style="display:block; clear:both; margin-top:20px;">
        							<label>'.$this->l('Facebook').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_facebook" value="1" '.(Configuration::get('snl_facebook')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_facebook_url" style="width:250px;" value="'.Configuration::get('snl_facebook_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Twitter').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_twitter" value="1" '.(Configuration::get('snl_twitter')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_twitter_url" style="width:250px;" value="'.Configuration::get('snl_twitter_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Youtube').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_youtube" value="1" '.(Configuration::get('snl_youtube')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_youtube_url" style="width:250px;" value="'.Configuration::get('snl_youtube_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Google').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_google" value="1" '.(Configuration::get('snl_google')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_google_url" style="width:250px;" value="'.Configuration::get('snl_google_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('LinkedIn').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_linkedin" value="1" '.(Configuration::get('snl_linkedin')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_linkedin_url" style="width:250px;" value="'.Configuration::get('snl_linkedin_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('RSS Feed').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_rss" value="1" '.(Configuration::get('snl_rss')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_rss_url" style="width:250px;" value="'.Configuration::get('snl_rss_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Instagram').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_instagram" value="1" '.(Configuration::get('snl_instagram')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_instagram_url" style="width:250px;" value="'.Configuration::get('snl_instagram_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Flickr').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_flickr" value="1" '.(Configuration::get('snl_flickr')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_flickr_url" style="width:250px;" value="'.Configuration::get('snl_flickr_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Vkontakte').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_vkontakte" value="1" '.(Configuration::get('snl_vkontakte')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_vkontakte_url" style="width:250px;" value="'.Configuration::get('snl_vkontakte_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Odnaklassniki').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_odnru" value="1" '.(Configuration::get('snl_odnru')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_odnru_url" style="width:250px;" value="'.Configuration::get('snl_odnru_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Nasza Klasa').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_nk" value="1" '.(Configuration::get('snl_nk')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_nk_url" style="width:250px;" value="'.Configuration::get('snl_nk_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Pinterest').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_pinterest" value="1" '.(Configuration::get('snl_pinterest')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_pinterest_url" style="width:250px;" value="'.Configuration::get('snl_pinterest_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('MySpace').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_myspace" value="1" '.(Configuration::get('snl_myspace')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_myspace_url" style="width:250px;" value="'.Configuration::get('snl_myspace_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('lastFM').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_lastfm" value="1" '.(Configuration::get('snl_lastfm')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_lastfm_url" style="width:250px;" value="'.Configuration::get('snl_lastfm_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Yelp').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_yelp" value="1" '.(Configuration::get('snl_yelp')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_yelp_url" style="width:250px;" value="'.Configuration::get('snl_yelp_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('picsart').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_picsart" value="1" '.(Configuration::get('snl_picsart')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_picsart_url" style="width:250px;" value="'.Configuration::get('snl_picsart_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Tumblr').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_tumblr" value="1" '.(Configuration::get('snl_tumblr')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_tumblr_url" style="width:250px;" value="'.Configuration::get('snl_tumblr_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('digg').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_digg" value="1" '.(Configuration::get('snl_digg')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_digg_url" style="width:250px;" value="'.Configuration::get('snl_digg_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Wordpress').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_wordpress" value="1" '.(Configuration::get('snl_wordpress')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_wordpress_url" style="width:250px;" value="'.Configuration::get('snl_wordpress_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Deviantart').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_deviantart" value="1" '.(Configuration::get('snl_deviantart')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_deviantart_url" style="width:250px;" value="'.Configuration::get('snl_deviantart_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Weibo').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_weibo" value="1" '.(Configuration::get('snl_weibo')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_weibo_url" style="width:250px;" value="'.Configuration::get('snl_weibo_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Qzone').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_qzone" value="1" '.(Configuration::get('snl_qzone')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_qzone_url" style="width:250px;" value="'.Configuration::get('snl_qzone_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Formspring').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_formspring" value="1" '.(Configuration::get('snl_formspring')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_formspring_url" style="width:250px;" value="'.Configuration::get('snl_formspring_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Blogger').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_blogger" value="1" '.(Configuration::get('snl_blogger')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_blogger_url" style="width:250px;" value="'.Configuration::get('snl_blogger_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('LiveJournal').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_ljournal" value="1" '.(Configuration::get('snl_ljournal')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_ljournal_url" style="width:250px;" value="'.Configuration::get('snl_ljournal_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Ask.fm').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_ask" value="1" '.(Configuration::get('snl_ask')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_ask_url" style="width:250px;" value="'.Configuration::get('snl_ask_url').'" />
        							</div>	
        		                </div>
                                <div style="display:block; clear:both;">
        							<label>'.$this->l('Orkut').':</label>
        							<div class="margin-form" style="text-align:left;">
                                    <input type="checkbox" name="snl_orkut" value="1" '.(Configuration::get('snl_orkut')==1 ? 'checked="yes"' : '').'>
                                    <input type="text" name="snl_orkut_url" style="width:250px;" value="'.Configuration::get('snl_orkut_url').'" />
        							</div>	
        		                </div>
        	                </div>             
        	<div style="margin-top:20px; clear:both; overflow:hidden; display:block; text-align:center">
        	   <input type="submit" name="module_settings" class="button" value="'.$this->l('save').'">
        	</div>
            </fieldset>
           	</form>'; 
            return $form.'<iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Ffacebook.com%2Fmypresta&amp;send=false&amp;layout=button_count&amp;width=120&amp;show_faces=true&amp;font=verdana&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=276212249177933" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:120px; height:21px; margin-top:10px;" allowTransparency="true"></iframe><div style="float:right; text-align:right; display:inline-block; margin-top:10px; font-size:10px;">
        '.$this->l('Proudly developed by').' <a href="http://mypresta.eu" style="font-weight:bold; color:#B73737">MyPresta<font style="color:black;">.eu</font>.</a>
        </div>'; 
    }
    
    
    function hookheader($params){
        if ($this->psversion()==5){
            $this->context->controller->addCSS(($this->_path).'css/socialnetworklinks.css', 'all');
        } else {
            Tools::addCSS(($this->_path).'css/socialnetworklinks.css', 'all');
        }
	}
    
    function hooktop($params){
        if (Configuration::get('snl_where')==0){
            if ($this->psversion()==5){
                $this->context->smarty->assign(array('snlvar' => $this->getvar()));
            } else {
                global $smarty;
                $smarty->assign(array('snlvar' => $this->getvar()));
            }
            return $this->display(__FILE__, 'views/front/column.tpl');
        }
    }
    
    function hookleftcolumn($params){
        if (Configuration::get('snl_where')==1){
            if ($this->psversion()==5){
                $this->context->smarty->assign(array('snlvar' => $this->getvar()));
            } else {
                global $smarty;
                $smarty->assign(array('snlvar' => $this->getvar()));
            }
            return $this->display(__FILE__, 'views/front/column.tpl');
        }
    }
    
    function hookrightcolumn($params){
        if (Configuration::get('snl_where')==2){
            if ($this->psversion()==5){
                $this->context->smarty->assign(array('snlvar' => $this->getvar()));
            } else {
                global $smarty;
                $smarty->assign(array('snlvar' => $this->getvar()));
            }
            return $this->display(__FILE__, 'views/front/column.tpl');
        }
    }
    
    function hookfooter($params){
        if (Configuration::get('snl_where')==3){
            if ($this->psversion()==5){
                $this->context->smarty->assign(array('snlvar' => $this->getvar()));
            } else {
                global $smarty;
                $smarty->assign(array('snlvar' => $this->getvar()));
            }
            return $this->display(__FILE__, 'views/front/column.tpl');
        }
    }
}



class socialnetworklinksUpdate extends socialnetworklinks {  
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