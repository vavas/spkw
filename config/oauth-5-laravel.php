<?php

return [


	/*
	|--------------------------------------------------------------------------
	| oAuth Config
	|--------------------------------------------------------------------------
	*/

	/**
	 * Storage
	 */
	'storage' => 'Session',

	/**
	 * Consumers
	 */
	'consumers' => [

		'Google' => [
            'client_id'     => '312072798049-88r65ubdf0c69mbkpkcfkepe2ra47lu0.apps.googleusercontent.com',
			'client_secret' => 'WA9SCtvx1fL9EYfZNTTv3xX0',
			'scope'         => ['youtube'],
		],
        'Instagram' => [
            'client_id'     => 'd623c7561d494ecd87505320c0a37d40',
            'client_secret' => '07e87c412b2a402a86246735a651b452',
            'scope'         => ['basic', 'comments', 'relationships', 'likes'],
        ]

	]

];