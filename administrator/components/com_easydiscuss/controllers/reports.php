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

require_once DISCUSS_HELPERS . '/date.php';
require_once DISCUSS_HELPERS . '/input.php';

class EasyDiscussControllerReports extends EasyDiscussController
{
	function publish()
	{
		$post	= JRequest::getVar( 'cid' , array(0) , 'POST' );

		$message	= '';
		$type		= 'success';

		if( count( $post ) <= 0 )
		{
			$message	= JText::_('COM_EASYDISCUSS_INVALID_POST_ID');
			$type		= 'error';
		}
		else
		{
			$model		= $this->getModel( 'Reports' );

			if( $model->publish( $post , 1 ) )
			{
				$message	= JText::_('COM_EASYDISCUSS_POST_PUBLISHED');
			}
			else
			{
				$message	= JText::_('COM_EASYDISCUSS_ERROR_PUBLISHING_POST');
				$type		= 'error';
			}

		}

		DiscussHelper::setMessageQueue( $message , $type );

		$this->setRedirect( 'index.php?option=com_easydiscuss&view=reports' );
	}

	function unpublish()
	{
		$post	= JRequest::getVar( 'cid' , array(0) , 'POST' );

		$message	= '';
		$type		= 'success';

		if( count( $post ) <= 0 )
		{
			$message	= JText::_('COM_EASYDISCUSS_INVALID_POST_ID');
			$type		= 'error';
		}
		else
		{
			$model		= $this->getModel( 'Reports' );

			if( $model->publish( $post , 0 ) )
			{
				$message	= JText::_('COM_EASYDISCUSS_POST_UNPUBLISHED');
			}
			else
			{
				$message	= JText::_('COM_EASYDISCUSS_ERROR_UNPUBLISHING_POST');
				$type		= 'error';
			}

		}

		DiscussHelper::setMessageQueue( $message , $type );

		$this->setRedirect( 'index.php?option=com_easydiscuss&view=reports' );
	}

	function togglePublish()
	{
		$postId		= JRequest::getInt( 'post_id' , '0' , 'POST' );
		$postVal	= JRequest::getInt( 'post_val' , '0' , 'POST' );

		$model		= $this->getModel( 'Reports' );
		$message	= '';
		$type		= 'success';

		if(empty($postId))
		{
			$message	= JText::_('COM_EASYDISCUSS_INVALID_POST_ID');
			$type		= 'error';
		}

		if($postVal && !empty($postId))
		{
			if( $model->publish( array($postId) , 1 ) )
			{
				$message	= JText::_('COM_EASYDISCUSS_POST_PUBLISHED');
			}
			else
			{
				$message	= JText::_('COM_EASYDISCUSS_ERROR_PUBLISHING_POST');
				$type		= 'error';
			}
		}
		else
		{
			if( $model->publish( array($postId) , 0 ) )
			{
				$message	= JText::_('COM_EASYDISCUSS_POST_UNPUBLISHED');
			}
			else
			{
				$message	= JText::_('COM_EASYDISCUSS_ERROR_UNPUBLISHING_POST');
				$type		= 'error';
			}
		}
		DiscussHelper::setMessageQueue( $message , $type );

		$this->setRedirect( 'index.php?option=com_easydiscuss&view=reports' );
	}

	function removeReports()
	{
		$postId		= JRequest::getInt( 'post_id' , '0' , 'POST' );

		$model		= $this->getModel( 'Reports' );
		$message	= '';
		$type		= 'success';

		if(empty($postId))
		{
			$message	= JText::_('COM_EASYDISCUSS_INVALID_POST_ID');
			$type		= 'error';
		}

		$model->removeReports($postId);


		$message	= JText::_('COM_EASYDISCUSS_REPORT_ABUSE_REMOVED');
		DiscussHelper::setMessageQueue( $message , $type );
		$this->setRedirect( 'index.php?option=com_easydiscuss&view=reports' );
	}

	function edit()
	{
		JRequest::setVar( 'view', 'post' );
		JRequest::setVar( 'id' , JRequest::getVar( 'id' , '' , 'REQUEST' ) );
		JRequest::setVar( 'source' , 'reports' );

		parent::display();
	}

	function remove()
	{
		$post	= JRequest::getVar( 'cid' , array(0) , 'POST' );

		$message	= '';
		$type		= 'success';

		if( count( $post ) <= 0 )
		{
			$message	= JText::_('COM_EASYDISCUSS_INVALID_POST_ID');
			$type		= 'error';
		}
		else
		{
			$model		= $this->getModel( 'Reports' );

			for($i = 0; $i < count($post); $i++)
			{
				$pid = $post[$i];
				$model->removePostReports($pid);
			}
		}

		$message	= JText::_('COM_EASYDISCUSS_POST_DELETED');
		DiscussHelper::setMessageQueue( $message , $type );
		$this->setRedirect( 'index.php?option=com_easydiscuss&view=reports' );
	}

	function deletePost()
	{
		$postId		= JRequest::getInt( 'post_id' , '0' , 'POST' );

		$model		= $this->getModel( 'Reports' );
		$message	= '';
		$type		= '';

		if(empty($postId))
		{
			$message	= JText::_('COM_EASYDISCUSS_INVALID_POST_ID');
			$type		= 'error';
		}

		$postTbl = JTable::getInstance( 'posts', 'Discuss' );
		$postTbl->load($postId);

		$repliesDeleted = false;

		if($postTbl->is_parent == 0)
		{
			//we need to delete the child record.
			$repliesDeleted = $model->deleteReplies($postTbl->id);
		}

		$model->removePostReports($postId);

		$message	= JText::_('COM_EASYDISCUSS_POST_DELETED');
		if($repliesDeleted)
			$message	= JText::_('COM_EASYDISCUSS_POST_DELETED_WITH_REPLIES');

		DiscussHelper::setMessageQueue( $message , $type );

		$this->setRedirect( 'index.php?option=com_easydiscuss&view=reports' );
	}
}
