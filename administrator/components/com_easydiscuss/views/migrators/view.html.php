<?php
/**
 * @package		EasyDiscuss
 * @copyright	Copyright (C) 2010 Stack Ideas Private Limited. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 *
 * EasyDiscuss is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
defined('_JEXEC') or die('Restricted access');

require_once DISCUSS_ADMIN_ROOT . '/views.php';

jimport( 'joomla.filesystem.file' );

class EasyDiscussViewMigrators extends EasyDiscussAdminView
{
	public function display( $tpl = null )
	{
		parent::display( $tpl );
	}

	public function communitypolls()
	{

		$this->set( 'installed' , $this->communityPollsExists() );

		parent::display( 'communitypolls' );
	}

	public function communityPollsExists()
	{
		return JFile::exists( JPATH_ROOT . '/administrator/components/com_communitypolls/communitypolls.xml' );
	}

	public function kunenaExists()
	{

		return JFile::exists( JPATH_ROOT . '/components/com_kunena/kunena.php' );
	}

	public function jomsocialExists()
	{
		jimport( 'joomla.filesystem.file' );
		return JFile::exists( JPATH_ROOT . '/components/com_community/community.php' );
	}

	public function registerToolbar()
	{
		JToolBarHelper::title( JText::_( 'COM_EASYDISCUSS_MIGRATORS' ), 'migrators' );
		JToolBarHelper::back( 'COM_EASYDISCUSS_BACK' , 'index.php?option=com_easydiscuss');
	}
}
