<?php

/**
 * Extends PHP JSON handling for custom applications.
 *
 * @author Miquel Brazil <mbrazil@thingwone.com>
 */

class TW_JsonExtended
{

	public $json;
	
	/** @type integer Counter determines which iteration on JSON object */
	public $c = 0;
	
	public $path_current = array();
	public $path_previous = array( 0 => array() );
	
	/**
	 * Sets up JSON Extended object.
	 */
	function __construct( $json ) {
		
		echo "<p>We just instantiated the JSON Extended class.</p>";
		
		// set instance variable to incoming JSON data
		$this->json = $json;
		
	}

	/**
	 * Navigates through passed JSON object.
	 */
	public function iterate() {
	
		echo "<div style=\"background-color : red; color : white; padding : 5px;\"><p>jsonIterate has been called.<br />Incrementing the counter by 1.</p><p>The counter currently equals ". $this->c . "</div>";
		
		$this->c++;
		
		echo "<div style=\"background-color : red; color : white; padding : 5px;\"><p>The counter now equals ". $this->c . "</div>";
		
		echo "Current path on run " . $this->c . ":";
		var_dump($this->path_current);
		
		echo "Previous path(s) on run " . $this->c . ":";
		var_dump($this->path_previous);
	
		//echo "JSON <b>before</b> iteration occurs:";
		//var_dump($json);
		
		if ( empty( $this->path_current ) ) {
			
			echo "<p>First run, we need to inspect the 'schema' tree.</p>";
			
			// set breadcrumbs to 'schema'
			$this->path_current[] = 'schema';
			
		}
		
		$json = $this->json;
		
		// use $this->path_current to target specific portion of JSON data
		foreach ( $this->path_current as $part ) {
			
			$json = $json[ $part ];
			
		}
		
		//echo "JSON <b>after</b> iteration occurs:";
		//var_dump($this->json);
		//var_dump($json);
		
		// check if JSON object has properties
		if ( array_key_exists( 'properties' , $json ) ) {
			
			echo "<p>The JSON object has its own properties. We need to inspect further.</p>";
			
			// set $path_previous so it remembers where we were
			$this->path_previous[ $this->c ] = $this->path_current;
			
			echo "<p>Adding 'properties' crumb to breadcrumbs...</p>";
			// add crumb to breadcrumbs array
			$this->path_current[] = 'properties';
			
			echo "<p>Running jsonIterate again...</p>";
			// call jsonIterate() to recursively inspect these properties
			$this->iterate();
			
		}
		
		//var_dump($json);
		//var_dump($this->c);
		
		if ( $this->c > 1 ) {
			
			// if outer JSON object has no properties
			foreach ( $json as $k => $v ) {
			
				echo "<h2>Inside the JSON property loop.</h2>";
				
				echo "<p>The current <b>" . $k . "</b> JSON property:</p>";
				var_dump($v);
			
				// check if JSON object has properties
				if ( array_key_exists( 'properties' , $v ) ) {
				
					echo "<p>The JSON object has its own properties. We need to inspect further.</p>";
					
					// set $path_previous so it remembers where we were
					$this->path_previous[ $this->c ] = $this->path_current;
					
					// add crumbs to breadcrumbs array
					array_push( $this->path_current , $k , 'properties' );
					
					echo "<p>Running jsonIterate again...</p>";
					// call jsonIterate() to recursively inspect these properties
					$this->iterate();
					
				}
				
			}
			
		}
		
		echo "<div style=\"background-color : red; color : white; padding : 5px;\"><p>We've completed a cycle of the jsonIterator<br />Decrementing the counter by 1.</p><p>The counter currently equals ". $this->c . ":</div>";
		
		// decrement the function call counter after completing a cycle
		$this->c--;
		
		echo "<div style=\"background-color : red; color : white; padding : 5px;\"><p>The counter now equals ". $this->c . "</div>";
		
		// reset the $path
		
		$this->path_current = $this->path_previous[ $this->c ];
		
		echo "<p>Returning to the original function that called this.</p>";
		
	}
	
	public function load() {
		
		
		
	}
	
	public function encode() {
		
		
		
	}
	
	public function decode() {
		
		
		
	}

}

?>