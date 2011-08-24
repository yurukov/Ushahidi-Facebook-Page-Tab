<?php defined('SYSPATH') or die('No direct script access.');
/**
 * FacebookPageTab Controller
 * Generates a simple map representation fit for Facebook pages
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
* 
*/

class FacebookPageTab_Controller extends Template_Controller
{

	public $template = 'application/layout';
	protected $themes;

	public function __construct()
	{
		parent::__construct();
		$this->themes = new FacebookThemes();
		$this->themes->api_url = Kohana::config('settings.api_url');
		$this->template->header  = new View('facebookpagetab/header');
		$this->template->body  = new View('facebookpagetab/main');

		$site_name = Kohana::config('settings.site_name');
		$this->template->header->site_name = $site_name;
		$this->template->header->site_tagline = Kohana::config('settings.site_tagline');

	}

	public function index()
	{

		// Map Settings
		$clustering = Kohana::config('settings.allow_clustering');
		$marker_radius = Kohana::config('map.marker_radius');
		$marker_opacity = Kohana::config('map.marker_opacity');
		$marker_stroke_width = Kohana::config('map.marker_stroke_width');
		$marker_stroke_opacity = Kohana::config('map.marker_stroke_opacity');

        // pdestefanis - allows to restrict the number of zoomlevels available
		$numZoomLevels = Kohana::config('map.numZoomLevels');
		$minZoomLevel = Kohana::config('map.minZoomLevel');
	   	$maxZoomLevel = Kohana::config('map.maxZoomLevel');

		// pdestefanis - allows to limit the extents of the map
		$lonFrom = Kohana::config('map.lonFrom');
		$latFrom = Kohana::config('map.latFrom');
		$lonTo = Kohana::config('map.lonTo');
		$latTo = Kohana::config('map.latTo');

		$this->themes->js = new View('application/main_js');
		$this->themes->js->json_url = ($clustering == 1) ?
			"json/cluster" : "json";
		$this->themes->js->marker_radius =
			($marker_radius >=1 && $marker_radius <= 10 ) ? $marker_radius : 5;
		$this->themes->js->marker_opacity =
			($marker_opacity >=1 && $marker_opacity <= 10 )
			? $marker_opacity * 0.1  : 0.9;
		$this->themes->js->marker_stroke_width =
			($marker_stroke_width >=1 && $marker_stroke_width <= 5 ) ? $marker_stroke_width : 2;
		$this->themes->js->marker_stroke_opacity =
			($marker_stroke_opacity >=1 && $marker_stroke_opacity <= 10 )
			? $marker_stroke_opacity * 0.1  : 0.9;

		// pdestefanis - allows to restrict the number of zoomlevels available
		$this->themes->js->numZoomLevels = $numZoomLevels;
		$this->themes->js->minZoomLevel = $minZoomLevel;
		$this->themes->js->maxZoomLevel = $maxZoomLevel;

		// pdestefanis - allows to limit the extents of the map
		$this->themes->js->lonFrom = $lonFrom;
		$this->themes->js->latFrom = $latFrom;
		$this->themes->js->lonTo = $lonTo;
		$this->themes->js->latTo = $latTo;

		$this->themes->js->default_map = Kohana::config('settings.default_map');
		$this->themes->js->default_zoom = Kohana::config('settings.default_zoom');
		$this->themes->js->latitude = Kohana::config('settings.default_lat');
		$this->themes->js->longitude = Kohana::config('settings.default_lon');
		$this->themes->js->default_map_all = Kohana::config('settings.default_map_all');

		$this->themes->js->active_startDate = time()-3600*24*30.5*3;
		$this->themes->js->active_endDate = time()+3600*10;
		
		$this->themes->js->blocks_per_row = Kohana::config('settings.blocks_per_row');

		// Rebuild Header Block
		$this->template->header->header_block = $this->themes->header_block();
	}
}

?>
