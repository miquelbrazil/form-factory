<?php

class TW_JsonForma extends TW_JsonSchema
{

	private $html_form = '';
	
	public $forma_fields = array();
	
	public $fields = array();
	
	public $forma = array();
	
	
	public function render() {
	
		$fields_p = $this->getField_paths( $this->json->form->fields );
		
		$this->forma = $fields_p;
		
		$fields_l = $this->getField_limits( $fields_p );
			
		/** assumes 'form' property exists in JSON Forma object */
		foreach( $this->json->form->fields as $field_meta ) {
		
			$field_id = $this->getField_id( $field_meta );
			
			$field_path_def = $fields_p[ $field_id ]['def'];
			
			if ( empty( $fields_p[ $field_id ]['dest'] ) ) {
				
				$field_path_dest = $field_path_def;
				
			} else {
				
				$field_path_dest = $fields_p[ $field_id ]['dest'];
			}
			
			$f = $this->getFieldData( $field_path_def , $this->json );
			
			if ( is_object( $f ) ) {
				
				if ( $this->hasProperties( $f ) ) {
					
					$f_iter = $this->setupIterator( $f->properties );
					
					foreach( $f_iter as $k => $v ) {
					
						if ( $this->isField( $v ) ) {
						
							$field_loc = $this->setFieldPath( $field_path_dest , $k , $f_iter->getDepth() , $f_iter );
							
							if ( !in_array( implode( '.' , $field_loc) , $fields_l ) ) {
							
								$field = array( "name" => $k , "def" => $v , "params" => $field_meta );
								
								$field_html = $this->getField( $field );
								
								$this->setField( $field_loc , $this->fields , $k , $field_html  );
							}
						}
					}
					
				} else {
					
					$field = array( "name" => $field_path_def[ count($field_path_def)-1 ] , "def" => $f , "params" => $field_meta );
					
					//var_dump($field);
					
					$field_html = $this->getField( $field );
					
					$this->setField( $field_path_dest , $this->fields , $field_path_def[ count($field_path_def)-1 ] , $field_html  );
				}
				
			} else {
				
				echo '<p>Field should be an object.</p>';
			}
		}
		
		$form = json_encode($this->fields);
		$form = json_decode($form);
		
		var_dump($form);
		
		$form_iter = $this->setupIterator( $form , 'RecursiveArrayIterator' , 'RecursiveIteratorIterator' , 'CHILD_FIRST' );
		
		$forma = $this->json->form->fields;
		
		//var_dump($fields_p);
		
		foreach ( $form_iter as $k => $v ) {
			
			$html = '';
			
			$path = implode( '.' , $this->setFieldPath( array() , $k , $form_iter->getDepth() , $form_iter ) );
			
			$tags['group'] = $this->getTags( $path , 'group' );
			$tags['collection'] = '';
			
			
			var_dump($form_iter->getDepth());
			var_dump($k);
			var_dump($v);
				
			/** this should wrap collections not groups */
			if ( $form_iter->getDepth() == 0 ) {
			
				echo 'There are ' . count( (array)$v ) . ' element(s) in this object';
				
				if ( is_object( $v ) ) {
					
					foreach ( $v as $value ) {
						
						$html .= $value;
					}
					
				} else {
					
					$html = $v;
				}
				
				if ( !empty( $tags['group'] ) ) {
						
					$html = $tags['group']['open'] . $html . $tags['group']['close'];
				}
				
			} else {
				
				if ( is_object( $v ) ) {
					
					foreach ( $v as $value ) {
						
						$html .= $value;
					}
					
					$html = '<dl>' . $html . '</dl>';
					
				} else {
					
					$html = $v;
				}
			}
			
			
			
			$form_iter->getSubIterator($form_iter->getDepth())->offsetSet( $k , $html );
		}
		
		echo implode( iterator_to_array( $form_iter->getInnerIterator() ) );
		
	}
	
