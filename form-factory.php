<?php

/*
Plugin Name:    Form Factory
Plugin URI:     http://thingwondurful.com/code/form-factory
Description:    <strong>thingwondurful internal plugin</strong> for rendering forms and fields consistently across web apps
Version:        0.0.1
Author:         Miquel Brazil [thingwone]
Author URI:     http://thingwone.com/aboutme
License:        undefined
*/

class TW_FormFactory
{

	private $json;
	private $json_object;
	private $json_form;
	private $json_schema;
	
	
	public function __construct( $json_obj ) {
		
		$this->json = $this->json_load_data( $json_obj );
		$this->json_object = $this->json_decode_data( $this->json );
		
	}
	
	
	public function json_build_form() {
	
		$this->json_locate_refs( $this->json_object );
		
	}
	
	
	/**
	 * Traverse JSON schema to find any $ref pointers
	 *
	 * * This function currently implements a recursive function
	 * * I feel like it should use the SPL iterative functions
	 * * but I'm having trouble implementing them and all my reading
	 * * says that it would actually be slower.
	 *
	 * @uses json_expand_refs
	 */
	
	public function json_locate_refs( $json_obj , $header = "" ) {
		
		// locate ref pointers and call json_expand_refs t0 replace JSON Schema Object Node with JSON Schema
		
		if ( isset( $json_obj->schema ) ) {
		
			// we are at the root level of properties
			
			foreach ( $json_obj->schema->properties as $key => $value ) {
				
				if ( isset( $value->properties ) ) {
					//echo $key . ' has additional properties.<br />';
					$this->json_locate_refs( $value , $key );
				}
				
				if ( isset( $value->{'$ref'} ) ) {
				
					//echo 'I found a JSON ref pointer in ' . $key . '.<br />';
					
					$ref = $this->json_parse_ref( $value->{'$ref'} );
					
					$ref_schema = $this->json_expand_ref( $ref );
					
					$json_obj->schema->properties->$key = $ref_schema->properties;
					
				}
			}
			
		} else {
		
			foreach ( $json_obj->properties as $key => $value ) {
			
				//var_dump($key);
				//var_dump($value);
				
				if ( isset( $value->properties ) ) {
					//echo $key . ' has additional properties.<br />';
					$this->json_locate_refs( $value );
				}
				
				if ( $key == '$ref' ) {
					//echo 'I found a JSON ref pointer in ' . $header . '.<br />';
				}
				
			}
			
		}
		
		//var_dump($schema_obj);
		
	}
	
	
	public function json_parse_ref( $ref ) {
	
		var_dump($ref);
		
		if ( $ref[0] == '#'  ) {
		
			echo 'Internal document definitions reference.<br />';
			
			echo 'ref is an document relative pointer<br />';
			
		} else {
		
			echo 'External JSON document reference<br />';
			
			if ( strpos( $ref , '#' ) ) {
				
				echo 'External JSON document has an internal Definitions reference.<br />';
				
				$doc = strstr( $ref , '#' , true );
				
			} else {
			
				echo 'External JSON document is stand-alone.<br />';
				
				$doc = $ref;
				
			}
		}
		
		return $doc;
		
	}
	
	
	/**
	 * Retrieves JSON Schema document from template directory.
	 *
	 * Need to build in error handling for malformed JSON Schema documents
	 */
	
	public function json_load_data( $json_obj ) {
	
		$json_data = file_get_contents( get_template_directory() . '/schemas/' . $json_obj . '.json' );
				
		return $json_data;
	}
	
	
	private function json_decode_data( $json_data ) {
		
		$json_decoded = json_decode( $json_data );
		
		return $json_decoded;
		
	}
	
	
	/**
	 * replace JSON Schema ref pointer by:
	 *
	 *     1) calling json_parse_ref find replacement JSON Schema Object
	 *     2) calling json_get_schema to pull JSON string into a variable
	 *     3) replacing ref pointer with JSON string
	 *
	 * @uses json_parse_ref
	 * @uses json_get_schema
	 */
	
	public function json_expand_ref( $ref ) {
		
		$schema = $this->json_load_data( $ref );
		
		$schema = $this->json_decode_data( $schema );
		
		//var_dump($schema);
		
		return $schema;
		
	}

}











/*
foreach ( $data->properties as $key => $value ) {
	$list = get_object_vars($value);
	
	if ( array_key_exists( '$ref' , $list ) ) {
		// echo 'There is a reference pointer constraint in ' . $key . ' property.<br />';
		
		//var_dump($list[ '$ref' ]);*/
		
		/**
		 *    found a ref pointer...now need to find the file
		 *    The file location should be a string value
		 *    Will always be contained in the 'schemas' folder of the theme directory.
		 *    Needs to handle uri's that are in subfolders.
		 *    Needs to only search for external file if pointer doesn't start with #.
		 *    RULE: ref pointer objects will be found under 'definitions' object when ref pointer starts
		 *          with # and is located in the same document.
		 *    RULE: All external schema URI's will be relative to the 'schema' directory.
		 *    RULE: External schemas with multiple definitions will be references by a pointer structured as
		 *          schema.json#definition
		 *    TODO: Need code to handle these three different situations.
		 */
		
		/*$pointer = file_get_contents( get_template_directory() . '/schemas/' . $list['$ref'] );
		
		//var_dump($pointer);
		
		$pointer = json_decode($pointer);
		
		//var_dump($pointer);
		
		$data->properties->$key = $pointer;
		
	} else {
		echo 'There is no reference pointer constraint in ' . $key . ' property.<br />';
	}
}*/


//var_dump($data);

?>