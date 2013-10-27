<?php
	/**
	 * Module-Metadata for the pl_analytics.
	 * @author Paul Lamp <pl@paul-lamp.de>
	 * based on	 
	 * Module-Metadata for the marmPiwik.
	 * @author Joscha Krug <krug@marmalade.de>
	 */
	$sMetadataVersion = '0.1';

	$aModule = array(
		'id'		=> 'pl_analytics',
		'title'		=> 'paul-lamp.de Google Analytics eCommerce Tracking',
		'version'	=> '0.1',
		'author'	=> 'paul-lamp.de',
		'email'		=> 'pl@paul-lamp.de',
		
		'description'	=> array(
			'de'		=> 'Tracking mit Google Analytics',
		  'en'		=> 'Tracking with Google Analytics'
		),
		
		'extend' => array(
			'oxoutput'		=> 'pl/pl_analytics/core/pl_analytics_oxoutput'
		),
		
		'files' => array(
			'pl_analytics_setup'	=> 'pl/pl_analytics/controllers/admin/pl_analytics_setup.php',
			'pl_analytics'		=> 'pl/pl_analytics/core/pl_analytics.php'
		),
		
		'templates' => array(
			'pl_analytics_setup.tpl'	=> 'pl/pl_analytics/views/admin/tpl/pl_analytics_setup.tpl'
		)
				
	); //
