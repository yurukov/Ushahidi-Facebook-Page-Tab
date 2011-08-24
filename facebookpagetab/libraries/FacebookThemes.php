<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Themes Library
 * These are regularly used templating functions
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	Boyan Yurukov yurukov@gmail.com
 * @package	
 * @module	FacebookPageTab Controller
 * @copyright	Boyan Yurukov http://yurukov.net/blog
 * @license	http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)  
 */

class FacebookThemes extends Themes_Core{
	
	public $map_enabled = false;
	public $api_url = null;
	public $main_page = false;
	public $this_page = false;
	public $treeview_enabled = false;
	public $validator_enabled = false;
	public $photoslider_enabled = false;
	public $videoslider_enabled = false;
	public $colorpicker_enabled = false;
	public $site_style = false;
	public $js = null;
	
	public $css_url = null;
	public $js_url = null;
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function header_block()
	{
		return $this->_header_css_fb().
			$this->_header_js_fb();
	}
	

	private function _header_css_fb()
	{
		$core_css = "";
		$core_css .= html::stylesheet($this->css_url."media/css/jquery-ui-themeroller", "", true);

		$core_css .= html::stylesheet($this->css_url."plugins/facebookpagetab/views/facebookpagetab/style.css", "", true);
		$core_css .= "<!--[if lte IE 7]>".html::stylesheet($this->css_url."media/css/iehacks","",true)."<![endif]-->";
		$core_css .= "<!--[if IE 7]>".html::stylesheet($this->css_url."media/css/ie7hacks","",true)."<![endif]-->";
		$core_css .= "<!--[if IE 6]>".html::stylesheet($this->css_url."media/css/ie6hacks","",true)."<![endif]-->";
		$core_css .= html::stylesheet($this->css_url."media/css/openlayers","",true);
		
		// Render CSS
		$plugin_css = plugin::render('stylesheet');
		
		return $core_css.$plugin_css;
	}
	
	
	private function _header_js_fb()
	{
		$core_js = "";
		$core_js .= html::script($this->js_url."media/js/OpenLayers", true);
		$core_js .= "<script type=\"text/javascript\">OpenLayers.ImgPath = '".$this->js_url."media/img/openlayers/"."';</script>";
		$core_js .= html::script($this->js_url."media/js/jquery", true);
		//$core_js .= html::script($this->js_url."media/js/jquery.ui.min", true);
		$core_js .= html::script("https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js", true);
		$core_js .= html::script($this->js_url."media/js/jquery.pngFix.pack", true);
		$core_js .= $this->api_url;
		$core_js .= html::script($this->js_url."media/js/selectToUISlider.jQuery", true);
		$core_js .= html::script($this->js_url."media/js/jquery.flot", true);
		$core_js .= html::script($this->js_url."media/js/timeline", true);
		$core_js .= "<!--[if IE]>".html::script($this->js_url."media/js/excanvas.min", true)."<![endif]-->";
		
		// Javascript files from plugins
		$plugin_js = plugin::render('javascript');
		
		// Inline Javascript
		$inline_js = "<script type=\"text/javascript\">
                        <!--//"
			.$this->js.
                        "//-->
                        </script>";
		
		return $core_js.$plugin_js.$inline_js;
	}

}
