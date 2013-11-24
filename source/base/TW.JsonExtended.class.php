<?php

/**
 * Extends PHP JSON handling for custom applications.
 *
 * @author Miquel Brazil <mbrazil@thingwone.com>
 */

class TW_JsonExtended
{

	public $json;
	public $forma;
	
	/** @type integer Counter determines which iteration on JSON object */
	public $c = 0;
	
	public $path_current = array();
	public $path_previous = array( 0 => array() );
	
	
	/**
	 * Sets up JSON Extended object.
	 */
	function __construct( $forma_type ) {
		
		/** @debug START */
			echo "<p>We just instantiated the JSON Extended class.</p>";
		/** @debug END */
		
		/** @type array Set instance variable to incoming JSON data */
		$this->load( $forma_type );
		$this->decode( $this->forma );
		
		/** @debug START */
			// var_dump($this->json);
		/** @debug END */
		
	}
	
	
	/**
	 * Navigates through passed JSON object.
	 */
	public function iterate() {
		
		/** @debug START */
			echo "<div style=\"background-color : red; color : white; padding : 5px;\"><p>jsonIterate has been called.<br />Incrementing the counter by 1.</p><p>The counter currently equals ". $this->c . "</div>";
		/** @debug END */
		
		/** @type integer Increment counter each time iterator() is called. */
		$this->c++;
		
		/** @debug START */
			echo "<div style=\"background-color : green; color : white; padding : 5px;\"><p>The counter now equals ". $this->c . "</div>";
			
			echo "Current path on run " . $this->c . ":";
			var_dump($this->path_current);
			
			echo "Previous path(s) on run " . $this->c . ":";
			var_dump($this->path_previous);
		/** @debug END */
	
		/** @type array Initialize JSON object in method scope to instance of JSON object. */
		$json = $this->json;
		
		/** Use path_current property to target specific portion of JSON object in method scope. */
		foreach ( $this->path_current as $part ) {
			
			/** @debug START */
				echo "<p>Navigating JSON with <b>" . $part . "</b> key.</p>";
			/** @debug END */
			
			$json = $json[ $part ];
			
		}
		
		//var_dump($json);
		//var_dump($this->c);
		
		/**
		 * Iterate over each element in the passed JSON object.
		 * This iterator will not iterate over additional values of a schema
		 * once it locates a properties section (i.e. title, description, required, etc).
		 */
		foreach ( $json as $k => $v ) {
			
			/** @debug START */
				echo "<h2>Inside the JSON iterator FOREACH loop.</h2>";
				
				echo "<p>We are evaluating the <b>" . $k . "</b> JSON property:</p>";
				
				var_dump($v);
			/** @debug END */
			
			/** Ensure we are working with an array */
			if ( is_array( $v ) ) {
				
				/** Check if the snippet has any additional properties */
				if ( array_key_exists( 'properties' , $v ) ) {
					
					/** @debug START */
						echo "<p>The JSON object has its own properties. We need to inspect further.</p>";
					/** @debug END */
					
					/** $type array Set path_previous property to current path so it remembers where we were */
					$this->path_previous[ $this->c ] = $this->path_current;
					
					/** @type string Add target parts to current path property */
					array_push( $this->path_current , $k , 'properties' );
					
					/** @debug START */
						echo "<p>Running jsonIterate again...</p>";
					/** @debug END */
					
					/** Recursively call iterate() to inspect properties */
					$this->iterate();
					
				} else {
				
					/** @debug START */
						echo "<p>The <b>" . $k . "</b> element is a singular node.<br />There is nothing to iterate over.<p>";
					/** @debug END */
					
				}
				
			} else {
			
				/** @debug START */
					echo "<p>The <b>" . $k . "</b> element is a singular node.<br />There is nothing to iterate over.<p>";
				/** @debug END */
				
			}
			
		}
		
		
		/** @debug START */
			echo "<div style=\"background-color : blue; color : white; padding : 5px;\"><p>We've completed a cycle of the jsonIterator<br />Decrementing the counter by 1.</p><p>The counter currently equals ". $this->c . ":</div>";
		/** @debug END */
		
		
		/** @type integer Decrement the function call counter after completing a cycle. */
		$this->c--;
		
		
		/** @debug START */
			echo "<div style=\"background-color : red; color : white; padding : 5px;\"><p>The counter now equals ". $this->c . "</div>";
		/** @debug END */
		
		
		/** @type array Reset the path_current property to previous path. */
		$this->path_current = $this->path_previous[ $this->c ];
		
		
		/** @debug START */
			echo "<p>Returning to the original function that called this.</p>";
		/** @debug END */
		
	}
	
	
	/**
	 * Load JSON.
	 *
	 * @todo Add error handling for file loading issues.
	 *
	 * @param string $forma_type Type of JSON Forma to load.
	 *
	 * @return boolean Returns true if file successfully loaded. False otherwise.
	 */
	public function load( $forma_type ) {
		
		$forma = file_get_contents( get_template_directory() . '/schemas/' . $forma_type . '.jsf' );
		
		$this->forma = $forma;
				
		return true;
		
	}
	
	
	public function encode() {
		
		
		
	}
	
	
	/**
	 * Decode JSON.
	 *
	 * @todo Add error handling for decode issues.
	 *
	 * @param string $json Decodes JSON string.
	 *
	 * @return boolean Returns true if successfully decoded. False otherwise.
	 */
	public function decode( $json ) {
		
		$json_decoded = json_decode( $json , true );
		
		$this->json = $json_decoded;
		
		return true;
		
	}

}

?>