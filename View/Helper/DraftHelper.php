<?php
/**
* Draft Helper
* requires jQuery
*
Load the helper
@example
public $helpers = array('Draft.Draft'); //Default settings. 
public $helpers = array('Draft.Draft' => array(
	'buffer' => true,
	'draftPath' => array('admin' => false, 'plugin' => 'draft', 'controller' => 'drafts', 'action' => 'save'),
	'discardPath' => array('admin' => false, 'plugin' => 'draft', 'controller' => 'drafts', 'action' => 'delete') 
));

Draft Link
@example
echo $this->Draft->button('Save Draft', array(
	'model' => 'ModelName',
	'form_id' => 'ModelForm'
));
echo $this->Draft->button('Save Draft', array(
	'model' => 'ModelName',
	'form_id' => 'ModelForm',
	'script' => false // don't autoload javascript to execute saves.
));
*
* @author Nick Baker
* @since 1.0
*/
App::uses('AppHelper','View/Helper');
class DraftHelper extends AppHelper {
	
	/**
	* Helpers
	* @var array
	* @access public
	*/
	public $helpers = array('Html', 'Js');
	public $draftPath = array('admin' => false, 'plugin' => 'draft', 'controller' => 'drafts', 'action' => 'save');
	public $discardPath = array('admin' => false, 'plugin' => 'draft', 'controller' => 'drafts', 'action' => 'delete');
	public $buffer = true;
	
	/**
	* Save Draft Button
	* @param string text for link
	* @param array of options
	* - id: String id of button, required (default DraftButton)
	* - model: String of model name to save against (default null)
	* - script: Boolean build the jquery javascript (default true)
	* - form_id: String of form data to serialize and submit for drafts (default null)
	* @return string button link.
	*/
	public function button($text = 'Save Draft', $options = array()) {
		$options = array_merge(array(
			'id' => 'DraftButton',
			'model' => '',
			'script' => true,
			'form_id' => null
		), (array) $options);
		
		$form_id = $options['form_id'];
		$model = $options['model'];
		$script = $options['script'];
		unset($options['form_id'], $options['script'], $options['model']);
		
		$path = $this->draftPath;
		$path[] = $model;
		$retval = $this->Html->link($text, $path, $options);
		if ($script) {
			$url = Router::url($path);
			$js = 'jQuery.("#'. $options['id'] .'").click(function(){
				jQuery.ajax({
					type:"POST",
					url: "'. $url .'",
					data: jQuery.("#'. $form_id .'").serialize(),
					success: function(){
						alert("Draft Saved.");
					}
				});
				return false;
			});';
			if ($this->buffer) {
				$this->Js->buffer($js);
			} else {
				$retval .= $this->Html->scriptBlock($js);
			}
		}
		return $retval;
	}
	
	/**
	* Discard the draft button
	* @param string text (default Discard Draft)
	* @param array of options
	* - id: String id of button, required (default DiscardButton)
	* - model: String of model name to save against (default null)
	* - model_id: String of model_id to delete against, (default null)
	* @return string link to discard
	*/
	public function discard($text = 'Discard Draft', $options = array()) {
		$options = array_merge(array(
			'id' => 'DiscardButton',
			'model' => '',
			'model_id' => null
		), (array) $options);
		
		$model_id = $options['model_id'];
		$model = $options['model'];
		unset($options['model_id'], $options['model']);
		
		$path = $this->discardPath;
		$path[] = $model;
		$path[] = $model_id;
		return $this->Html->link($text, $path, $options);
	}
	
	/**
	* AutoDraft intervals
	* @param int seconds (default 60)
	* @param array of options
	* - model: String of model name to save against (default null)
	* - form_id: String of form data to serialize and submit for drafts (default null)
	* @return scriptblock or buffer interval to auto draft
	*/
	public function autoDraft($interval = 60 /* seconds */, $options = array()) {
		$options = array_merge(array(
			'model' => '',
			'form_id' => null
		), (array) $options);
		
		$path = $this->draftPath;
		$path[] = $options['model'];
		$url = Router::url($path);
		$js = 'setInterval(function(){
			jQuery.ajax({
				type:"POST",
				url: "'. $url .'",
				data: jQuery.("#'. $options['form_id'] .'").serialize()
			});
			return false;
		}, '. $interval * 1000 .');';
		if ($this->buffer) {
			return $this->Js->buffer($js);
		} else {
			return $this->Html->scriptBlock($js);
		}
	}

}
