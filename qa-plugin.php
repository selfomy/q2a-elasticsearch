<?php

/*
	Plugin Name: ElasticSearch Module for Q2A
	Plugin URI: https://github.com/selfomy/q2a-elasticsearch
	Plugin Update Check URI: https://raw.github.com/vijsha79/q2a-elasticsearch/master/qa-plugin.php
	Plugin Description: Use ElasticSearch features to improve Q2A search feature
	Plugin Version: 1.0
	Plugin Date: 2020-02-12
	Plugin Author: Bao Bui (forked from Vijay Sharma)
	Plugin Author URI: https://selfomy.com/
	Plugin License: MIT
	Plugin Minimum Question2Answer Version: 1.8
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}


	qa_register_plugin_module('search', 'qa-es-admin.php', 'qa_elasticsearch', 'qa_elasticsearch');
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
