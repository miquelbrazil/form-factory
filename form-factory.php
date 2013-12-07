<?php

/*
Plugin Name:    Form Factory
Plugin URI:     http://thingwondurful.com/code/form-factory
Description:    <strong>thingwondurful internal plugin</strong> for rendering forms and fields consistently across web apps
Version:        0.2.5
Author:         Miquel Brazil [thingwone]
Author URI:     http://thingwone.com/aboutme
License:        undefined
*/

require_once('source/base/TW.JsonSchema.class.php');
require_once('source/TWForma/TW.JsonForma.class.php');
require_once('source/iterators/TW.JsonRefIterator.class.php');
require_once('source/iterators/TW.JsonRefIteratorIterator.class.php');

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
}