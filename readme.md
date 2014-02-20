# Draft CakePHP Plugin
* Author: Nick Baker
* Version: 1.0
* License: MIT
* Website: <http://www.webtechnick.com>

## Features

CakePHP Plugin to Automatically save drafts of any model, allowing for data recovery of progress made persisting through authentication timeouts or power outages.

## Requirements

CakePHP 2.x
jQuery (if you want to use the auto draft and save draft buttons in the helper)

## Changelog
* 1.0 Initial release
* 0.1 Start of project

## Install

Clone the repository into your `app/Plugin/Draft` directory:

	$ git clone git://github.com/webtechnick/CakePHP-Draft-Plugin.git app/Plugin/Draft

Run the schema into your database:

	$ cake schema create --plugin Draft
	
## Setup

Load the plugin in your bootstrap.php. Update your your `app/Config/bootstrap.php` file with:

	CakePlugin::load('Draft');

## Description

Drafts are a way to have a "future" state of your current production record that mutliple people can see and edit without having to save to the actual table.  The idea is your users should never lose work they've made in a form and should be able to come back to work from whereever they leave off last.  Drafts also have the added benefit of persisting through authentication timeouts as well as any other form completion interuption.

You can think of Drafts as a Google Doc, where there is only one "Draft" state of any record at a time, users are tracked as who made the most recent change to the draft. Anyone with access to edit the record has access to overwrite/update the draft state.

You can also create Drafts of records that don't exist yet (new records).  So you can create a draft state of new content, and it's associated with your user (AuthComponent::user('id') or $Model->getUserId() definition).  You can find and continue editing any new content from draft state, clicking save will delete the draft after a successful save has been made on the Model.

