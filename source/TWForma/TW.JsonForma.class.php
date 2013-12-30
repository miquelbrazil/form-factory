<?php

class TW_JsonForma extends TW_JsonSchema
{

	private $html_form = '';
	
	public $forma_fields = array();
	
	public $fields = array();
	
	public $form = '';
	
	public $form_fields = array();
	
	public $forma_directory = array();
	
	public $forma = array();
	
	public $forma_fields_defined = array();
	
	
	public function __construct( $json ) {
		
		parent::__construct( $json );
		
		$this->forma = $this->getForma( $this->json->form->fields );
		
		$this->forma_directory = $this->getForma_directory( $this->forma );
		
		/*
		echo '<h3>Forma Explicit Definiton Directory:</h3>';
		var_dump( $this->forma_directory );
		*/
		
		$this->form_fields = $this->getForma_fields();
		
		echo '<h3>Form Fields:</h3>';
		var_dump( $this->form_fields );
		
		// $this->forma_fields_defined = $this->getFields_explicit( $this->formas );
	}
	
	
	public function render() {
	
		$this->getFields( $this->form_fields );
		
		/** assumes 'form' property exists in JSON Forma object */
		/*
		foreach( $this->json->form->fields as $field_meta ) {
		
			$field_id = $this->getField_id( $field_meta );
			
			
			$field_path_def = $fields_p[ $field_id ]['def'];
			
			
			if ( empty( $fields_p[ $field_id ]['dest'] ) ) {
				
				$field_path_dest = $field_path_def;
				
			} else {
				
				$field_path_dest = $fields_p[ $field_id ]['dest'];
			}
			
			
			$f = $this->getField_schema( $field_path_def );
			
			
			if ( is_object( $f ) ) {
				
				if ( $this->hasProperties( $f ) ) {
					
					$f_iter = $this->getIterator( $f->properties );
					
					foreach( $f_iter as $k => $v ) {
					
						//var_dump($v);
					
						if ( $this->isField( $v ) ) {
						
							//var_dump($v);
						
							$field_loc = $this->getField_path( $field_path_dest , $k , $f_iter->getDepth() , $f_iter );
							
							if ( !in_array( implode( '.' , $field_loc) , $fields_l ) ) {
							
								//$field = array( "name" => $k , "def" => $v , "params" => $field_meta );
								
								$field = array( "path" => $field_loc , "schema" => $v );
								
								$field_html = $this->getField( $field );
								
								$this->setField( $field_loc , $this->fields , $k , $field_html  );
							}
						}
					}
					
				} else {
					
					//$field = array( "name" => $field_path_def[ count($field_path_def)-1 ] , "def" => $f , "params" => $field_meta );
					
					$field = array( "path" => $field_path_def , "schema" => $f );
					
					//var_dump($field);
					
					$html = $this->getField( $field );
					
					$this->setField( $field_path_dest , $this->fields , $field_path_def[ count($field_path_def)-1 ] , $html  );
				}
				
			} else {
				
				echo '<p>Field should be an object.</p>';
			}
		}
		*/
		
		/*
		$form = json_encode($this->fields);
		$form = json_decode($form);
		
		//var_dump($form);
		
		// setup iterator to collapse fields into groups and collections
		$form_iter = $this->getIterator( $form , 'RecursiveArrayIterator' , 'RecursiveIteratorIterator' , 'CHILD_FIRST' );
				
		// loop over iterator looking for groups and collections to collapse
		foreach ( $form_iter as $k => $v ) {
			
			// reset variable that stores html string from previous loop
			$html = '';
			
			// establish the path to find the forma params for the field we are on
			$path_array = $this->getField_path( array() , $k , $form_iter->getDepth() , $form_iter );
			$path = implode( '.' , $path_array );
			$path_parent = implode( '.' , array_splice( $path_array , 0 , -1 ) );
			
			$tags['group'] = $this->getTags( $path , 'group' , $form_iter->getDepth() );
			$tags['collection'] = $this->getTags( $path , 'collection' );
			
			//var_dump($path);
			//var_dump($form_iter->getDepth());
			//var_dump($k);
			//var_dump($v);
			
			// collapse any groups below the root level
			if ( $form_iter->getDepth() > 0 ) {
				
				if ( is_object( $v ) ) {
					
					//var_dump( $path );
					
					if ( $this->is_fieldGrouped( $path ) ) {
						
						//echo '<p>Fields will be grouped as a single unit.</p>';
						
						// collapse fields
						foreach ( $v as $value ) {
							
							$html .= $value;
						}
						
						if ( !empty( $tags['group'] ) ) {
						
							$html = $tags['group']['open'] . $html . $tags['group']['close'];
						}
						
					} else {
						
						//echo '<p>Fields will be grouped as individual elements.</p>';
						
						// collapse fields
						foreach ( $v as $value ) {
							
							if ( !empty( $tags['group'] ) ) {
						
								$value = $tags['group']['open'] . $value . $tags['group']['close'];
							}
							
							$html .= $value;
						}
					}
					
					// the parent should determine if the field should be grouped as unit or individually
					
					$form_iter->getSubIterator($form_iter->getDepth())->offsetSet( $k , $html );
				}
				
			} elseif ( $form_iter->getDepth() == 0 ) {
				
				if ( is_object( $v ) ) {
					
					//var_dump( $path );
					
					if ( $this->is_fieldGrouped( $path ) ) {
						
						//echo '<p>Fields will be grouped as a single unit.</p>';
						
						// collapse fields
						foreach ( $v as $value ) {
							
							$html .= $value;
						}
						
						if ( !empty( $tags['group'] ) ) {
						
							$html = $tags['group']['open'] . $html . $tags['group']['close'];
						}
						
					} else {
						
						//echo '<p>Fields will be grouped as individual elements.</p>';
						
						// collapse fields
						foreach ( $v as $value ) {
							
							if ( !empty( $tags['group'] ) ) {
						
								$value = $tags['group']['open'] . $value . $tags['group']['close'];
							}
							
							$html .= $value;
						}
					}
					
					if ( !empty( $tags['collection'] ) ) {
						
						$html = $tags['collection']['open'] . $html . $tags['collection']['close'];
					}
					
					// the parent should determine if the field should be grouped as unit or individually
					
					$form_iter->getSubIterator($form_iter->getDepth())->offsetSet( $k , $html );
					
				} elseif ( is_string( $v ) ) {
					
					
					if ( !empty( $tags['group'] ) ) {
					
						$html = $tags['group']['open'] . $v . $tags['group']['close'];
					}
					
					if ( !empty( $tags['collection'] ) ) {
						
						$html = $tags['collection']['open'] . $html . $tags['collection']['close'];
					}
					
					$form_iter->getSubIterator($form_iter->getDepth())->offsetSet( $k , $html );
				}
			}
		}
		
		//var_dump( iterator_to_array( $form_iter->getInnerIterator() ) );
		
		echo implode( iterator_to_array( $form_iter->getInnerIterator() ) );
		
		*/
		
	}
	
	
	/**
	 * Get Fields
	 *
	 *
	 */
	private function getFields( $fields ) {
		
		foreach ( $fields as $id => $field ) {
			
			// setup variables
			
			$html = $this->getField( $field );
			
			var_dump($html);
			
			// set field
			
			$this->setField( $field[ "path_scheme" ] , $this->fields , $field["path_scheme"][ count($field["path_scheme"])-1 ] , $html  );
		}
	}
	
	
	private function getFields_group() {
		
		
	}
	
	
	private function setField( $field_path , &$fields , $field_key , $field ) {
		
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
	
	
	/**
	 * Determines if a group of fields should be wrapped as a complete unit or as individual elements
	 */
	private function is_fieldGrouped( $path ) {
	
		if ( array_key_exists( $path , $this->forma ) ) {
		
			$forma = $this->forma[ $path ];
			
			if ( property_exists( $forma['params'] , 'group' ) ) {
				
				$forma_params = $forma['params']->group;
				
				if ( !$forma_params ) {
					
					return false;
				}
			}
		}
		
		return true;
	}
	
	
	/**
	 * types of tags
	 * tag_collection - Collection Tag - wraps the entire collection of similar fields - default: <fieldset>
	 * tag_group - Group Tag - wraps a group of related label/input pairs - default: <dl> ( could also be <ol>, <ul>)
	 * tag_pair - Pair Tag - wraps label/input pairs - no default becuase this would be used for nested list or tables <tr>
	 * tag_label - Label Tag - wraps a input field label - default: <dt> ( could also be <li> )
	 * tag_input - Input Tag - wraps a input field - default: <dd> ( could also be <li> )
	 */
	private function getTags( $forma , $tag_type , $depth = 0 ) {
		
		$tags = array();
		
		switch ( $tag_type ) {
			
			case 'input':
				$tags = $this->getTags_input( $forma );
				break;
				
			case 'label':
				$tags = $this->getTags_label( $forma );
				break;
			
			case 'pair':
				$tags = $this->getTags_pair( $forma );
				break;
			
			case 'group':
				$tags = $this->getTags_group( $forma , $depth );
				break;
				
			case 'collection':
				$path = $forma;
				$tags = $this->getTags_collection( $path , $depth );
				break;
			
		}
		
		return $tags;
	}
	
	
	/**
	 * default wrap tag for labels is <dt>
	 */
	private function getTags_label( $field_params ) {
		
		$tag_label = array();
		
		if ( property_exists( $field_params , 'tag_input' ) ) {
			
			if ( !property_exists( $field_params , 'tag_label' ) ) {
				
				$tag = $field_params->tag_input;
				
			} else {
				
				$tag = $field_params->tag_label;
			}
			
		} elseif ( property_exists( $field_params , 'tag_label' ) ) {
			
			$tag = $field_params->tag_label;
			
		} else {
			
			$tag = 'dt';
			
		}
		
		$tag_label['open'] = '<' . $tag . '>';
		$tag_label['close'] = '</' . $tag . '>';
		
		return $tag_label;
	}
	
	
	/**
	 * default wrap tag for input is <dd>
	 */
	private function getTags_input( $forma ) {
		
		$tag_input = array();
		
		if ( property_exists( $forma , 'tag_label' ) ) {
			
			if ( !property_exists( $forma , 'tag_input' ) ) {
				
				$tag = $forma->tag_label;
				
			} else {
				
				$tag = $forma->tag_input;
			}
			
		} elseif ( property_exists( $forma , 'tag_input' ) ) {
			
			$tag = $forma->tag_input;
			
		} else {
			
			$tag = 'dd';
			
		}
		
		$tag_input['open'] = '<' . $tag . '>';
		$tag_input['close'] = '</' . $tag . '>';
		
		return $tag_input;
	}
	
	
	private function getTags_group( $path , $depth = 0 ) {
		
		$tag_group = array();
		
		$tag = '';
		
		/**
		 * tags can appear in one of three locations:
		 * 1. explicitly named in a child params object of the given path
		 * 2. if the field was regrouped then the path will match an imploded 'dest' string
		 * 3. inherited from the parent object
		 */
		 
		$params = $this->getField_params( $path );
		
		if ( property_exists( $params , 'tag_group' ) ) {
			
			$forma_tag_group = $params->tag_group;
			
			$tag = $forma_tag_group;
			
		}
		
		/**
		 * sets default group tag for root level field elements
		 * returns an empty array otherwise
		 */
		if ( empty( $tag ) && $depth == 0 ) {
			
			$tag = 'dl';
		}
		
		if ( $tag ) {
			
			$tag_group['open'] = '<' . $tag . '>';
			$tag_group['close'] = '</' . $tag . '>';
		}
		
		return $tag_group;
	}
	
	
	/**
	 * Method assumes every Forma object has a 'params' object
	 * expects path to be an array
	 */
	private function getField_params( $path ) {
		
		//var_dump($path);
		
		echo '<p>Attempting to locate field paramters in Formas object using path: <pre><strong>' . implode( '.' , $path ) . '</strong></pre></p>';
		
		// setup variables
		
		$params = new stdClass;
		
		$formas = $this->formas;
		
		if ( is_array( $path ) ) {
			$path_string = implode( '.' , $path );
		} else {
			$path_string = $path;
		}
		
		
		$is_regrouped = $this->is_fieldReGrouped( $path_string );
		
		
		if ( $is_regrouped ) {
			
			$path = $is_regrouped;
			
			$params = $formas[ $path ]['params'];
			
		} elseif ( array_key_exists( $path_string , $formas ) ) {
			
			$params = $formas[ $path_string ]['params'];
			
		} else {
			
			$path = array_splice( $path , 0 , -1 );
			
			if ( !empty( $path ) ) {
				
				$params = $this->getField_params( $path );
			}
		}
		
		//var_dump($params);
		
		return $params;
	}
	
	
	private function is_fieldReGrouped( $path ) {
		
		if ( is_array( $path ) ) {
			$path = implode( '.' , $path );
		}
		
		$a_forma = $this->formas;
		
		foreach( $a_forma as $forma ) {
			
			$forma_path = implode( '.' , $forma['dest'] );
			
			if ( $path == $forma_path ) {
				
				return implode( '.' , $forma['def'] );
			}
		}
		
		return false;
	}
	
	
	private function getTags_collection( $path , $depth = 0 ) {
		
		$tag_collection = array();
		
		$tag = '';
		
		if ( array_key_exists( $path , $this->forma ) ) {
		
			$forma = $this->forma[ $path ];
			
			if ( property_exists( $forma['params'] , 'tag_collection' ) ) {
			
				$forma_tag_collection = $forma['params']->tag_collection;
				
				if ( is_string( $forma_tag_collection ) ) {
				
					$tag = $forma_tag_collection;
					
				} elseif ( $forma_tag_collection === true ) {
					
					$tag = 'fieldset';	
				}
				
			} else {
				
				$tag = 'fieldset';
			}
			
		} else {
			
			$tag = 'fieldset';
			
		}
		
		if ( $tag ) {
			
			$tag_collection['open'] = '<' . $tag . '>';
			$tag_collection['close'] = '</' . $tag . '>';
		}
		
		return $tag_collection;
	}
	
	
	private function wrapFields ( &$fields ) {
	
		$more = false;
		
		foreach ( $fields as $field ) {
			
			if ( is_array( $field ) ) {
				
				$this->wrapFields( $field );
				
			} else {
				
				$fields[ $key ] = '<fieldset>' . $field . '</fieldset>';
			}
		}
	}
	
	
	/**
	 *
	 */
	private function getField( $field ) {
	
		// setup variables
		
		/*
		echo '<h3>Field Definition</h3>:';
		var_dump( $field );
		*/
		
		$f_html = '';
		
		//$params = new stdClass;
		
		
		// get Field paramaters
		/*
		if ( array_key_exists( 'path' , $field ) ) {
		
			$params = $this->getField_params( $field['path'] );
		}
		*/
		/*
		echo '<h3>Field Parameters</h3>:';
		var_dump($params);
		*/
		
		// add params to the Field array
		
		//$field['params'] = $params;
		
		/*
		echo '<h3>Redefined Field Array:</h3>';
		var_dump($field);
		*/
		
		// determine field type
		
		// create a isFieldDef() function to determine that all parts are present
	
		$field_type = $this->getField_type( $field );
		
		
		/*
		echo '<h3>Field Type:</h3>';
		var_dump( $field_type );
		*/
		
		// get opening and closing tags for label/input pairs
		
		$tags['pair'] = $this->getTags( $field['params'] , 'pair' );
		
		
		// select method for designing form field based on field type
		
		switch( $field_type ) {
		
			case 'text':
				$f_html = $this->getField_text( $field );
				break;
			
			case 'checkbox':
				$f_html = '<p>Render checkboxes</p>';
				break;
				
			case 'select':
				$f_html = '<p>Render select field</p>';
				break;
			
			default:
				$f_html = '<p>I don\'t understand this field type.</p>';
		}
		
		// wrap collapsed label/input pair in tags if present, simply collapse if not
		
		if ( !empty( $tags['pair'] ) ) {
			
			$f_html = $tags['pair']['open'] . implode( $f_html ) . $tags['pair']['close'];
			
		} else {
			
			if ( is_array( $f_html ) ) {
				
				$f_html = implode( $f_html );
			}
		}
		
		
		// return field HTML to calling method
			
		return $f_html;
	}
	
	
	
	private function getForma( $forma ) {
	
		// check to ensure Forma object is valid
		
		return $forma;
	}
	
	
	private function getForma_fields() {
		
		$fields = array();
		
		foreach( $this->forma as $formi ) {
		
			$id = $this->getField_id( $formi );    // could this use getField_path() method?
			
			$path_scheme = $this->getField_path( $formi );
			
			$scheme = $this->getField_scheme( $path_scheme );
			
			/*
			echo '<h3>Formi:</h3>';
			var_dump( $formi );
			
			echo '<h3>Scheme:</h3>';
			var_dump( $scheme );
			*/
			
			// package field information
			
			if ( is_object( $scheme ) ) {    // sanity check to ensure field is an object
				
				if ( $this->hasProperties( $scheme ) ) {    //sanity check to ensure there are properties to iterate over
				
					/*
					 * Uses SPL Iterators to loop through because it would have
					 * otherwise required a recursive function. This allows the 
					 * loop to remain inline.
					 *
					 * Can the foreach loop be moved to a subclassed Iterator?
					 */
					
					// only want to work singular objects, not its properties
					
					$scheme_iterator = $this->getIterator( $scheme->properties );
					
					foreach( $scheme_iterator as $id => $scheme ) {
						
						if ( $this->isField( $scheme ) ) {
						
							$path = $this->getField_path( $path_scheme , $id , $scheme_iterator->getDepth() , $scheme_iterator );
							
							if ( !in_array( implode( '.' , $path ) , $this->forma_directory ) ) {
								
								$fields[ implode( '.' , $path ) ] = array( "path_scheme" => $path , "scheme" => $scheme , "params" => $formi );
							}
						}
					}
					
				} else {
					
					$fields[ $id ] = array( "path_scheme" => $path_scheme , "scheme" => $scheme , "params" => $formi );
				}
				
			} else {
				
				// log a WP_Error because $field should be an object
			}
			
			
			
			
			/*
			if ( is_object( $formi ) ) {
				
				if ( property_exists( $formi , 'regroup' ) ) {
					
					$path_field = $this->getField_path( $formi->regroup );
					
					if ( $path_field ) {
						
						$fields[ $id ]['path_field'] = $path_field;
					}
					
				} else {
					
					$fields[ $id ]['path_field'] = $path_scheme;
				}
				
				// $fields[ $id ]['params'] = $formi;
				
			} else {
				
				$fields[ $id ]['path_field'] = $path_scheme;
				
				// $fields[ $id ]['params'] = new stdClass();
			}
			*/
		}
		
		return $fields;
	}
	
	/**
	 * Get collection of fields that are explicitly defined by the Formas.
	 * Prevents them from being redefined by the default Schema definition.
	 */
	private function getForma_directory( $forma ) {
		
		$listing = array();
		
		foreach ( $forma as $formi ) {
		
			$path = $this->getField_path( $formi );
			
			if ( count( $path ) > 1 ) {
				
				$listing[] = implode( '.' , $path );
			}
		}
		
		return $listing;
	}
	
	
	/** get the name/id of the field to find it's path */
	/**
	 * mixed $field
	 */
	private function getField_id ( $field ) {
		
		$id = '';
		
		if ( is_object( $field ) ) {
		
			// assumes key property is present because it doesn't provide an else
					
			if ( property_exists( $field , 'key' ) ) {
				
				$id = $field->key;
			}
			
		} elseif ( is_string( $field ) ) {
		
			if ( $field === '*' ) {
				
				$id = 'all';
				
			} else {
			
				$id = $field;
			}
			
		} else {
			
			$id = 'No ID';
		}
		
		return $id;		
	}
	
	
	private function getField_type( $field ) {
	
		$field_type = '';
		
		if ( property_exists( $field['params'] , 'type' ) ) {
			
			/** include error-checking to ensure type valid */
			$field_type = $field['params']->type;
			
		} else {
			
			if ( property_exists( $field['scheme'] , 'type' ) ) {
			
				$type = $field['scheme']->type;
				
				if ( $type == 'string' || $type == 'number' || $type == 'integer' ) {
					
					if ( property_exists( $field['scheme'] , 'enum' ) ) {
						
						$field_type = 'select';
						
					} else {
					
						$field_type = 'text';
					}
					
				} elseif ( $type == 'boolean' || $type == 'array' ) {
					
					$field_type = 'checkbox';
				}
			}
		}
		
		if ( empty( $field_type ) ) {
			
			$field_type = '<p>Field type could not be determined.</p>';
		}
		
		return $field_type;
	}
	
	
	private function getField_text( $field ) {
	
		$html = array();
		$tags = array();
		
		$id = array_pop( $field['path_scheme'] );
		
		$tags['input'] = $this->getTags( $field['params'] , 'input' );
		
		if ( $this->showLabel( $field['params'] ) ) {
			
			$html['label'] = $this->getField_label( $field );
		}
		
		
		$html['input'] = '<input type="text" id="' . $id . '" name="' . $id . '" />';
		
		if ( !empty( $tags['input'] ) ) {
			
			$html['input'] = $tags['input']['open'] . $html['input'] . $tags['input']['close'];
		}
		
		return $html;
	}
	
	
	private function getTags_pair( $forma ) {
		
		$tag_pair = array();
		
		$tag = '';
		
		if ( property_exists( $forma , 'tag_pair' ) ) {
			
			$tag = $forma->tag_pair;
			
		} else {
			
			$tag = '';    // explicitly sets default tag
		}
		
		if ( !empty( $tag ) ) {
			
			$tag_pair['open'] = '<' . $tag . '>';
			$tag_pair['close'] = '</' . $tag . '>';
		}
		
		return $tag_pair;
	}
	
	
	private function showLabel( $f_params ) {
	
		if ( property_exists( $f_params , 'label' ) ) {
			
			if ( !$f_params->label ) {
				
				return false;
			}
		}
		
		return true;
	}
	
	
	/**
	 * Label values are defined in the 'title' property of the JSON Schema's field definition
	 * These values can be redefined in the Forma object
	 * this method checks the redefinition first and if it isn't present it uses the title property
	 * if this isn't present it uses the generated name of the field as the label
	 */
	private function getField_label( $field ) {
	
		$html = '';
		
		$for_id = array_pop( $field['path_scheme'] );
		
		$tags = $this->getTags( $field['params'] , 'label' );
		
		
		if ( property_exists( $field['params'] , 'label' ) ) {
			
			if ( is_string( $field['params']->label ) ) {
				
				$label = $field['params']->label;
				
			} else {
				
				/** redefined label property is not a string **/
			}
			
		} elseif ( property_exists( $field['scheme'] , 'title' ) ) {
			
			$label = $field['scheme']->title;
			
		} else {
			
			$label = array_pop( $field['path_scheme'] );
		}
		
		
		
		$html = '<label for="' . $for_id . '">' . $label . '</label>';
		
		if ( !empty($tags) ) {
			
			$html = $tags['open'] . $html . $tags['close'];
		}
		
		
		return $html;
	}
	
	
	public static function triageType( $action , $json ) {
	
		echo '<div style="background-color : lime ; padding : 10px;"><p>Triaging Form</p>';
		var_dump( $json );
		echo '</div>';
		
		$demo = new TW_JsonExtended( $json , 'object' );
		
		var_dump($demo->json);
		
		$action = array( 
			'search' => array( 'method' => 'value' , 'lookup' => 'type' ),
			'callback' => array( 'TW_Form' , 'handleFieldType' )
		);
		
		$demo->iterate( $action );
		
	}
	
	public static function handleFieldType( $action , $json ) {
		
		echo 'Do something!';
		var_dump($json);
		
		switch ( $json['type'] ) {
			
			case 'object':
				echo 'Rendering a fieldset.<br />';
				
				if ( empty( self::$html_form ) ) {
					
					self::$html_form = '<fieldset><legend>' . $json['forma']['title'] . '</legend>';    // assume fieldset is being created for the first time.
					
				} else {
					
					self::$html_form .= '</fieldset><fieldset><legend>' . $json['forma']['title'] . '</legend>';
					
				}
				
				$test = new TW_JsonExtended( $json['forma']['properties'] , 'object' );
				
				$object_action = array( 
					'search' => array( 'method' => 'value' , 'lookup' => 'type' ),
					'callback' => array( 'TW_Form' , 'handleFieldType' )
				);
				
				$test->iterate( $object_action );
				
				break;
			
			case 'string':
				echo 'Rendering a text field.<br />';
				
				self::$html_form .= $json['forma']['title'] . ': <input type="text" id="" name="" placeholder="" value="" /><br />';
				
				break;
				
			default:
				echo 'I haven\'t learned how to deal with this field type yet<br />';
		}
		
	}
	
	
	/**
	 * Get the path of the given field object (could be a Formi or Schemi)
	 */
	private function getField_path( $field , $id = '' , $depth = 0 , $scheme = '{}'  ) {
		
		if ( is_object( $field ) ) {
					
			if ( property_exists( $field , 'key' ) ) {
				
				$path = explode( '.' , $field->key );
			}
			
		} elseif ( is_string( $field ) ) {
		
			if ( $field === '*' ) {
				
				$path = array();
				
			} else {
			
				$path = explode( '.' , $field );
			}
			
		} elseif ( is_array( $field ) ) {
			
			$path = $field;
			
			$d = 0;
			do {
			
				if ( $d ) {
					$d = $d + 1;
				}
				
				$path[] = $scheme->getSubIterator( $d )->key();
				$d = $d + 1;
				
			} while ( $d < $depth );
			
			if ( $path[ count( $path ) - 1 ] !== $id ) {
				
				$path[] = $id;
			}
		
		} else {
			
			return false;
		}
		
		/** add code to return a string form of the path */
		
		return $path;
	}
	
	/**
	 *
	 * @param array $path Each array element is a pointer towards the Schema object
	 */
	private function getField_scheme( $path ) {
		
		if ( $this->hasSchema( $this->json ) ) {    // sanity check to ensure Schema object is present
		
			$schema = $this->json->schema;
			
			// return this if path is equal to *
			
			foreach( $path as $segment ) {
				
				if ( $this->hasProperties( $schema ) ) {    // sanity check to ensure there are properties to retrieve
					
					$schema = $schema->properties->$segment;
					
				} else {    // could not drill down further because next segment doesn't have any properties
					
					return $schema;
				}
			}
			
		} else {
			
			// log a WP_Error because Schema object could not be found
		}
		
		return $schema;
	}
}

?>