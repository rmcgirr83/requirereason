<?php
/**
*
* @package Require Reason
* @copyright (c) 2021 Richard McGirr
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace rmcgirr83\requirereason\event;

use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var language $language */
	protected $language;

	/* @var request $request */
	protected $request;

	/** @var template $template */
	protected $template;

	/** @var user $user */
	protected $user;

	/** @var string root_path */
	protected $root_path;

	/** @var string php_ext */
	protected $php_ext;

	/**
	* Constructor
	* NOTE: The parameters of this method must match in order and type with
	* the dependencies defined in the services.yml file for this service.
	*
	* @param language	$language		language object
	* @param request	$request		request object
	* @param template	$template		template object
	* @param user		$user			user object
	* @param string		$root_path		phpBB root path
	* @param string		$php_ext		phpEx
	*/
	public function __construct(
		language $language,
		request $request,
		template $template,
		user $user,
		string $root_path,
		string $php_ext)
	{
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup_after'						=> 'user_setup_after',
			'core.acp_extensions_run_action_after'		=> 'acp_extensions_run_action_after',
			'core.modify_posting_auth'					=> 'modify_posting_auth',
			'core.handle_post_delete_conditions'		=> 'handle_post_delete_conditions',
			'core.posting_modify_submission_errors'		=> 'posting_modify_submission_errors',
			'core.posting_modify_template_vars'			=> 'posting_modify_template_vars',
		);
	}

	/**
	 * Inject lang vars
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function user_setup_after($event)
	{
		$this->language->add_lang('requirereason', 'rmcgirr83/requirereason');
	}

	/* Display additional metAdate in extension details
	*
	* @param $event			event object
	* @param return null
	* @access public
	*/
	public function acp_extensions_run_action_after($event)
	{
		if ($event['ext_name'] == 'rmcgirr83/requirereason' && $event['action'] == 'details')
		{
			$this->template->assign_var('S_BUY_ME_A_BEER_REQUIREREASON', true);
		}
	}

	/**
	* Display error if deleting || soft_deleting a post
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function modify_posting_auth($event)
	{
		$mode = $event['mode'];

		if (in_array($mode, array('delete','soft_delete')))
		{
			$delete_reason = $this->request->variable('delete_reason', '', true);

			if ($this->request->is_set_post('confirm') && empty($delete_reason))
			{
				$post_id = $event['post_id'];
				$meta_info = append_sid("{$this->root_path}viewtopic.$this->php_ext", "p=$post_id") . "#p$post_id";
				meta_refresh(3, $meta_info);
				trigger_error('REASON_REQUIRED_DELETE');
			}
		}
	}

	/**
	* Display info if deleting || soft_deleting a post
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function handle_post_delete_conditions($event)
	{
		$delete_reason = $event['delete_reason'];
		if (empty($delete_reason))
		{
			$this->template->assign_var('S_REQUIREREASON', true);
		}
	}

	/**
	* Display error if editing a post
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function posting_modify_submission_errors($event)
	{
		$error = $event['error'];
		$mode = $event['mode'];
		$edit_reason = $event['post_data']['post_edit_reason'];
		$delete_checked = $this->request->is_set_post('delete') ? true : false;

		if ($mode == 'edit' && empty($edit_reason) && !$delete_checked)
		{
			$error[] = $this->language->lang('REASON_REQUIRED_EDIT');
		}

		$event['error'] = $error;
	}

	/**
	* Display message and delete post box checked?
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function posting_modify_template_vars($event)
	{
		$delete_checked = $this->request->is_set_post('delete') ? true : false;
		$error = $event['error'];
		$page_data = $event['page_data'];
		$edit_reason = !empty($page_data['EDIT_REASON']) ? true : false;
		$edit_reason_auth = $page_data['S_EDIT_REASON'];

		if ($event['mode'] == 'edit' && $edit_reason_auth && !count($error))
		{
			if (!$edit_reason)
			{
				$page_data['S_REQUIRE_REASON'] = true;
			}
		}
		if ($delete_checked)
		{
			$page_data['S_SOFTDELETE_CHECKED'] = ' checked="checked"';
		}

		$event['page_data'] = $page_data;
	}
}