There are a number of features the plugin offers as well as helpers for auto saving as draft (require's jQuery).

## Usage

Attach the Draftable Behavior to any Model you wish to allow drafts for.

	public $actsAs = array('Draft.Draftable');

### Save A Draft

There are three ways in the behavior to save a draft version of your current record. All of which will NOT update the actual record, but instead create a Draft of the record for later recovery/editing.

	$data = array(
		'Model' => array(
			'field1' => 'value',
		)
	);
	//saves the data as a draft
	$this->Model->save($data, array('draft' => true));
	
	$data = array(
		'Model' => array(
			'draft' => true, //magic key!
			'field1' => 'value',
		)
	);
	//will save as draft because 'draft' is set to true in $data['Model']
	$this->Model->save($data);
	
	$this->Model->data = array(
		'Model' => array(
			'field1' => 'value',
		)
	);
	//will save any data in $Model->data as draft.
	$this->Model->saveDraft();

### Find A Draft

Recover a Draft record and merge it with your actual record to continue editing (keeping the original intact)

Find the data first, using the model find, but also find the draft of this record. Then merge the data together, where the 'Model' key will be the Drafted version. A new key of `ModelOriginal` will contain the original record. 

	$data = $this->Model->find('first', array(
		'draft' => true
	));
	$this->request->data = $this->Model->mergeDraft($data);

Another option is using contain, the rest is the same.

	$this->Model->bindDraft();
	$data = $this->Model->find('first', array(
		'contain' => array('Draft')
	));
	$this->request->data = $this->Model->mergeDraft($data);

You can also use the custom findDraft method which does the merge for your automatically

	$this->request->data = $this->Model->findDraft(array(
		'conditions' => array(
			'Model.id' => 1,
			//your other conditions
		),
		'contain' => array(
			//your contains
		)
	));

The data return (and Merged) will include any contains you included along with the Draft.  If there is an active Draft, the Draft changes are merged into the Model, and a new Key of `ModelOringial` is created for your reference and ease of switching between draft and original record. (draft being the default editable in your forms).

Model with an active draft.

	$data = array(
		'Model' => array(
			'field' => 'value',
			'field2' => 'value',
			'is_draft' => true,
		),
		'Draft' => array(
			'id' => 'long-string-id',
			'user_id' => 1,
			//...
		),
		'ModelOriginal' => array(
			'field' => 'original_value',
			'field2 => 'original_value',
		)
	);

Model without an active draft.

	$data = array(
		'Model' => array(
			'field' => 'value',
			'field2' => 'value',
		),
		'Draft' => array(
			'id' => null,
			'user_id' => null,
			//...
		),
	);

### Find Draft by User

You can also save drafts of records that aren't actually create yet.  Drafts are automatically tracked to a user, to use this feature you'll need to either be using `AuthComponent::user('id')` or you'll need to define `$Model->getUserId()`

NOTE: You can define `getUserId()` in the AppModel

	public function getUserId() {
		//your custom method to get the user_id string or int.
	}

Otherwise `AuthComponent::user('id');` is used instead.

Once you can track users, you can then retrieve drafts that don't have a record associated with them (new unsaved records)

	$this->request->data = $this->Model->findDraftByUser(); //Find draft by logged in user
	$this->request->data = $this->Model->findDraftByUser(1); //Find draft by logged in user_id of 1

### Delete A Draft

	$this->Model->deleteDraft(); //will delete any draft data saved to $Model->id;
	$this->Model->deleteDraft(1); //will delete any draft data saved to this model with the model's id of 1;

## Example Setups

#### Controller Changes

Update your Controller to find and merge drafts for your request data.

	$this->request->data = $this->Model->findDraft(array(
		'conditions' => array('Model.id' => $id),
	));

Full example of edit function.

	//Example Edit finding and restoring draft data
	function edit($id = null) {
		if (!empty($this->request->data)) {
			if ($this->Model->saveAll($this->request->data)) {
				$this->Session->setFlash('Success!');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Failures');
			}
		}
		//Altered!
		if ($id && empty($this->request->data)) {
			$this->request->data = $this->Model->findDraft(array(
				'conditions' => array('Model.id' => $id),
				'contain' => array('Association')
			));
		}
	}

You'll want to alter your add function as well if you intend on allowing drafts of unsaved content (new records)

	if ($data = $this->Model->findDraftByUser()) {
		$this->request->data = $data;
	}

Full example of add function.

	//Example Add Pulling up Draft data from unsaved records.
	function add() {
		if (!empty($this->request->data)) {
			if ($this->Model->save($this->request->data)) {
				$this->Session->setFlash(__('The Model has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The Model could not be saved. Please, try again.'));
			}
		}
		//Altered!
		if ($data = $this->Model->findDraftByUser()) {
			$this->request->data = $data;
		}
	}

#### View Changes

##### Save Draft Button

You'll want to add the `Save Draft` Button.  This feature requires the name of the Model you have Draftable on, as well as the HTML ID of the form you wish to Draft

If you have a form like this:

	<?php echo $this->Form->create('Model', array('id' => 'ModelForm')); ?>

Your Draft Button would be this:

	<?php echo $this->Draft->button('Save Draft', array('model' => 'Model', 'form_id' => 'ModelForm')); ?>

This feature uses jQuery to attach and execute the draft save to the custom drafts controller via ajax (recommended setup).  If you don't want the auto script and you want to handle the draft save yourself you can turn off the auto scripting by adding `'script' => false` in the options.

	<?php echo $this->Draft->button('Save Draft', array('model' => 'Model', 'script' => false)); ?>
	
Any additional options for the link, put into the options array.

	<?php echo $this->Draft->button('Save Draft', array('class' => 'btn btn-large', 'escape' => false, 'model' => 'Model')); ?>

##### Discard Button

You'll also want the `Discard Draft' Button. When you're editing a draft, you'll also want the discard draft button to allow the user to blow away the draft and start fresh.

	<?php echo $this->Draft->discard('Discard Draft', array('model' => 'Content', 'model_id' => $this->Form->value('Model.id'))); ?>
	
Again you can pass in any link option into the options and it will keep.

	<?php echo $this->Draft->discard('Discard Draft', array('class' => 'btn btn-large btn-danger', 'model' => 'Content', 'model_id' => $this->Form->value('Model.id'))); ?>

ProTip: Only show the discard button if you're actively editing a Draft.  Do so by checking for the `is_draft` field in the data.

	<?php if (isset($this->request->data['Model']['is_draft'])): ?>
		<?php echo $this->Draft->discard('Discard Draft', array('model' => 'Model', 'model_id' => $this->Form->value('Model.id'))); ?>
	<?php endif; ?>

##### Auto Draft Feature

You can set an automatic timer to save drafts at certain intervals (every 60 seconds is default).  Again, like Save Draft feature -- this feature requires jQuery to work.

	<?php echo $this->Draft->autoDraft(60, array('model' => 'Model', 'form_id' => 'ModelForm')); ?>

## Basic Admin

You can view and search all open drafts using the built in admin system.

Navigate to `http://example.com/admin/draft/drafts` to see all your saved drafts.

#### ENJOY! Comments are appreciated