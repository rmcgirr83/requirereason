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

	/* @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/**
	* Constructor
	* NOTE: The parameters of this method must match in order and type with
	* the dependencies defined in the services.yml file for this service.
	*
	* @param language	$language	Language object
	*/
	public function __construct(
		language $language,
		request $request,
		template $template,
		user $user)
	{
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
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
			'core.acp_extensions_run_action_after'		=>	'acp_extensions_run_action_after',
			'core.modify_posting_auth'					=> 'modify_posting_auth',
			'core.handle_post_delete_conditions'		=> 'handle_post_delete_conditions',
			'core.posting_modify_submission_errors'		=> 'posting_modify_submission_errors',
		);
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
			$this->language->add_lang('requirereason', $event['ext_name']);
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
			$this->user->add_lang_ext('rmcgirr83/requirereason', 'requirereason');
			$delete_reason = $this->request->variable('delete_reason', '', true);

			if ($this->request->is_set_post('confirm') && empty($delete_reason))
			{
				trigger_error('REASON_REQUIRED');
			}
		}
	}

	/**
	* Display error if deleting || soft_deleting a post
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function handle_post_delete_conditions($event)
	{
		$this->user->add_lang_ext('rmcgirr83/requirereason', 'requirereason');
		$this->template->assign_var('S_REQUIREREASON', true);
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
		$this->language->add_lang('requirereason', 'rmcgirr83/requirereason');

		if ($mode == 'edit' && empty($edit_reason))
		{
			$error[] = $this->language->lang('REASON_REQUIRED');
		}

		$event['error'] = $error;
	}
}
