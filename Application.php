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

/**
 * AWS Simple Email Service Application Class
 */
class _Application extends \IPS\Application
{
	/**
	 * _Application constructor.
	 */
	public function __construct()
	{
		// Load vendor library if it hasn't already been
        if (!class_exists('Aws\\Ses\\SesClient') || !class_exists('Aws\\Sns\\SnsClient')) {
            require_once static::getRootPath() . '/applications/awsses/sources/vendor/autoload.php';
        }
    }

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