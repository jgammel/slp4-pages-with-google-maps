<?php
if (! class_exists('SLPPages_UI')) {

    require_once(SLPLUS_PLUGINDIR.'/include/base_class.userinterface.php');

    /**
     * Holds the UI-only code.
     *
     * This allows the main plugin to only include this file in the front end
     * via the wp_enqueue_scripts call.   Reduces the back-end footprint.
     *
     * @property        SLPPages        $addon
     *
     * @package StoreLocatorPlus\SLPPages\UI
     * @author Lance Cleveland <lance@storelocatorplus.com>
     * @copyright 2015 Charleston Software Associates, LLC
     */
    class SLPPages_UI  extends SLP_BaseClass_UI  {
        public $js_requirements = array( 'google_maps' );


        /**
         * Add hooks and filters.
         */
	    function add_hooks_and_filters() {
		    parent::add_hooks_and_filters();

		    add_filter( 'slp_js_options' , array( $this , 'modify_js_options'           ) );

		    add_shortcode('storepage'    , array( $this , 'process_storepage_shortcode' ) );
		    // storepage = shortcode tag. process_storepage_shortcode = hook to run when shortcode is found. $this = the class which must refer to the current store location.
		    
		    add_shortcode('storepage_map'    , array( $this , 'process_storepage_map' ) );
		    //attempt to create a shortcode that will print a map for store pages.

            if ( get_post_type() === SLPlus::locationPostType ) {
                $current_location_array =
                    $this->slplus->database->get_Record(
                        array('selectall', 'wherelinkedpostid'), get_the_ID()
                    );
                $this->slplus->currentLocation->set_PropertiesViaArray( $current_location_array );

                $this->addon->options['current'] = $current_location_array;

                $this->js_settings = array_merge( $this->slplus->options , $this->addon->options );
                $this->slplus->enqueue_google_maps_script();
                $this->enqueue_ui_javascript();
                $this->enqueue_ui_css();
            }
	    }

        /**
         * Create an url hyperlink base on the type field provided.
         *
         * @param string $field
         * @param string $content
         *
         * @return string
         */
        private function create_string_hyperlink( $field , $content ) {
            if ( is_null( $field ) ) { return ''; }
            if ( empty( $content ) ) { return ''; }
            return
                sprintf('<a href="%s" target="store_locator_plus">%s</a>',
                    esc_url($content) ,
                    $content
                );
        }


        /**
         * Create an image string if the field and content are valid.
         *
         * @param string $field
         * @param string $content
         * @param string $title
         *
         * @return string
         */
        private function create_string_image( $field , $content , $title ) {
            if ( is_null( $field ) ) { return ''; }
            if ( empty( $content ) ) { return ''; }
            if ( empty( $title ) ) { $title = $this->slplus->currentLocation->store; }
            $title=sprintf('title="%s" alt="%s"',$title,$title);

            return
                sprintf('<img src="%s" class="store_page_image field-%s" %s>',
                    esc_url($content) ,
                    $field,
                    $title
                );
        }

        /**
         * Create an email hyperlink base on the type field provided.
         *
         * @param string $field
         * @param string $content
         *
         * @return string
         */
        private function create_string_mailto ( $field , $content ) {
            if ( is_null( $field ) ) { return ''; }
            if ( empty( $content ) ) { return ''; }
            return
                sprintf('<a href="%s" target="store_locator_plus">%s</a>',
                    'mailto:' . $content ,
                    $content
                );
        }

        /**
         * Only enqueue JS on store page page type.
         */
        public function enqueue_ui_javascript() {
            if ( get_post_type() === SLPlus::locationPostType ) {
                parent::enqueue_ui_javascript();
            }
        }

        /**
         * Only enqueue CSS on store page page type.
         */
        public function enqueue_ui_css() {
            if ( get_post_type() === SLPlus::locationPostType ) {
                parent::enqueue_ui_css();
            }
        }

        /**
         * Generate a map for the current location.
         */
        private function generate_map() {
            $map_style = 'style="' .
                "height: {$this->slplus->options_nojs['map_height']}{$this->slplus->options_nojs['map_height_units']}; " .
                "width: {$this->slplus->options_nojs['map_width']}{$this->slplus->options_nojs['map_width_units']}; " .
                '"';

            $html =
                "<div id='storepage-{$this->slplus->currentLocation->id}-map' class='map-canvas-box' {$map_style}>".
                    "<div id='map-canvas'></div>".
                "</div>";

            return $html;
        }

	    /**
	     * Modify the slplus.options object going into SLP.js
	     *
	     * @param   array $options
	     * @return  array
	     */
	    function modify_js_options( $options ) {
		    return array_merge($options, array(
				    'use_same_window' => $this->addon->options['prevent_new_window'],  // used by slp.js
			    )
		    );
	    }
	    
	     /**
	     * Manage the storepage map shortcode
	     *
	     * @param array $attributes named array of attributes set in shortcode
	     * @param string $content the existing content that we will modify
	     * @return string the modified HTML content
	     */
	     
	     
	    function process_storepage_map($attributes, $content = null){
		    
		     $attributes = apply_filters('shortcode_storepage', $attributes);



		    $store_map_address = $this->slplus->currentLocation->address;
		    $store_map_city =  $this->slplus->currentLocation->city;
		    $store_map_state = $this->slplus->currentLocation->state;
		    
		    $mapcode = '<iframe style="display: block; margin-right:30px;" id="store-map" width="100%" height="330" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' 
		    			. $store_map_address 
		    			. '+'
		    			. $store_map_city 
		    			.','
		    			. $store_map_state
		    			. '&amp;maptype=roadmap&amp;aq=0&amp;t=m&amp;ie=UTF8&amp;output=embed"></iframe>';
		    
		    return $mapcode;
	    }

	    /**
	     * Manage the storepage shortcode
	     *
	     * @param array $attributes named array of attributes set in shortcode
	     * @param string $content the existing content that we will modify
	     * @return string the modified HTML content
	     */
	    function process_storepage_shortcode($attributes, $content = null) {

		    // Pre-process the attributes.
		    //
		    // This allows third party plugins to man-handle the process by
		    // tweaking the attributes.  If, for example, they were to return
		    // array('hard_coded_value','blah blah blah') that is all we would return.
		    //
		    // FILTER: shortcode_storepage
		    //
		    $attributes = apply_filters('shortcode_storepage', $attributes);

		    // Process the attributes
		    //
            $field = null;
            $title = null;
		    foreach ($attributes as $key => $value) {
			    $key = strtolower($key);
			    switch ($key) {

				    // Field attribute: output specified field
				    //
				    case 'field':

					    // Convert legacy sl_<field> references
					    //
					    $field = preg_replace('/\W/', '', htmlspecialchars_decode($value));
					    $field = preg_replace('/^sl_/', '', strtolower($value));
					    $content = $this->slplus->currentLocation->$field;
					    break;

                    case 'title':
                        $title = $value;
                        break;

				    case 'type':
					    switch ($value) {
						    case 'hyperlink':
							    $content = $this->create_string_hyperlink( $field , $content );
							    break;

                            case 'image':
                                $content = $this->create_string_image( $field , $content , $title );
                                break;

                            case 'mailto':
                                $content = $this->create_string_mailto( $field , $content );
                                break;

						    default:
							    break;
					    }
					    break;

				    case 'hard_coded_value':
					    $content = $value;
					    break;

                    case 'map':
                        $content = $this->generate_map();
                        break;

				    default:
					    break;
			    }
		    }

            return $content;
	    }

    }
}