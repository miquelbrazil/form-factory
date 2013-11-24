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

require_once('source/base/TW.JsonExtended.class.php');

class TW_FormFactory
{

	private $json;
	private $json_object;
	private $json_form;
	private $json_schema;
	
	private $breadcrumbs;
	
	private $html_form;
	
	
	public function __construct( $json_obj ) {
		
		$this->json = $this->json_load_data( $json_obj );
		$this->json_object = $this->json_decode_data( $this->json );
		$this->breadcrumbs = array();
		
	}
	
	
	public function jsonTestSchemaExpansion() {
	
		//var_dump($this->json_object);
	
		$this->json_locate_refs( $this->json_object );
		
		//var_dump($this->json_object);
		
		return $this->json_object;
		
	}
	
	
	/**
	 * Traverse JSON schema to find and replace any $ref pointers
	 *
	 * * This function currently implements a recursive function
	 * * I feel like it should use the SPL iterative functions
	 * * but I'm having trouble implementing them and all my reading
	 * * says that it would actually be slower.
	 *
	 * TODO: may have to refactor slightly for practical method of
	 *       extending refs
	 *
	 * @uses json_expand_refs
	 */
	
	public function json_locate_refs( $json_obj ) {
	
		//var_dump($json_obj);
		
		//echo 'Breadcrumbs before loop:<br />';
		
		//var_dump($this->breadcrumbs);
	
		foreach ( $json_obj as $node => $value ) {
		
			if ( is_array( $value ) ) {
				
				if ( array_key_exists( 'properties' , $value ) ) {
				
					//echo '<p>Breadcrumbs inside foreach loop:</p>';
				
					//var_dump($this->breadcrumbs);
				
					//echo $node . ' has additional properties.<br />';
					
					//echo '<br />Path to additional properties: <br />';
					
					array_push( $this->breadcrumbs , $node , 'properties' );
					
					//var_dump( $this->breadcrumbs );
					
					$this->json_locate_refs( $value['properties'] );
					
				} elseif ( array_key_exists( '$ref' , $value ) ) {
					
					//echo 'I found a JSON ref pointer in ' . $node . '.<br />';
						
					$ref_parsed = $this->json_parse_ref( $value[ '$ref' ] );    // need to handle nodes
					
					array_splice( $this->breadcrumbs , -1 , 1 , array( 'properties' , $node ) );
					
					//var_dump($this->breadcrumbs);
					
					$ref_schema = $this->json_expand_ref( $ref_parsed , $this->breadcrumbs );
					
				} else {
					
					//echo '<p>' . $node . ' has no properties.</p>';
					//var_dump($path);
					
				}
				
				if ( end($json_obj) == $value  ) {
				
					//echo "BREADCRUMBS RESET<br /><br />";
					
					$this->breadcrumbs = array( 'schema' , 'properties' );
					
					//var_dump($this->breadcrumbs);
					
				}
				
			}
			
		}
		
	}
	
	
	public function json_parse_ref( $ref ) {
	
		//var_dump($ref);
		
		if ( $ref[0] == '#'  ) {
		
			// echo 'Internal document definitions reference.<br />';
			
			// echo 'ref is an document relative pointer<br />';
			
			$doc = 'definitions';
			
		} else {
		
			// echo 'External JSON document reference<br />';
			
			if ( strpos( $ref , '#' ) ) {
				
				// echo 'External JSON document has an internal Definitions reference.<br />';
				
				$doc = strstr( $ref , '#' , true );
				
			} else {
			
				// echo 'External JSON document is stand-alone.<br />';
				
				$doc = $ref;
				
			}
		}
		
		return $doc;
		
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
	
	public function json_expand_ref( $ref , $breadcrumbs ) {
	
		//var_dump($this->json_object);
		
		//var_dump($snippet);
		
		$target = &$this->json_object;
		
		$index = end($breadcrumbs);
		
		foreach ( $breadcrumbs as $crumb ) {
			
			$target = &$target[$crumb];
			
		}
		
		$schema = $this->json_load_data( $ref );
		
		$schema = $this->json_decode_data( $schema );
		
		$target = $schema;
		
		unset($target);
		
		//return $schema;
		
	}

}