<?php
/**
 * Main cluster js file.
 * 
 * Server Side Map Clustering
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
?>
		// Map JS
		
		// Map Object
		var map;
		// Selected Category
		var currentCat;
		// Selected Layer
		var thisLayer;
		// WGS84 Datum
		var proj_4326 = new OpenLayers.Projection('EPSG:4326');
		// Spherical Mercator
		var proj_900913 = new OpenLayers.Projection('EPSG:900913');
		// Change to 1 after map loads
		var mapLoad = 0;
		// /json or /json/cluster depending on if clustering is on
		var default_json_url = "<?php echo $json_url ?>";
		// Current json_url, if map is switched dynamically between json and json_cluster
		var json_url = default_json_url;
		
		/* 
		 - Part of #2168 fix
		 - Added by E.Kala <emmanuel(at)ushahidi.com>
		*/
		// Global list for current KML overlays in display
		var kmlOverlays = [];
		
		var baseUrl = "<?php echo url::base(); ?>";
		var longitude = <?php echo $longitude; ?>;
		var latitude = <?php echo $latitude; ?>;
		var defaultZoom = <?php echo $default_zoom; ?>;
		var markerRadius = <?php echo $marker_radius; ?>;
		var markerOpacity = "<?php echo $marker_opacity; ?>";
		var selectedFeature;
		var allGraphData = "";
		var dailyGraphData = "";
		var gMediaType = 0
		var timeout = 1500;

		var startTime = <?php echo $active_startDate ?>;
		var endTime = <?php echo $active_endDate ?>;	
		
		var activeZoom = null;

		var gMarkerOptions = {
			baseUrl: baseUrl, longitude: longitude,
			latitude: latitude, defaultZoom: defaultZoom,
			markerRadius: markerRadius,
			markerOpacity: markerOpacity,
			protocolFormat: OpenLayers.Format.GeoJSON
		};
							
		/*
		Create the Markers Layer
		*/
		function addMarkers(catID,startDate,endDate, currZoom, currCenter,
			mediaType, thisLayerID, thisLayerType, thisLayerUrl, thisLayerColor)
		{
			activeZoom = currZoom;
			
			if(activeZoom == ''){
				return $.timeline({categoryId: catID,
		                   startTime: new Date(startDate * 1000),
		                   endTime: new Date(endDate * 1000),
						   mediaType: mediaType
						  }).addMarkers(
							startDate, endDate, gMap.getZoom(),
							gMap.getCenter(), thisLayerID, thisLayerType, 
							thisLayerUrl, thisLayerColor, json_url);
			}
			
			setTimeout(function(){
				if(currZoom == activeZoom){
					return $.timeline({categoryId: catID,
		                   startTime: new Date(startDate * 1000),
		                   endTime: new Date(endDate * 1000),
						   mediaType: mediaType
						  }).addMarkers(
							startDate, endDate, gMap.getZoom(),
							gMap.getCenter(), thisLayerID, thisLayerType, 
							thisLayerUrl, thisLayerColor, json_url);
				}else{
					return true;
				}
			}, timeout);
		}

		/**
		 * Display loader as Map Loads
		 */
		function onMapStartLoad(event)
		{
			if ($("#loader"))
			{
				$("#loader").show();
			}

			if ($("#OpenLayers\\.Control\\.LoadingPanel_4"))
			{
				$("#OpenLayers\\.Control\\.LoadingPanel_4").show();
			}
		}

		/**
		 * Hide Loader
		 */
		function onMapEndLoad(event)
		{
			if ($("#loader"))
			{
				$("#loader").hide();
			}

			if ($("#OpenLayers\\.Control\\.LoadingPanel_4"))
			{
				$("#OpenLayers\\.Control\\.LoadingPanel_4").hide();
			}
		}

		/**
		 * Close Popup
		 */
		function onPopupClose(event)
		{
			selectControl.unselect(selectedFeature);
			selectedFeature = null;
		}

		/**
		 * Display popup when feature selected
		 */
		function onFeatureSelect(event)
		{
			selectedFeature = event.feature;
			zoom_point = event.feature.geometry.getBounds().getCenterLonLat();
			lon = zoom_point.lon;
			lat = zoom_point.lat;
			
			var thumb = "";
			if ( typeof(event.feature.attributes.thumb) != 'undefined' && 
				event.feature.attributes.thumb != '')
			{
				thumb = "<div class=\"infowindow_image\"><a href='"+event.feature.attributes.link+"'>";
				thumb += "<img src=\""+event.feature.attributes.thumb+"\" height=\"59\" width=\"89\" /></a></div>";
			}

			var content = "<div class=\"infowindow\">" + thumb;
			content += "<div class=\"infowindow_content\"><div class=\"infowindow_list\">"+event.feature.attributes.name+"</div>";
			content += "\n<div class=\"infowindow_meta\">";
			if ( typeof(event.feature.attributes.link) != 'undefined' &&
				event.feature.attributes.link != '')
			{
				content += "<a href='"+event.feature.attributes.link+"'><?php echo Kohana::lang('ui_main.more_information');?></a><br/>";
			}
			
			content += "<a href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +",1)'>";
			content += "<?php echo Kohana::lang('ui_main.zoom_in');?></a>";
			content += "&nbsp;&nbsp;|&nbsp;&nbsp;";
			content += "<a href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +",-1)'>";
			content += "<?php echo Kohana::lang('ui_main.zoom_out');?></a></div>";
			content += "</div><div style=\"clear:both;\"></div></div>";		

			if (content.search("<?php echo '<'; ?>script") != -1)
			{
				content = "Content contained Javascript! Escaped content below.<br />" + content.replace(/<?php echo '<'; ?>/g, "&lt;");
			}
            
			// Destroy existing popups before opening a new one
			if (event.feature.popup != null)
			{
				map.removePopup(event.feature.popup);
			}
			
			popup = new OpenLayers.Popup.FramedCloud("chicken", 
				event.feature.geometry.getBounds().getCenterLonLat(),
				new OpenLayers.Size(100,100),
				content,
				null, true, onPopupClose);

			event.feature.popup = popup;
			map.addPopup(popup);
		}

		/**
		 * Destroy Popup Layer
		 */
		function onFeatureUnselect(event)
		{
			// Safety check
			if (event.feature.popup != null)
			{
				map.removePopup(event.feature.popup);
				event.feature.popup.destroy();
				event.feature.popup = null;
			}
		}

		// Refactor Clusters On Zoom
		// *** Causes the map to load json twice on the first go
		// *** Need to fix this!
		function mapZoom(event)
		{
			// Prevent this event from running on the first load
			if (mapLoad > 0)
			{
				// Get Current Category
				currCat = $("#currentCat").val();

				// Get Current Start Date
				currStartDate = $("#startDate").val();

				// Get Current End Date
				currEndDate = $("#endDate").val();

				// Get Current Zoom
				currZoom = map.getZoom();

				// Get Current Center
				currCenter = map.getCenter();

				// Refresh Map
				addMarkers(currCat, currStartDate, currEndDate, currZoom, currCenter);
			}
		}

		function mapMove(event)
		{
			// Prevent this event from running on the first load
			if (mapLoad > 0)
			{
				// Get Current Category
				currCat = $("#currentCat").val();

				// Get Current Start Date
				currStartDate = $("#startDate").val();

				// Get Current End Date
				currEndDate = $("#endDate").val();

				// Get Current Zoom
				currZoom = map.getZoom();

				// Get Current Center
				currCenter = map.getCenter();
				
				// Part of #2168 fix
				// Remove the KML overlays
				if (kmlOverlays.length > 0)
				{
					for (var i = 0; i < kmlOverlays.length; i++)
					{
						map.removeLayer(kmlOverlays[i]);
					}
				}
				
				// Refresh Map
				addMarkers(currCat, currStartDate, currEndDate, currZoom, currCenter, gMediaType);
				
				// Part of #2168 fix
				// E.Kala <emmanuel(at)ushahidi.com>
				// Add back the KML overlays
				
				/* 
				  - The timout is so that the cluster markers are given time to load before
				  - the overlays can be rendered
				*/
				setTimeout(
					function()
					{
						if (kmlOverlays.length > 0)
						{
							for (var i = 0; i < kmlOverlays.length; i++)
							{
								kmlItem = kmlOverlays[i];
								map.addLayer(kmlItem);
								
								// Add feature selection events to the last item
								if (i == kmlOverlays.length -1)
								{
									selectControl = new OpenLayers.Control.SelectFeature(kmlItem);
									map.addControl(selectControl);
									selectControl.activate();
									kmlItem.events.on({
										"featureselected": onFeatureSelect,
										"featureunselected": onFeatureUnselect
									});
								}
								
							}
						}
					},
					timeout
				);
			}
		}
		
		/**
		 * Zoom to Selected Feature from within Popup
		 */
		function zoomToSelectedFeature(lon, lat, zoomfactor)
		{
			var lonlat = new OpenLayers.LonLat(lon,lat);

			// Get Current Zoom
			currZoom = map.getZoom();
			// New Zoom
			newZoom = currZoom + zoomfactor;
			// Center and Zoom
			map.setCenter(lonlat, newZoom);
			// Remove Popups
			for (var i=0; i<?php echo '<'; ?>map.popups.length; ++i)
			{
				map.removePopup(map.popups[i]);
			}
		}
		
		/*
		Zoom to Selected Feature from outside Popup
		*/
		function externalZeroIn(lon, lat, newZoom, cipopup)
		{
			
			var point = new OpenLayers.LonLat(lon,lat);
			point.transform(proj_4326, map.getProjectionObject());
			// Center and Zoom
			map.setCenter(point, newZoom);
			
			if (cipopup === undefined) 
			{
				// A checkin id was not passed so we won't bother showing the info window
			}
			else
			{
				// An id was passed, so lets show an info window
				// TODO: Do this.
			}
		}

		
		/**
		 * Toggle Layer Switchers
		 */
		function toggleLayer(link, layer)
		{
			if ($("#"+link).text() == "<?php echo Kohana::lang('ui_main.show'); ?>")
			{
				$("#"+link).text("<?php echo Kohana::lang('ui_main.hide'); ?>");
			}
			else
			{
				$("#"+link).text("<?php echo Kohana::lang('ui_main.show'); ?>");
			}
			$('#'+layer).toggle(500);
		}
		
		/**
		 * Create a function that calculates the smart columns
		 */

		jQuery(function() {
			var map_layer;
			markers = null;
			var catID = '';
			OpenLayers.Strategy.Fixed.prototype.preload=true;
			
			/*
			- Initialize Map
			- Uses Spherical Mercator Projection
			- Units in Metres instead of Degrees					
			*/
			var options = {
				units: "mi",
				numZoomLevels: 18,
				controls:[],
				projection: proj_900913,
				'displayProjection': proj_4326,
				eventListeners: {
					"zoomend": mapMove
				},
				'theme': null
			};
			
			map = new OpenLayers.Map('map', options);
			map.addControl( new OpenLayers.Control.LoadingPanel({minSize: new OpenLayers.Size(573, 366)}) );
			
			<?php echo map::layers_js(FALSE); ?>
			map.addLayers(<?php echo map::layers_array(FALSE); ?>);
			
			
			// Add Controls
			map.addControl(new OpenLayers.Control.Navigation());
			map.addControl(new OpenLayers.Control.Attribution());
			map.addControl(new OpenLayers.Control.PanZoomBar());
			map.addControl(new OpenLayers.Control.MousePosition(
				{
					div: document.getElementById('mapMousePosition'),
					numdigits: 5
				}));    
			map.addControl(new OpenLayers.Control.Scale('mapScale'));
			map.addControl(new OpenLayers.Control.ScaleLine());
			map.addControl(new OpenLayers.Control.LayerSwitcher());
			
				
			gMap = map;
			
			// Category Switch Action
			$("a[id^='cat_']").click(function()
			{
				var catID = this.id.substring(4);
				var catSet = 'cat_' + this.id.substring(4);
				$("a[id^='cat_']").removeClass("active"); // Remove All active
				$("[id^='child_']").hide(); // Hide All Children DIV
				$("#cat_" + catID).addClass("active"); // Add Highlight
				$("#child_" + catID).show(); // Show children DIV
				$(this).parents("div").show();
				
				currentCat = catID;
				$("#currentCat").val(catID);

				// setUrl not supported with Cluster Strategy
				//markers.setUrl("<?php echo url::site(); ?>" json_url + '/?c=' + catID);
				
				// Destroy any open popups
				if (selectedFeature) {
					onPopupClose();
				};
				
				// Get Current Zoom
				currZoom = map.getZoom();
				
				// Get Current Center
				currCenter = map.getCenter();
				
				gCategoryId = catID;
				
				addMarkers(catID, startTime, endTime, currZoom, currCenter, gMediaType);
								
				
				return false;
			});
			
					
			gCategoryId = '0';
			gMediaType = 0;
			
			// Initialize Map
			addMarkers(gCategoryId, startTime, endTime, '', '', gMediaType);
			
		});