	/**
	 * types of tags
	 * tag_collection - Collection Tag - wraps the entire collection of similar fields - default: <fieldset>
	 * tag_group - Group Tag - wraps a group of related label/input pairs - default: <dl> ( could also be <ol>, <ul>)
	 * tag_pair - Pair Tag - wraps label/input pairs - no default becuase this would be used for nested list or tables <tr>
	 * tag_label - Label Tag - wraps a input field label - default: <dt> ( could also be <li> )
	 * tag_input - Input Tag - wraps a input field - default: <dd> ( could also be <li> )
	 */
	private function getTags( $forma , $tag_type ) {
		
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
				$tags = $this->getTags_group( $forma );
				break;
				
			case 'collection':
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
	
	
	private function getTags_group( $forma_path ) {
		
		$tag_group = array();
		
		$tag = '';
		
		if ( array_key_exists( $forma_path , $this->forma ) ) {
			
			if ( property_exists( $this->forma[ $forma_path ]['params'] , 'tag_group' ) ) {
				
				$tag = $this->forma[ $forma_path ]['params']->tag_group;
				
			}
		}
		
		if ( empty( $tag ) ) {
			
			$tag = 'fieldset';
		}
		
		if ( $tag ) {
			
			$tag_group['open'] = '<' . $tag . '>';
			$tag_group['close'] = '</' . $tag . '>';
		}
		
		return $tag_group;
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
	
	
	private function getField( $field ) {
	
		//var_dump( $field );
		
		$f_html = '';
	
		/** create a isFieldDef() function to determine that all parts are present */
	
		$field_type = $this->getField_type( $field );
		
		$tags['pair'] = $this->getTags( $field['params'] , 'pair' );
		
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
		
		
		if ( !empty( $tags['pair'] ) ) {
			
			$f_html = $tags['pair']['open'] . implode( $f_html ) . $tags['pair']['close'];
			
		} else {
			
			if ( is_array( $f_html ) ) {
				
				$f_html = implode( $f_html );
			}
		}
				
		return $f_html;
	}
	
	
	private function getField_paths( $fields ) {
		
		$field_paths = array();
		
		foreach( $fields as $field ) {
		
			$key = $this->getField_id( $field );
			
			$definition_path = $this->setFieldPath( $field );
			
			$field_paths[ $key ][ 'def' ] = $definition_path;
			
			if ( is_object( $field ) ) {
				
				if ( property_exists( $field , 'regroup' ) ) {
					
					$destination_path = $this->setFieldPath( $field->regroup );
					
					if ( $destination_path ) {
						
						$field_paths[ $key ][ 'dest' ] = $destination_path;
					}
					
				} else {
					
					$field_paths[ $key ][ 'dest' ] = array();
				}
				
				$field_paths[ $key ]['params'] = $field;
				
			} else {
				
				$field_paths[ $key ][ 'dest' ] = array();
				
				$field_paths[ $key ]['params'] = new stdClass();
			}
			
			
		}
		
		return $field_paths;
	}
	
	
	private function getField_limits( $field_paths ) {
		
		$field_limits = array();
		
		foreach ( $field_paths as $path ) {
			
			$c = count( $path['def'] );
			
			if ( $c > 1 ) {
				
				$field_limits[] = implode( '.' , $path['def'] );
			}
		}
		
		return $field_limits;
	}
	
	
	/** get the name/id of the field to find it's path */
	private function getField_id ( $field ) {
		
		$f_id = '';
		
		if ( is_object( $field ) ) {
					
			if ( property_exists( $field , 'key' ) ) {
				
				$f_id = $field->key;
			}
			
		} elseif ( is_string( $field ) ) {
		
			if ( $field === '*' ) {
				
				$f_id = 'all';
				
			} else {
			
				$f_id = $field;
			}
			
		}
		
		return $f_id;		
	}
	
	
	private function getField_type( $field ) {
	
		$field_type = '';
		
		if ( property_exists( $field['params'] , 'type' ) ) {
			
			/** include error-checking to ensure type valid */
			$field_type = $field['params']->type;
			
		} else {
			
			if ( property_exists( $field['def'] , 'type' ) ) {
			
				$type = $field['def']->type;
				
				if ( $type == 'string' || $type == 'number' || $type == 'integer' ) {
					
					if ( property_exists( $field['def'] , 'enum' ) ) {
						
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
		
		$id = $field['name'];
		
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
	
	
	private function getField_tags( $field ) {
		
		
		
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
		
		$for_id = $field['name'];
		
		$tags = $this->getTags( $field['params'] , 'label' );
		
		
		if ( property_exists( $field['params'] , 'label' ) ) {
			
			if ( is_string( $field['params']->label ) ) {
				
				$label = $field['params']->label;
				
			} else {
				
				/** redefined label property is not a string **/
			}
			
		} elseif ( property_exists( $field['def'] , 'title' ) ) {
			
			$label = $field['def']->title;
			
		} else {
			
			$label = $field['name'];
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
	
	
	private function setFieldPath( $f_meta , $field_key = '' , $depth = 0 , $json = '{}'  ) {
		
		if ( is_object( $f_meta ) ) {
					
			if ( property_exists( $f_meta , 'key' ) ) {
				
				$f_path = explode( '.' , $f_meta->key );
			}
			
		} elseif ( is_string( $f_meta ) ) {
		
			if ( $f_meta === '*' ) {
				
				$f_path = array();
				
			} else {
			
				$f_path = explode( '.' , $f_meta );
			}
			
		} elseif ( is_array( $f_meta ) ) {
			
			$f_path = $f_meta;
			
			$d = 0;
			do {
			
				if ( $d ) {
					$d = $d + 1;
				}
				
				$f_path[] = $json->getSubIterator( $d )->key();
				$d = $d + 1;
				
			} while ( $d < $depth );
			
			if ( $f_path[ count( $f_path ) - 1 ] !== $field_key ) {
				
				$f_path[] = $field_key;
			}
		
		} else {
			
			return false;
		}
		
		/** add code to return a string form of the path */
		
		return $f_path;
	}
	
	
	private function getFieldData( $field_path , $json_s ) {
		
		if ( $this->hasSchema( $json_s ) ) {
		
			$f_data = $json_s->schema;
			
			// return this is path is equal to *
			
			foreach( $field_path as $pointer ) {
				
				if ( $this->hasProperties( $f_data ) ) {
					
					$f_data = $f_data->properties->$pointer;
				}
			}
			
		} else {
			
			echo '<p>Can\'t find schema object.</p>';
		}
		
		return $f_data;
	}
}

?>