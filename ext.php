<?php
/**
*
* Require Reason [English]
*
* @package language
* @copyright (c) 2021 Richard McGirr
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace rmcgirr83\requirereason;

/**
* Extension class for custom enable/disable/purge actions
*/
class ext extends \phpbb\extension\base
{
	/**
	 * Enable extension if phpBB version requirement is met
	 *
	 * @return bool
	 * @access public
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');
		$allowed = (phpbb_version_compare($config['version'], '3.3', '>=') && phpbb_version_compare($config['version'], '4.0', '<')) ? true : false;
		return $allowed;
	}
}
