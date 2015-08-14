<?php

/**
 * Made with ❤ by themesfor.me
 *
 * XML Feed generation
 */

namespace Chucky\Controller;

use Chucky\Model;

class Feed
{
	/**
     * Setup hooks
     */
	public function run()
	{
		add_action('init', array($this, 'intercept_feed'));
	}

	/**
     * Intecept all url's with feed param
     */
	public function intercept_feed()
	{
		$feed_name = isset($_GET['feed']) ? $_GET['feed'] : null;

		switch($feed_name){
		    case 'google_feed' :
				$feed = new Model\Feed();

				header('Content-Type: application/xml');
				
				echo $feed->getXML();
		        exit;
		    break;
		}
	}
}
