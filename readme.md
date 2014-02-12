# Work In Progress (WIP)

# Draft CakePHP Plugin
* Author: Nick Baker
* Version: 0.1
* License: MIT
* Website: <http://www.webtechnick.com>

## Features

CakePHP Plugin to Automatically save drafts of any model, allowing for data recovery of progress made persisting through authentication timeouts or power outages.

## Changelog
* 0.1 Start of project

## Install

Clone the repository into your `app/Plugin/Draft` directory:

	$ git clone git://github.com/webtechnick/CakePHP-Draft-Plugin.git app/Plugin/Draft

Run the schema into your database:

	$ cake schema create --plugin Draft
	
## Setup

Load the plugin in your bootstrap.php. Update your your `app/Config/bootstrap.php` file with:

	CakePlugin::load('Draft');