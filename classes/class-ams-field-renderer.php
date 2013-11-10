<?php
require( AMS_CORE_PLUGIN_BASEDIR . 'classes/common/data-functions.php' );

class AMSFieldRenderer {

	private $field_prefix;

	public function __construct ( $prefix = '' ) {
	
		$this->field_prefix = $prefix;
				
	}

	public function render ( $field , $display = 0 ) {
	
		if ( !isset($field['display'] ) ) {
			$field['display'] = true;
		}
		
		$output = '';
		
		if ( $field['display'] ) {
			
			switch ( $field['form_type'] ) {
				
				case 'text':
					if ( method_exists( $this , 'render_'.$field['form_type'] ) ) {
						$output = call_user_func_array( array( $this , 'render_'.$field['form_type'] ) , array( $field ) );
					} else {
						$output = 'The '.$field['form_type'].' method hasn\'t been defined yet, I can\'t do anything<br />';
					}
					
					break;
					
				case 'textarea':
					if ( method_exists( $this , 'render_'.$field['form_type'] ) ) {
						$output = call_user_func_array( array( $this , 'render_'.$field['form_type'] ) , array( $field ) );
					} else {
						$output = 'The '.$field['form_type'].' method hasn\'t been defined yet, I can\'t do anything<br />';
					}
					
					break;
					
				case 'select':
					if ( method_exists( $this , 'render_'.$field['form_type'] ) ) {
						$output = call_user_func_array( array( $this , 'render_'.$field['form_type'] ) , array( $field ) );
					} else {
						$output = 'The '.$field['form_type'].' method hasn\'t been defined yet, I can\'t do anything<br />';
					}
					
					break;
					
				case 'radio':
					if ( method_exists( $this , 'render_'.$field['form_type'] ) ) {
						$output = 'Render radio field<br />';
					} else {
						$output = 'The '.$field['form_type'].' method hasn\'t been defined yet, I can\'t do anything<br />';
					}
					
					break;
				
				case 'checkbox':
					if ( method_exists( $this , 'render_'.$field['form_type'] ) ) {
						$output = call_user_func_array( array( $this , 'render_'.$field['form_type'] ) , array( $field ) );
					} else {
						$output = 'The '.$field['form_type'].' method hasn\'t been defined yet, I can\'t do anything<br />';
					}
					
					break;
				
			}
			
			/* maybe should check to see if $output has any data first */
			
			if ( 0 === $display ) {
				return $output;
			} elseif ( 1 === $display ) {
				echo $output;
			}
			
		} else {
			
			return $output;
			
		}
		
	}
	
	public function group_render ( $fields , $group , $display = 0 ) {
	
		$output = '';
		
		if ( $group['label'] ) {
			$label = '<legend>'.$group['name'].'</legend>';
		} else {
			$label = '';
		}
		
		$group_fields = $this->get_group( $fields , $group['slug'] , $group['sort'] );
		
		foreach ( $group_fields as $field ) {
			
			$output .= $this->render( $field );
			
		}
		
		if ( $group['fieldset'] ) {
			$output = '<fieldset id="'.$group['prefix'].$group['slug'].'-fields">'.$label.$output.'</fieldset>';
		}
		
		if ( 0 === $display ) {
			return $output;
		} elseif ( 1 === $display ) {
			echo $output;
		}
		
	}
	
	private function get_group ( $fields , $group , $sort = 1 ) {
		
		$grouped_fields = array();
		
		foreach ( $fields as $field ) {
			
			if ( $field['group'] === $group ) {
				
				$grouped_fields[] = $field;
				
			}
			
		}
		
		if ( $sort ) {
		
			usort( $grouped_fields , array( $this , 'sort_group_fields' ) );
			
		}
		
		return $grouped_fields;
		
	}
	
	private function sort_group_fields ( $a , $b ) {
		
		if ( $a['order'] == $b['order'] ) {
			return 0;
		}
		
		return ( $a['order'] < $b['order'] ) ? -1 : 1;
		
	}
	
	private function render_text ( $field ) {
		
		extract($field);
		$prefix = $this->field_prefix;
		$html = '';
				
		if ( $label ) {
			$html .= '<label for="'.$id.'">'.$name.'</label>';
		}
		
		$html .= '<input type="text" id="'.$id.'" name="'.$prefix.'['.$id.']" placeholder="'.$placeholder.'" value="'.$value.'" />';
		
		
		
		if ( $field['fieldset'] ) {
			return '<fieldset>'.$html.'</fieldset>';
		} else {
			return $html;
		}
				
	}
	
	private function render_select ( $field ) {
		
		extract( $field );
		$prefix = $this->field_prefix;
		$html = '';
		
		
		if ( $label ) {
			$html .= '<label for="'.$id.'">'.$name.'</label>';
		}
		
		
		$options = $this->render_options( $field );
		
		
		/*vvvv only renders the select field if options comes back with values vvvv*/
		
		if ( $options ) {
		
			//check to make sure options is an array and isn't empty	
			$html .= '<select id="'.$id.'" name="'.$prefix.'['.$id.']">'.implode( $options ).'</select>';
			
		} else {
			
			$html .= '<p>There were no options available</p>';
			
		}
		
		
		if ( $field['fieldset'] ) {
			return '<fieldset>'.$html.'</fieldset>';
		} else {
			return $html;
		}
		
	}
	
