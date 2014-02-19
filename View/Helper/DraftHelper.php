<?php
/**
* Draft Helper
*
* requires jQuery
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
			$js = '$("#'. $options['id'] .'").click(function(){
				$.ajax({
					type:"POST",
					url: "'. $url .'",
					data: $("#'. $form_id .'").serialize(),
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
	
	public function discard($text = 'Discard Draft', $options = array()) {
		$options = array_merge(array(
			'id' => 'DiscardButton',
			'model' => '',
			'model_id' => ''
		), (array) $options);
		
		$model_id = $options['model_id'];
		$model = $options['model'];
		unset($options['model_id'], $options['model']);
		
		$path = $this->discardPath;
		$path[] = $model;
		$path[] = $model_id;
		return $this->Html->link($text, $path, $options);
	}
	
	public function autoDraft($interval = 60 /* seconds */, $options = array()) {
		$options = array_merge(array(
			'model' => '',
			'form_id' => null
		), (array) $options);
		
		$path = $this->draftPath;
		$path[] = $options['model'];
		$url = Router::url($path);
		$js = 'setInterval(function(){
			$.ajax({
				type:"POST",
				url: "'. $url .'",
				data: $("#'. $options['form_id'] .'").serialize()
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
