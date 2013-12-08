<?php

/**
 * Extends PHP JSON handling for my custom applications.
 *
 * I created this class as a superset of functionality to the 
 * built in PHP JSON handling. This class is primarily used
 * to parse JSON for use by the TW_Forma class.
 *
 * @author Miquel Brazil <mbrazil@thingwone.com>
 */

class TW_JsonSchema
{

	/**
	 * The decoded JSON this object works with.
	 *
	 * @since 0.0.1
	 * @access public
	 * @see TW_JsonExtended::decode()
	 * @type array $json Defaults as an array rather than an object.
	 */
	public $json = null;
	
	
	/**
	 * The raw JSON string when loaded from a URI.
	 *
	 * @since 0.0.1
	 * @access public
	 * @see TW_JsonExtended::load()
	 * @type string $json_raw
	 * @note Not sure this is absolutely necessary.
	 */
	public $json_raw = null;
	
	
	/**
	 * Initializes JSON Extended object.
	 *
	 * @since 0.0.1
	 * @access public
	 *
	 * @uses TW_JsonExtender::load() Loads JSON string if given a URI.
	 * @uses TW_JsonExtender::decode() Decodes JSON string.
	 *
	 * @param mixed $json {
	 *     JSON data used to initialize object.
	 *     @type string URI specifying a location for the JSON data.
	 *     @type string Raw/undecoded JSON string.
	 *     @type array Decoded JSON object.
	 * }
	 *
	 * @param string $type Optional {
	 *     Describes the type of JSON data being passed to constructor.
	 *     Default <'uri'>.
	 *     Accepts <'uri'> , <'string'> , <'object'>
	 * }
	 *
	 * @todo Add error handling for load and decode functions.
	 */
	function __construct( $json , $type = 'uri' ) {
		
		switch ( $type ) {
			
			case 'uri':
				
				$uri = $this->parseUri( $json );
				$json = $this->load( $uri );
				$this->json = $json;
				
				$json = $this->resolveRefs( $json );
				
				$this->json = $json;
				
				break;
			
			case 'string':
			
				$this->decode( $json );
				break;
				
			case 'object':
			
				$this->json = $json;
				
				break;
		}
	}

	
	/**
	 * Load JSON.
	 *
	 * @todo Add error handling for file loading issues.
	 *
	 * @param array $uri URI of JSON Forma to load.
	 *
	 * @return string Returns a string of data if file successfully loaded. Returns false otherwise.
	 */
	public function load( $uri , $format = 'decode' ) {
	
		$json_raw = '{}';
		$json_decoded = null;
	
		if ( $uri['base'] !== '#' ) {
			
			/** Error-handling would happen here */
			$json_raw = file_get_contents( get_template_directory() . '/schemas/' . $uri['base'] . '.jsf' );
			
			if ( $format == 'raw' ) {
				
				return $json_raw;
				
			} elseif ( $format == 'decode' ) {
			
				$json_decoded = $this->decode( $json_raw );
				
			}
			
		} else {
			
			$json_decoded = $this->json;
		}
				
		if ( $uri['path'] && is_object($json_decoded) ) {
			
			foreach( $uri['path'] as $p ) {
				
				if( property_exists( $json_decoded , $p ) ) {
					
					$json_decoded = $json_decoded->$p;
					
				} else {
					
					/** Entering here means the code could not follow the path. Need to decide what to do. */
					$json_decoded = null;
					break;
				}
			}
		}
				
		
		return $json_decoded;
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
	public function decode( $json_raw ) {
		
		$json_decoded = json_decode( $json_raw );
				
		return $json_decoded;
		
	}
	
	
	/**
	 * Locate $ref pointers and replace with JSON.
	 *
	 * @since 0.0.1
	 *
	 * @uses TW_JsonExtended::json The JSON object property of this instance that this method searches.
	 * @uses TW_JsonExtended::iterate() Recursively searches JSON object property of this instance.
	 *
	 * @return boolean Returns JSON object with resolved $ref pointers.
	 */
	public function resolveRefs( $json ) {
	
		$ref_flag = 0;
	
		$json = new RecursiveArrayIterator( $json );
		
		$iter = new RecursiveIteratorIterator( $json , 1 );
		
		foreach ( $iter as $k => $v ) {
			
			if ( is_object( $v ) ) {
				
				if ( property_exists( $v , '$ref' ) ) {
				
					$depth = $iter->getDepth();
					
					$ref_parsed = $this->parseUri( $v->{'$ref'} );
					
					$json_ref = $this->load( $ref_parsed );
					
					$iter->getSubIterator($depth)->offsetUnset( $k );
					$iter->getSubIterator($depth)->offsetSet(  $k , $json_ref );
					
					$ref_flag++;
				}
			}
		}
		
		/**
		 * Consider adding a method to a subclassed RecursiveIteratorIterator that 
		 * converts the JSON object back to plain JSON.
		 */
		
		if ( $ref_flag > 0 ) {
			$this->resolveRefs( json_decode( json_encode( $iter->getInnerIterator() ) ) );
		}
		
		return json_decode( json_encode( $iter->getInnerIterator() ) );
	}
	
	
	/**
	 *
	 *
	 * @todo Include a way to determine what scheme to use to load the URI (i.e http, file, etc).
	 *
	 * @return array $uri A parsed URI to a JSON object.
	 */
	protected function parseUri( $uri ) {
		
		// var_dump($uri);
		
		$uri_base = null;
		$uri_path = null;
		$uri_scheme = null;
		
		if ( strpos( $uri , '#' ) === 0 ) {    /** URI refers to an JSON object relative to the root */
		
			// echo '<p>URI refers to an object relative to the JSON Schema\'s root object.</p>';
		
			$uri_base = substr( $uri , 0 , 1 );
			
			$uri_path = explode( '/' , substr( $uri , 2 ) );
			
			// echo '<p>The base of the URI is:</p>';
			
			// var_dump($uri_base);
			
			// echo '<p>The path of the URI is:</p>';
			
			// var_dump($uri_path);
			
		} else {    /** URI refers to an external JSON Schema */
		
			if ( strpos( $uri , '#' ) !== false ) {    /** URI refers to an object within the externally referenced JSON Schema */
				
				list( $uri_base , $uri_path ) = explode( '#' , $uri );
				
				$uri_path = explode( '/' , ltrim( $uri_path , '/' ) );
				
				// echo '<p>The base of the URI is:</p>';
			
				// var_dump($uri_base);
				
				// echo '<p>The path of the URI is:</p>';
				
				// var_dump($uri_path);
				
			} else {    /** URI simply refers to an external JSON Schema document */
			
				$uri_base = $uri;
				
				// echo '<p>URI refers to an external JSON Schema.</p>';
				
				// echo '<p>The path of the URI is:</p>';
				
				// var_dump($uri);
			}
		}
				
		$uri = array(
			'base' => $uri_base,
			'path' => $uri_path
		);
		
		// echo '<p>The URI array for the JSON Schema is:</p>';
		// var_dump($uri);
		
		return $uri;
		
	}
	
	
	protected function setupIterator( $json , $iter = 'RecursiveArrayIterator' , $iter_iter = 'RecursiveIteratorIterator' , $mode = 'SELF_FIRST' ) {
		
		if ( !is_array( $json ) && !is_object( $json ) ) {
		
			return false;
		}
		
		if ( class_exists( $iter ) ) {
		
			$iterator = new $iter( $json );
			
			if ( class_exists( $iter_iter ) ) {
				$iterator_iterator = new $iter_iter( $iterator , constant( 'RecursiveIteratorIterator::'.$mode ) );
				
			} else {
			
				return false;
			}
			
		} else {
		
			return false;
		}
		
		return $iterator_iterator;
	}
	
	
	protected function buildFieldPath( $field_path , $field_key , $depth , $json ) {
		
		$d = 0;
		
		do {
		
			if ( $d ) {
				$d = $d + 1;
			}
			
			$field_path[] = $json->getSubIterator( $d )->key();
			$d = $d + 1;
			
		} while ( $d < $depth );
		
		if ( $field_path[ count( $field_path ) - 1 ] !== $field_key ) {
			
			$field_path[] = $field_key;
		}
		var_dump($field_path);
		return $field_path;
	}
	
	
	protected function setField( $field_path , &$fields , $field_key , $field ) {
		
		foreach ( $field_path as $index => $path ) {
		
			if ( !array_key_exists( $path , $fields ) ) {
				
				$fields[ $path ] = array();
			}
			
			$fields =& $fields[ $path ];
			
			if ( $index === count( $field_path ) - 1 ) {
				
				$fields = $field;
			}
		}
		
		return true;
	}
	
	
	protected function hasSchema( $json_s ) {
		
		if ( property_exists( $this->json , 'schema' ) ) {
			
			return true;
			
		} else {
			
			return false;
		}
	}
	
	
	protected function hasProperties( $json_s ) {
		
		if ( !property_exists( $json_s , 'type' ) || !property_exists( $json_s , 'properties' ) ) {
			
			/** JSON 'type' property is not set to object and/or 'properties' property contains no data */
			//echo '<p>This object has no type or properties.</p>';
			return false;
			
		} else {
		
			if ( $json_s->type !== 'object' || empty( $json_s->properties ) ) {
				
				/** JSON is missing 'type' and/or 'properties' properties */
				//echo '<p>This object is not the correct type or has no properties.</p>';
				return false;
			}
		}
		
		//echo '<p>This object has properties.</p>';
		return true;
	}
	
	
	protected function isField( $json_s ) {
		//var_dump($json_s);
		
		if ( !is_object( $json_s ) || !property_exists( $json_s , 'type' ) ) {
			
			/** JSON snippet is not a PHP object and/or is missing a 'type' property */
			return false;
								
		} else {
			
			if ( $json_s->type === 'object' ) {
				
				/** JSON snippet's 'type' property is something other than 'object' */
				return false;
			}
		}
		
		/** JSON snippet is a JSON Schema Object */
		return true;
	}
}

?>