	private function render_options ( $data ) {
	
		extract( $data );
		
		$options = array();
		
		$options[] = '<option value="" '.selected( '' , $value , false ).' ></option>';
	
		if ( isset( $data_source ) && !empty( $data_source ) ) {
			
			switch ( $data_source ) {
				
				case 'list':
					
					if ( isset( $data_list ) && is_array( $data_list ) && !empty( $data_list ) ) {
					
						/**
						 * TODO: data list sorting function
						 */
						 
						foreach ( $data_list as $o ) {
							$options[] = '<option value="'.$o['value'].'" '.selected( $o['value'] , $value , false ).' >'.$o['name'].'</option>';
						}
						
					} else {
						
						/**
						 * variable was either not set, was not an array or was empty (or some combination)
						 * TODO: exception handling
						 */
						 
						return false;
						
					}
					
					break;
					
				case 'core':
					
					if ( isset( $data_list ) && !empty( $data_list ) && function_exists( $data_list ) ) {
						
						$data = call_user_func_array( $data_list , array( $data_params ) );
						
						// sorting would occur here if flag is set
						
						if ( is_array( $data ) ) {
						
							if ( !$data_map ) {
								
								foreach ( $data as $o ) {
									
									$options[] = '<option value="'.$o['value'].'" '.selected( $o['value'] , $value , false ).' >'.$o['name'].'</option>';
									
								}
								
							} else {
								
								foreach ( $data as $o ) {
									
									if ( array_key_exists( $data_map['name'] , $o ) && array_key_exists( $data_map['value'] , $o ) ) {
										
										$options[] = '<option value="'.$o[ $data_map['value'] ].'" '.selected( $o[ $data_map['value'] ] , $value , false ).' >'.$o[ $data_map['name'] ].'</option>';
										
									} else {
										
										return false;
										
									}
									
								}
								
							}
						
						} else {
							
							return false;
							
						}
						
					} else {
						
						return false;
						
					}
					
					break;
					
				case 'wp':
					break;
				
			}
			
		} else {
		
			/**
			 * need to place some error trapping code here
			 * no data source was defined
			 */
			
			return false;
			
		}
		
		return $options;
		
	}
	
	private function render_textarea ( $field ) {
	
		extract($field);
		$prefix = $this->field_prefix;
		$html = '';
		
		if ( $label ) {
			$html .= '<label for="'.$id.'">'.$name.'</label>';
		}
		
		$html .= '<textarea id="'.$id.'" name="'.$prefix.'['.$id.']" placeholder="'.$placeholder.'">'.$value.'</textarea>';
		
		return '<fieldset>'.$html.'</fieldset>';
		
	}
	
	private function render_checkbox ( $field ) {
		
		extract($field);
		
		$checkboxes = array();
		
		$prefix = $this->field_prefix;
		$value = maybe_unserialize( $value );
		//var_dump($value);
		$html = '';
		
		if ( $label ) {
			$html .= '<label for="'.$id.'">'.$name.'</label>';
		}
		
		/*
		 * determines if the checkboxes are set to display and save as one group or as individual checkboxes
		 * will serialize the array when saved
		 */
		$key_index = $collection ? '[]' : '';
		
		/*
		 * this line is a necessary evil
		 * in html protocol if the members of a field array have no value then they won't be submitted in the $_POST variable
		 * this translates that checkboxes that where previously on (checked) and later turned off (unchecked)
		 * 		would not submit a value to the server and so their value would never change
		 * this is partially corrected making the entire collection of checkboxes an array (using [])
		 * 		it would then save a single serialized value of all checkboxes that where checked
		 * 		if I checkbox had been unckecked it would simply no longer be in the serialized value and effectively be unset
		 * this line is actually a fail safe in the event that all checkboxes where unchecked
		 *		if this happened then nothing would be submitted to the server and nothing would be updated
		 *		this would submit an array with a single element and translate to nothing being checked
		 */
		$cb_submission = '<input type="hidden" name="'.$prefix.'['.$id.']'.$key_index.'" value="0">';
		
		if ( isset( $data_source ) && !empty( $data_source ) ) {
		
			switch ( $data_source ) {
				
				case 'list':
				
					break;
				
				case 'core':
				
					break;
					
				case 'wp':
				
					if ( isset( $data_list ) && !empty( $data_list ) && function_exists( $data_list ) ) {
						
						$data = call_user_func_array( $data_list , array( $data_params ) );
						//var_dump($data);
						
						foreach ( $data as $cb ) {
							
							/*vvvv method for determining which of the checkboxes should be checked vvvv*/
							
							if ( is_array( $value ) ) {    // $value should always be an array because it came from an unserialized string of checkbox values
								/*vvvv if the Post ID of the Post we're currently on is in the array of values for this checkbox collection then it should be checked vvvv*/
								$checked = in_array( $cb->$data_map['value'] , $value ) ? 'checked="checked" ' : '';
							} else {    // something went wrong in the save
								die('Not an array');
							}
							
							$checkboxes[] = '<li><label><input type="checkbox" name="'.$prefix.'['.$id.']'.$key_index.'" value="'.$cb->$data_map['value'].'" '.$checked.'/> '.$cb->$data_map['name'].'</label></li>';
							
						}
						
						if ( $field['fieldset'] ) {
							return $html .= '<fieldset>'.$cb_submission.'<ul>'.implode( $checkboxes ).'</ul></fieldset>';
						} else {
							return $html .= $cb_submission.'<ul>'.implode( $checkboxes ).'</ul>';
						}
						
					} else {
						
						die('Could not use data list');
						
					}
				
					break;
				
			}
			
		} else {
			
			die('No data source set');
			
		}
		
	}

}

?>