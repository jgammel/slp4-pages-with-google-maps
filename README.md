# slp4-pages-with-google-maps
Create a custom shortcode within slp-pages to render a dynamic google map on every store page

## Introduction
I am not a PHP developer and have limited experience with Wordpress Plugins, so please forgive this messy hack. I can confirm that this solution is working for me on Wordpress version 4.3.1 with SLP4. I hope it works for you as well.

NOTE: This is a continuation of the thread from the storelocatorplus.com forums found at [http://www.storelocatorplus.com/forums/topic/googles-static-map-api-for-store-pages/](http://www.storelocatorplus.com/forums/topic/googles-static-map-api-for-store-pages/)

## The Problem
Latest versions of Wordpress [no longer support shortcodes with attributes when they are embedded between double quotes](https://make.wordpress.org/core/2015/07/23/changes-to-the-shortcode-api/). This creates a problem for SLP Pages users who are trying to embed a dynamic Google Map on their store pages. My old code looked something like this:

```
<iframe style="display: block; margin-right:30px; " id="store-map" width="100%" height="330" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=[storepage field="address"]+[storepage field="city"],[storepage field="state"]&amp;maptype=roadmap&amp;aq=0&amp;t=m&amp;ie=UTF8&amp;output=embed">
</iframe>
```

What was happened was the shortcodes were rendering as text, instead of as their expected values.


## My Solution
Create a unique shortcode for Store Pages that will print out an entire chunk of HTML, with the values of the store page fields, and use that to display a map instead of an iframe with embedded shortcodes.

The shortcode:
```
 add_shortcode('storepage_map'    , array( $this , 'process_storepage_map' ) );
```

The function:
```
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

```

## How to install

Download my revised version of class.userinterface.php and upload it over what's currently in the slp-pages>include folder.

Use the shortcode `[storepage_map]` in the Store Locator Plus Pages template. 