<?php
/**
 * @brief		AWS Simple Email Service Application Class
 * @author		<a href='https://www.deschutesdesigngroup.com'>Deschutes Design Group LLC</a>
 * @copyright	(c) 2020 Deschutes Design Group LLC
 * @package		Invision Community
 * @subpackage	AWS Simple Email Service
 * @since		15 Dec 2020
 * @version		
 */
 
namespace IPS\awsses;

require_once \IPS\ROOT_PATH . '/applications/awsses/interface/vendor/autoload.php';

/**
 * AWS Simple Email Service Application Class
 */
class _Application extends \IPS\Application
{
	/**
	 * [Node] Get Icon for tree
	 *
	 * @note    Return the class for the icon (e.g. 'globe')
	 * @return    string|null
	 */
	protected function get__icon()
	{
		// Return the application icon
		return 'amazon';
	}
}