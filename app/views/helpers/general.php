<?php
/**
 * Wizard helper by jaredhoyt.
 *
 * Creates links, outputs step numbers for views, and creates dynamic progress menu as the wizard is completed.
 *
 * PHP versions 4 and 5
 *
 * Comments and bug reports welcome at jaredhoyt AT gmail DOT com
 *
 * Licensed under The MIT License
 *
 * @writtenby		jaredhoyt
 * @lastmodified	Date: March 11, 2009
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */ 
class GeneralHelper extends AppHelper {
	var $helpers = array('Session','Html','Form');
	var $output = null;

	function input($model = null, $options = array()){
		// Customized input field to fit with Twitter Bootstrap

		$defaults = array(
						  'help' => '');
						 
		$options = array_merge($defaults,$options); // erase $defaults with $options


		$extraClasses = '';
		if($this->Form->error($model) != null){
			$extraClasses .= ' error';
		}

		// If it is a checkbox/radio/text,etc., say so in the class
		if(isset($options['type'])){
			$extraClasses .= ' '.$options['type'];
		}

		echo '<div class="clearfix'.$extraClasses.'">';
			
			// Display checkboxes a little differently 
			// - label and input switch places
			// - checkboxes require 'type' and label to be set

			$use_checkbox = false;
			if(isset($options['type']) && $options['type'] == 'checkbox'){
				$use_checkbox = true;
				if(isset($options['label'])){
					$label_checkbox = $options['label'];
					unset($options['label']);
				}
			}
			if(!$use_checkbox){
				if(isset($options['label'])){
					echo $this->Form->label($model,$options['label']);
					unset($options['label']);
				} else {
					echo $this->Form->label($model);
				}
			}


			// Merge in additional, like 'type' and 'options'

			echo '<div class="input">';
				if(isset($options['type']) && $options['type'] == 'radio'){
					// Radio button
					echo '<ul class="inputs-list">';
					foreach($options['options'] as $key => $value){
						echo '<li><label>';
						echo $this->Form->input($model,array('type' => 'radio', 'label' => false, 'div' => false, 'hiddenField' => false, 'legend' => false, 'options' => array($key => $value)));
						echo '</li></label>';
					}
					echo '</ul>';
				} else {
					// Everything besides Radio buttons
					echo $this->Form->input($model, array_merge(array('label' => false, 'div' => false),$options));
				}
				if($options['help']){
					echo '<span class="help-block">'.$options['help'].'</span>';
				}
			echo '</div>';

			// Checkbox label
			if($use_checkbox){
				if(isset($label_checkbox)){
					echo $this->Form->label($model,$label_checkbox);
				} else {
					echo $this->Form->label($model);
				}
			}


		echo '</div>';

		//$view =& ClassRegistry::getObject('view');
		//pr($view->viewVars);
		//exit;

	}


	function prettyPhone($phone = ''){
		if(strlen($phone) == 10){
			$phone = "(".substr($phone,0,3).") ".substr($phone,3,3)."-".substr($phone,6,4);
			return $phone;
		} elseif(strlen($phone) == 11) {
			$phone = "1 (".substr($phone,1,3).") ".substr($phone,4,3)."-".substr($phone,7,4);
			return $phone;
		} else {
			return $phone;
		}
	}

}
?>