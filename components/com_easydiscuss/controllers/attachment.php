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

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
require_once JPATH_ROOT . '/components/com_easydiscuss/constants.php';
require_once JPATH_ROOT . '/components/com_easydiscuss/helpers/helper.php';

class EasyDiscussControllerAttachment extends EasyDiscussController
{
	function displayFile()
	{
		$id		= JRequest::getVar('id', '', 'GET');
		$config	= DiscussHelper::getConfig();

		if(empty($id))
		{
			return false;
		}

		$attachment	= DiscussHelper::getTable( 'Attachments' );
		if(!$attachment->load( $id ))
		{
			return false;
		}

		$path = $config->get( 'attachment_path' );
		$file = DISCUSS_MEDIA . '/' . $path . '/' . $attachment->path;

		switch (JRequest::getCmd('size')) {
			case 'thumb':
				$file .= '_thumb';
				break;

			default:
				break;
		}

		if (!JFile::exists($file))
		{
			return false;
		}

		header('Content-Description: File Transfer');
		header('Content-Type: ' . $attachment->mime);
		header('Content-Disposition: inline');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file) );
		ob_clean();
		flush();
		readfile($file);
		exit;
	}

	function getFile()
	{
		$id		= JRequest::getVar('id', '', 'GET');
		$config	= DiscussHelper::getConfig();

		if(empty($id))
		{
			return false;
		}

		$attachment	= DiscussHelper::getTable( 'Attachments' );
		if(!$attachment->load( $id ))
		{
			return false;
		}


		$type = explode("/", $attachment->mime);

		header('Content-Type: ' . $attachment->mime);

		$attachment->download();
	}

	function deleteFile($id)
	{
		$config	= DiscussHelper::getConfig();

		if(empty($id))
		{
			return false;
		}

		$attachment	= DiscussHelper::getTable( 'Attachments' );
		if(!$attachment->load( $id ))
		{
			return false;
		}

		return $attachment->delete();
	}
}
