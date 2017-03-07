<?php
 /**
  * 
  * @version    1.0
  * @copyright  Copyright (C) 2006 - 2017 IndraSoft, Inc. All rights reserved.
  * @license    GNU General Public License version 3; see LICENSE.txt
  * 
  * This program is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 3 of the License, or
  * (at your option) any later version.
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  * See the
  * GNU General Public License for more details.
  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software Foundation,
  * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
  * 
  */
 
 defined('JPATH_BASE') or die;
 
  /**
   * organization field profile plugin.
   *
   * @package		Joomla.Plugins
   * @subpackage	user.profile
   * @version		1.0
   */
  class plgUserOrganization extends JPlugin
  
  {
        protected $autoloadLanguage = true;
        
        /**
        * prefix that is stored in profile key column
        */ 
        private $org_prefix = "organization.";
        
	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile','com_users.registration','com_users.user','com_admin.profile'))){
			return true;
		}
 
		$userId = isset($data->id) ? $data->id : 0;
 
		// Load the profile data from the database.
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT profile_key, profile_value FROM #__user_profiles' .
			' WHERE user_id = '.(int) $userId .
			' AND profile_key LIKE \'' . $this->org_prefix . '%\'' .
			' ORDER BY ordering'
		);
		$results = $db->loadRowList();
 
		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->_subject->setError($db->getErrorMsg());
			return false;
		}
 
		// Merge the profile data.
		$data->organization = array();
		foreach ($results as $v) {
			$k = str_replace($this->org_prefix, '', $v[0]);
			$data->organization[$k] = json_decode($v[1], true);
		}
 
		return true;
	}
 
	function onContentPrepareForm($form, $data)
	{
		// Load profile_organization plugin language
		$lang = JFactory::getLanguage();
		$lang->load('plg_user_organization', JPATH_ADMINISTRATOR);
 
		if (!($form instanceof JForm)) {
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		// Check we are manipulating a valid form.
		if (!in_array($form->getName(), array('com_users.profile', 'com_users.registration','com_users.user','com_admin.profile'))) {
			return true;
		}
		if ($form->getName()=='com_users.profile')
		{
			// Add the organization fields to the form.
			JForm::addFormPath(dirname(__FILE__).'/profiles');
			$form->loadFile('organization', false);
 
			// Toggle whether the gov_affil field is required.
			if ($this->params->get('register-require_gov_affil', 1) > 0) {
				$form->setFieldAttribute('gov_affil', 'required', $this->params->get('profile-require_gov_affil') == 2, 'organization');
			} else {
				$form->removeField('gov_affil', 'organization');
			}
		}
 
		//we treat the frontend registration and the back end user create or edit as the same.
		elseif ($form->getName()=='com_users.registration' || $form->getName()=='com_users.user' )
		{		
			// Add the registration fields to the form.
			JForm::addFormPath(dirname(__FILE__).'/profiles');
			$form->loadFile('organization', false);
 
			// Toggle whether the gov_affil field is required.
			if ($this->params->get('register-require_gov_affil', 1) > 0) {
				$form->setFieldAttribute('gov_affil', 'required', $this->params->get('register-require_gov_affil') == 2, 'organization');
			} else {
				$form->removeField('gov_affil', 'organization');
			}
            
			// Toggle whether the org field is required.
			if ($this->params->get('register-require_org', 1) > 0) {
				$form->setFieldAttribute('org', 'required', $this->params->get('register-require_org') == 2, 'organization');
			} else {
				$form->removeField('org', 'organization');
			}

		}			
	}
 
	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId	= JArrayHelper::getValue($data, 'id', 0, 'int');
 
		if ($userId && $result && isset($data['organization']) && (count($data['organization'])))
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery('DELETE FROM #__user_profiles WHERE user_id = '.$userId.' AND profile_key LIKE \''.$this->org_prefix.'%\'');
				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
 
				$tuples = array();
				$order	= 1;
				foreach ($data['organization'] as $k => $v) {
					$tuples[] = '('.$userId.', '.$db->quote($this->org_prefix.$k).', '.$db->quote(json_encode($v)).', '.$order++.')';
				}
 
				$db->setQuery('INSERT INTO #__user_profiles VALUES '.implode(', ', $tuples));
				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e) {
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}
 
		return true;
	}
 
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success) {
			return false;
		}
 
		$userId	= JArrayHelper::getValue($user, 'id', 0, 'int');
 
		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
					' AND profile_key LIKE \''.$this->org_prefix.'%\''
				);
 
				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}
 
		return true;
	}
 
 }