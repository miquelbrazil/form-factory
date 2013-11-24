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
	public function iterate( $action = array() ) {
		
		/** @debug START */
			echo "<div style=\"background-color : red; color : white; padding : 5px;\"><p>jsonIterate has been called.<br />Incrementing the counter by 1.</p><p>The counter currently equals ". $this->c . "</div>";
		/** @debug END */
		
		/** @type integer Increment function call counter each time iterator() is called. */
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
		
		/** 
		 * Use path_current property to target specific portion of JSON object in method scope.
		 * If this section has already happened then it won't run again when the function returns to a previous cycle.
		 * Otherwise it would overwrite the JSON data it was handling and start the apocalypse.
		 */
		foreach ( $this->path_current as $part ) {
			
			/** @debug START */
				echo "<p>Navigating JSON with <b>" . $part . "</b> key.</p>";
			/** @debug END */
			
			$json = $json[ $part ];
		}
		
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
			
			/** Initiate LOOKUP & CALLBACK functionality if $action is set */
			if ( is_array( $action ) && !empty( $action ) ) {
			
				var_dump($action);    // inspect action;
				
				/** Select appropriate lookup strategy */
				if ( $action['search']['method'] == 'key' ) {
				
					echo '<div style="background-color : #1AE6E6 ; padding : 10px;"><p>I\'m searching for a particular key.</p></div>';
					
					if ( $action['search']['lookup'] == $k ) {
						
						echo '<div style="background-color : gray ; padding : 10px; font-weight : bold; color : white;"><p>I found the key I\'m looking for! Initiating callback...</p></div>';
						$this->doCallback( $action , $v );
					}
					
				} elseif ( $action['search']['method'] == 'value' ) {
				
					echo '<div style="background-color : #1AE6E6 ; padding : 10px;"><p>I\'m searching for a particular key within the current object.</p></div>';
					
					if ( is_array( $v ) ) {
						
						if ( array_key_exists( $action['search']['lookup'] , $v ) ) {
							
							echo '<div style="background-color : gray ; padding : 10px; font-weight : bold; color : white;"><p>I found the key I\'m looking for! Initiating callback...</p></div>';
							$this->doCallback( $action , $k );
							
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
			}
		}
		
		/** @debug START */
			echo "<div style=\"background-color : blue; color : white; padding : 5px;\"><p>We've completed a cycle of the jsonIterator<br />Decrementing the counter by 1.</p><p>The counter currently equals ". $this->c . "</div>";
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
	 * An alternate method for traversing the JSON Forma
	 * Not fully understanding how to leverage it to pass to other functions
	 */
	public function iterateSPL() {
	 
	 	$json = new RecursiveArrayIterator( $this->json );
	
		$jiterator = new RecursiveIteratorIterator( $json, 2 );
		
		/** Need to use identity comparison ( === ) when evaluating values */
		foreach ( $jiterator as $k => $v ) {
			
			var_dump($k);
			
		}
	
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
	
	
	public function resolveRefs() {
		
		
		
	}
	
	
	public function parseRefs() {
		
		
		
	}
	
	/**
	 * Prepare and send JSON to callback function for processing
	 */
	private function doCallback( $action , $json ) {
		
		echo '<div style="background-color : #e0e0e0 ; padding : 10px;"><p>Hello, I\'m preparing the callback function and paramters for you.</p><p>I will use the following paramaters to find the right callback:</p>';
		var_dump($action);
		echo '</div>';
		
		if ( $action['callback'][0] == '$this' ) {
			
			call_user_func_array( array( $this , $action['callback'][1] ) , array( $action , $json ) );
			
		} else {
			
			call_user_func_array( $action['callback'] , array( $action , $json ) );
		}
	}
	
	
	private function preIterate( $action , $k ) {
		
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
		$this->iterate( $action );
	}

}

?>