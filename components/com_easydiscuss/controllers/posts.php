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

require_once JPATH_ROOT . '/components/com_easydiscuss/helpers/helper.php';
require_once DISCUSS_HELPERS . '/date.php';
require_once DISCUSS_HELPERS . '/input.php';
require_once DISCUSS_HELPERS . '/filter.php';

class EasyDiscussControllerPosts extends EasyDiscussController
{
	public function __construct()
	{
		parent::__construct();

		// Register task aliases
		$this->registerTask( 'unfeature' , 'feature' );
	}

	/**
	 * Merges discussion.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return	
	 */
	public function merge()
	{
		$newParent 		= JRequest::getInt( 'id' );
		$currentParent 	= JRequest::getInt( 'current' );

		$newPost 		= DiscussHelper::getTable( 'Post' );
		$newPost->load( $newParent );

		// Update the current parent and change it's parent to the new parent.
		$post 				= DiscussHelper::getTable( 'Post' );
		$post->load( $currentParent );
		$post->parent_id	= $newParent;
		
		// Update the tags.
		if( !$post->store() )
		{
			DiscussHelper::setMessageQueue( JText::sprintf( 'COM_EASYDISCUSS_MERGE_ERROR' , $newPost->title ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::getPostRoute( $newParent , false ) ); 
		}

		// Update all the child items from this parent to the new parent.
		$model 			= DiscussHelper::getModel( 'Posts' );
		$model->updateNewParent( $currentParent , $newParent );

		// Once done, redirect the user to the new page.
		$app 			= JFactory::getApplication();

		// Set proper message in mail queue.
		DiscussHelper::setMessageQueue( JText::sprintf( 'COM_EASYDISCUSS_MERGE_SUCCESS' , $newPost->title ) , DISCUSS_QUEUE_SUCCESS );
		$app->redirect( DiscussRouter::getPostRoute( $newParent , false ) ); 
	}

	/**
	 * Use to move a post to a new category.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function move()
	{
		JRequest::checkToken('request') or jexit( 'Invalid Token' );

		$id			= JRequest::getInt( 'id' , 0 );
		$categoryId	= JRequest::getInt( 'category_id' , 0 );
		$app		= JFactory::getApplication();
		$my			= JFactory::getUser();

		// Load the post object.
		$post		= DiscussHelper::getTable( 'Post' );
		$state		= $post->load( $id );

		// Load the category.
		$newCategory	= DiscussHelper::getTable( 'Category' );
		$newCategory->load( $categoryId );

		// Only main post can be moved.
		if( !$id || !$state || !$post->id || $post->parent_id || !$categoryId || !$newCategory->id )
		{
			DiscussHelper::setMessageQueue( JText::_( 'COM_EASYDISCUSS_INVALID_ID_PROVIDED' ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss' , false ) );
			return $app->close();
		}

		$category 	= DiscussHelper::getTable( 'Category' );
		$category->load( $post->category_id );

		// Load the access.
		$access		= $post->getAccess( $category );

		if( !$my->id || !$access->canMove() )
		{
			DiscussHelper::setMessageQueue( JText::_( 'COM_EASYDISCUSS_SYSTEM_INSUFFICIENT_PERMISSIONS' ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::getPostRoute( $post->id , false ) );
			return $app->close();
		}

		// Switch to the new category and save it.
		$post->category_id 	= $categoryId;
		$post->store();

		// @TODO: Send notification to post owner that their discussion have been moved.

		DiscussHelper::setMessageQueue( JText::_( 'COM_EASYDISCUSS_POST_MOVED_SUCCESSFULLY' ) , DISCUSS_QUEUE_SUCCESS );
		$app->redirect( DiscussRouter::getPostRoute( $post->id , false ) );
	}

	/**
	 * Method to validate the password supplied by the user.
	 *
	 * @since	3.0
	 * @access	public
	 * @param	null
	 */
	public function setPassword()
	{
		$id 	= JRequest::getInt( 'id' );

		// Since return URLs are base64 encoded, we need to decode it back again.
		$return	= DiscussRouter::_( 'index.php?option=com_easydiscuss' , false );

		if( !$id )
		{
			DiscussHelper::setMessageQueue( JText::_( 'COM_EASYDISCUSS_INVALID_ID_PROVIDED' ) , DISCUSS_QUEUE_ERROR );
			$this->setRedirect( $return );
		}

		// Get password from the request.
		$password 	= JRequest::getVar( 'discusspassword' , '' );

		// If password is empty, we should throw some errors here.
		if( !$password )
		{
			DiscussHelper::setMessageQueue( JText::_( 'COM_EASYDISCUSS_INVALID_PASSWORD_PROVIDED' ) , DISCUSS_QUEUE_ERROR );
			$this->setRedirect( $return );
		}

		// Set the password that the user posted into the session's name space.
		$session 	= JFactory::getSession();
		$session->set( 'DISCUSSPASSWORD_' . $id , $password , 'com_easydiscuss' );

		$post 		= DiscussHelper::getTable( 'Post' );
		$post->load( $id );

		// Verify that the password matches
		if( $post->password != $password )
		{
			DiscussHelper::setMessageQueue( JText::_( 'COM_EASYDISCUSS_INVALID_PASSWORD_PROVIDED' ) , DISCUSS_QUEUE_ERROR );
			$this->setRedirect( $return );
		}

		// If the user supplied the correct password, we want to redirect them to the correct page.
		$return	= JRequest::getVar( 'return' );
		$return = base64_decode( $return );
		$return	= DiscussRouter::_( $return , false );

		// If the user passes here, then the page should be visible to the user.
		$this->setRedirect( $return );
	}

	/**
	 * Accepts a post as an answer
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function accept()
	{
		JRequest::checkToken('request') or jexit( 'Invalid Token' );

		$id		= JRequest::getInt( 'id' );
		$app 	= JFactory::getApplication();

		if( !$id )
		{
			DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_SYSTEM_INVALID_ID' ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss' , false ) );
			$app->close();
		}

		$config	= DiscussHelper::getConfig();
		$acl	= DiscussHelper::getHelper( 'ACL' );

		$reply  = DiscussHelper::getTable( 'Post' );
		$reply->load( $id );

		$question	= DiscussHelper::getTable( 'Post' );
		$question->load( $reply->parent_id );

		$isResolved	= $question->isresolve;
		$isMine		= DiscussHelper::isMine( $question->user_id );
		$isAdmin	= DiscussHelper::isSiteAdmin();

		if ( !$isMine && !$isAdmin && !$acl->allowed( 'mark_answered', '0') )
		{
			DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_SYSTEM_INSUFFICIENT_PERMISSIONS' ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::getPostRoute( $question->id , false ) );
			$app->close();
		}

		// Update reply table.
		$reply->answered	 = '1';
		$reply->store();

		// Update question table.
		$question = DiscussHelper::getTable( 'Post' );
		$question->load( $reply->parent_id );
		$question->isresolve = DISCUSS_ENTRY_RESOLVED;
		$question->store();

		$email  = array();

		//now send notification.
		$notify	= DiscussHelper::getNotification();

		// sending notification to person who made the reply
		$emailSubject	= JText::sprintf('COM_EASYDISCUSS_YOUR_REPLY_NOW_ACCEPTED', $question->title );
		$emailTemplate  = 'email.reply.marked.answered.php';

		$replyUser 					= DiscussHelper::getTable( 'Profile' );
		$replyUser->load( $reply->user_id );

		$emailData						= array();
		$emailData['postTitle']			= $question->title;
		$emailData['postLink']			= DiscussRouter::getRoutedURL('index.php?option=com_easydiscuss&view=post&id=' . $question->id, false, true);
		$emailData['replyAuthor']		= ( $replyUser->id ) ? $replyUser->getName() : $reply->poster_name;
		$emailData['replyAuthorAvatar'] = $replyUser->getAvatar();
		$emailData['replyContent']		= DiscussHelper::parseContent( $reply->content );

		if( empty( $reply->user_id ) )
		{
			$email[] = $reply->poster_email;
		}
		else
		{
			$email[] = $replyUser->user->email;
		}
		$notify->addQueue( $email, $emailSubject, '', $emailTemplate, $emailData);
		// sending notification to person who made the reply end.

		// Send notification to post owner when post is marked as answered.
		if( $config->get( 'notify_owner_answer' ) && $question->user_id != $my->id)
		{
			$email  = array();
			// prepare email content and information.
			$emailSubject	= JText::sprintf('COM_EASYDISCUSS_REPLY_NOW_ACCEPTED', $question->title );
			$emailTemplate  = 'email.reply.answered.php';

			//get owner email.
			if( !empty( $question->user_id ) )
			{
				$ownerUser  = JFactory::getUser( $question->user_id );
				$email[]	= $ownerUser->email;
			}

			if( !empty($email) )
			{
				$notify->addQueue( $email, $emailSubject, '', $emailTemplate, $emailData);
			}
		}

		$my		= JFactory::getUser();

		if( $reply->get( 'user_id' ) != $my->id )
		{
			// @rule: Add badges
			DiscussHelper::getHelper( 'History' )->log( 'easydiscuss.answer.reply' , $reply->get( 'user_id' ) , JText::sprintf( 'COM_EASYDISCUSS_HISTORY_ACCEPTED_REPLY' , $question->title ) );

			DiscussHelper::getHelper( 'Badges' )->assign( 'easydiscuss.answer.reply' , $reply->get( 'user_id' ) );
			DiscussHelper::getHelper( 'Points' )->assign( 'easydiscuss.answer.reply' , $reply->get( 'user_id' ) );

			//AUP
			DiscussHelper::getHelper( 'Aup' )->assign( DISCUSS_POINTS_ACCEPT_REPLY , $reply->get( 'user_id' ) , JText::sprintf( 'COM_EASYDISCUSS_HISTORY_ACCEPTED_REPLY' , $question->title ) );


			// @rule: Add notifications for the thread starter
			$notification	= DiscussHelper::getTable( 'Notifications' );
			$notification->bind( array(
					'title'		=> JText::sprintf( 'COM_EASYDISCUSS_ACCEPT_ANSWER_DISCUSSION_NOTIFICATION_TITLE' , $question->title ),
					'cid'		=> $question->get( 'id' ),
					'type'		=> DISCUSS_NOTIFICATIONS_ACCEPTED,
					'target'	=> $reply->get( 'user_id' ),
					'author'	=> $my->id,
					'permalink'	=> 'index.php?option=com_easydiscuss&view=post&id=' . $question->get( 'id' ) . '#answer'
				) );
			$notification->store();
		}

		DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_REPLY_ACCEPTED_AS_ANSWER' ) , DISCUSS_QUEUE_SUCCESS );
		$app->redirect( DiscussRouter::getPostRoute( $question->id , false ) );
		$app->close();
	}

	/**
	 * Rejects a post as an answer
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function reject()
	{
		JRequest::checkToken('request') or jexit( 'Invalid Token' );

		$id		= JRequest::getInt( 'id' );
		$app 	= JFactory::getApplication();

		if( !$id )
		{
			DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_SYSTEM_INVALID_ID' ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss' , false ) );
			$app->close();
		}

		$config	= DiscussHelper::getConfig();
		$acl	= DiscussHelper::getHelper( 'ACL' );

		$reply  = DiscussHelper::getTable( 'Post' );
		$reply->load( $id );

		$question	= DiscussHelper::getTable( 'Post' );
		$question->load( $reply->parent_id );

		$isMine		= DiscussHelper::isMine( $question->user_id );
		$isAdmin	= DiscussHelper::isSiteAdmin();

		if ( !$isMine && !$isAdmin && !$acl->allowed( 'mark_answered', '0') )
		{
			DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_SYSTEM_INSUFFICIENT_PERMISSIONS' ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::getPostRoute( $question->id , false ) );
			$app->close();
		}

		// Update reply
		$reply->answered	 = '0';
		$reply->store();

		// Update question
		$question 	= DiscussHelper::getTable( 'Post' );
		$question->load( $reply->parent_id );
		$question->isresolve = DISCUSS_ENTRY_UNRESOLVED;
		$question->store();

		if( $config->get( 'notify_owner_answer' ) )
		{
			//now send notification.
			$notify	= DiscussHelper::getNotification();

			// prepare email content and information.
			$emailSubject	= JText::sprintf('COM_EASYDISCUSS_REPLY_NOW_UNACCEPTED', $question->title);
			$emailTemplate  = 'email.reply.unanswered.php';

			$emailData					= array();
			$emailData['postTitle']		= $question->title;
			$emailData['postLink']		= DiscussRouter::getRoutedURL('index.php?option=com_easydiscuss&view=post&id=' . $question->id, false, true);

			//get owner email.
			$email  = '';
			if( !empty( $question->user_id ) )
			{
				$ownerUser  = JFactory::getUser( $question->user_id );
				$email	= $ownerUser->email;
			}

			if( !empty($email) )
			{
				$notify->addQueue( $email, $emailSubject, '', $emailTemplate, $emailData);
			}
		}

		DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_REPLY_REJECTED_AS_ANSWER' ) , DISCUSS_QUEUE_SUCCESS );
		$app->redirect( DiscussRouter::getPostRoute( $question->id , false ) );
		$app->close();
	}


	/*
	 * Allows anyone to approve replies provided that they get the correct key
	 *
	 * @param	null
	 * @return	null
	 */
	public function approvePost()
	{
		$mainframe	= JFactory::getApplication();
		$key		= JRequest::getVar( 'key' , '' );
		$redirect	= DiscussRouter::_( 'index.php?option=com_easydiscuss&view=index' , false );

		if( empty( $key ) )
		{
			$mainframe->redirect( $redirect , JText::_( 'COM_EASYDISCUSS_NOT_ALLOWED_HERE' ) , 'error' );
			$mainframe->close();
		}

		$hashkey	= DiscussHelper::getTable( 'HashKeys' );

		if( !$hashkey->loadByKey( $key ) )
		{
			$mainframe->redirect( $redirect , JText::_( 'COM_EASYDISCUSS_NOT_ALLOWED_HERE' ) , 'error' );
			$mainframe->close();
		}

		$post	= DiscussHelper::getTable( 'Post' );
		$post->load( $hashkey->uid );
		$post->published    = DISCUSS_ID_PUBLISHED;

		// @trigger: onBeforeSave
		$isNew	= (bool) $post->id;
		DiscussEventsHelper::importPlugin( 'content' );
		DiscussEventsHelper::onContentBeforeSave('post', $post, $isNew);

		if ( !$post->store() )
		{
			JError::raiseError(500, $post->getError() );
		}

		// @rule: Send out notifications when the pending moderation items are being published.
		DiscussHelper::sendNotification( $post , $post->parent_id, true, $post->user_id, DISCUSS_ID_PENDING);

		// @trigger: onAfterSave
		DiscussEventsHelper::onContentAfterSave('post', $post, $isNew);

		// Delete the unused hashkey now.
		$hashkey->delete();

		$message    = $hashkey->type == DISCUSS_REPLY_TYPE ? JText::_( 'COM_EASYDISCUSS_MODERATE_REPLY_PUBLISHED' ) : JText::_( 'COM_EASYDISCUSS_MODERATE_POST_PUBLISHED' );
		$pid        = $hashkey->type == DISCUSS_REPLY_TYPE ? $post->parent_id : $post->id;
		$mainframe->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss&view=post&id=' . $pid , false ) , $message , 'success' );
	}

	public function rejectPost()
	{
		$mainframe	= JFactory::getApplication();
		$key		= JRequest::getVar( 'key' , '' );
		$redirect	= DiscussRouter::_( 'index.php?option=com_easydiscuss&view=index' , false );

		if( empty( $key ) )
		{
			$mainframe->redirect( $redirect , JText::_( 'COM_EASYDISCUSS_NOT_ALLOWED_HERE' ) , 'error' );
			$mainframe->close();
		}

		$hashkey	= DiscussHelper::getTable( 'HashKeys' );

		if( !$hashkey->loadByKey( $key ) )
		{
			$mainframe->redirect( $redirect , JText::_( 'COM_EASYDISCUSS_NOT_ALLOWED_HERE' ) , 'error' );
			$mainframe->close();
		}

		$post	= DiscussHelper::getTable( 'Post' );
		$post->load( $hashkey->uid );
		$post->published    = DISCUSS_ID_UNPUBLISHED;

		// @trigger: onBeforeSave
		$isNew	= (bool) $post->id;


		DiscussEventsHelper::importPlugin( 'content' );
		DiscussEventsHelper::onContentBeforeSave('post', $post, $isNew);

		if ( !$post->store() )
		{
			JError::raiseError(500, $post->getError() );
		}

		// @trigger: onAfterSave
		DiscussEventsHelper::onContentAfterSave('post', $post, $isNew);

		// Delete the unused hashkey now.
		$hashkey->delete();

		$message    = $hashkey->type == DISCUSS_REPLY_TYPE ? JText::_( 'COM_EASYDISCUSS_MODERATE_REPLY_UNPUBLISHED' ) : JText::_( 'COM_EASYDISCUSS_MODERATE_POST_UNPUBLISHED' );
		$pid        = $hashkey->type == DISCUSS_REPLY_TYPE ? $post->parent_id : $post->id;
		$mainframe->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss&view=post&id=' . $pid , false ) , $message , 'success' );
	}

	/**
	 * Delete current post given the post id.
	 * It will also delete all childs related to this entry.
	 */
	function delete()
	{
		JRequest::checkToken('request') or jexit( 'Invalid Token' );

		$my			= JFactory::getUser();
		$id			= JRequest::getInt( 'id' );
		$mainframe	= JFactory::getApplication();
		$url		= JRequest::getVar( 'url' , '' );

		$reply 		= DiscussHelper::getTable( 'Post' );
		$reply->load( $id );

		if( !$id )
		{
			DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_ENTRY_DELETE_MISSING_ID') , DISCUSS_QUEUE_ERROR );
			$mainframe->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss&view=index' , false ) );
			return;
		}

		$post	= DiscussHelper::getTable( 'Post' );
		$post->load( $id );

		$acl		= DiscussHelper::getHelper( 'ACL' );
		$type		= $post->parent_id ? 'reply' : 'question';

		$isMine		= DiscussHelper::isMine( $post->user_id );
		$isAdmin	= DiscussHelper::isSiteAdmin();

		// @rule: Redirect to the parent's page
		if( $type == 'reply' )
		{
			$url 	= DiscussRouter::_( 'index.php?option=com_easydiscuss&view=post&id=' . $post->parent_id , false );
		}
		else
		{
			$url 	= DiscussRouter::_( 'index.php?option=com_easydiscuss&view=index' , false  );
		}

		$category   = DiscussHelper::getTable( 'Category' );
		$category->load( $post->category_id );

		$access = $post->getAccess( $category );

		if ( !$access->canDelete() )
		{
			DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_ENTRY_DELETE_NO_PERMISSION') , DISCUSS_QUEUE_ERROR );
			$mainframe->redirect( $url );
			return;
		}

		if( $post->islock && !$isAdmin )
		{
			DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_ENTRY_DELETE_LOCKED') , DISCUSS_QUEUE_ERROR );
			$mainframe->redirect( $url );
			return;
		}

		// @trigger: onBeforeDelete
		DiscussEventsHelper::importPlugin( 'content' );
		DiscussEventsHelper::onContentBeforeDelete('post', $post);

		if( !$post->delete() )
		{
			DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_ENTRY_DELETE_ERROR') , DISCUSS_QUEUE_ERROR );
			$mainframe->redirect( $url );
			return;
		}

		// @trigger: onAfterDelete
		DiscussEventsHelper::onContentAfterDelete('post', $post);

		// @rule: Process AUP integrations
		if( empty( $post->parent_id ) )
		{
			DiscussHelper::getHelper( 'Aup' )->assign( DISCUSS_POINTS_DELETE_DISCUSSION , $post->user_id , $post->title );
		}
		else
		{
			DiscussHelper::getHelper( 'Aup' )->assign( DISCUSS_POINTS_DELETE_REPLY , $post->user_id , $post->title );
		}

		if( $type == 'question' )
		{
			$model		= $this->getModel('Posts');
			$model->deleteAllReplies( $id );

			// Delete custom fields
			$ruleModel = DiscussHelper::getModel( 'CustomFields' );
			$ruleModel->deleteCustomFieldsValue( $this->id, 'post' );

			// Delete all favourite
			$favModel = DiscussHelper::getModel( 'Favourites' );
			$favModel->deleteAllFavourites( $id );

			$url		= DiscussRouter::_( 'index.php?option=com_easydiscuss&view=index' , false );

			DiscussHelper::setMessageQueue( JText::_( 'COM_EASYDISCUSS_ENTRY_DELETED' ) , DISCUSS_QUEUE_SUCCESS );
		}
		else
		{
			//this is a reply delete. now we check if this reply get accepted previously or not.
			// if yes, then upload the parent post to unresolved.
			$answerRemoved  = false;

			if( $post->answered )
			{
				$parent	= DiscussHelper::getTable( 'Post' );
				$parent->load( $post->parent_id );
				$parent->isresolve = DISCUSS_ENTRY_UNRESOLVED;
				$parent->store();

				$answerRemoved  = true;
			}

			$ruleModel = DiscussHelper::getModel( 'CustomFields' );
			$ruleModel->deleteCustomFieldsValue( $this->id, 'post' );

			$msgText	= ( $answerRemoved ) ? JText::_( 'COM_EASYDISCUSS_REPLY_DELETED_AND_UNRESOLVED' ) : JText::_( 'COM_EASYDISCUSS_REPLY_DELETED' ) ;
			DiscussHelper::setMessageQueue( $msgText , DISCUSS_QUEUE_SUCCESS );
		}

		$mainframe->redirect( $url );

		return;
	}


	/**
	 * Handles POST request for new discussions
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function submit()
	{
		JRequest::checkToken('request') or jexit( 'Invalid Token' );

		$config		= DiscussHelper::getConfig();
		$my			= JFactory::getUser();
		$app		= JFactory::getApplication();
		$acl		= DiscussHelper::getHelper( 'ACL' );
		$Ajax		= DiscussHelper::getHelper( 'Ajax' );

		// If guest posting is disallowed in the settings, they shouldn't be able to create a discussion at all.
		if( !$my->id && !$acl->allowed('add_question', '0') )
		{
			DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_POST_PLEASE_LOGIN' ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::_('index.php?option=com_easydiscuss' , false ) );
			return $app->close();
		}

		// If user is disallowed in the acl, they shouldn't be able to create a discussion at all.
		if( $my->id && !$acl->allowed('add_question', '0') )
		{
			DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_SYSTEM_INSUFFICIENT_PERMISSIONS' ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::_('index.php?option=com_easydiscuss' , false ) );
			return $app->close();
		}

		// Get values from the posted form.
		$data 	= JRequest::get( 'post' );

		// Run validation on the posted data.
		if(! $this->_fieldValidate($data))
		{
			$files		= JRequest::getVar( 'filedata' , array() , 'FILES');
			$data['attachments'] = $files;

			DiscussHelper::storeSession( $data , 'NEW_POST_TOKEN' );
			$app->redirect( DiscussRouter::getAskRoute(  null , false ) );
		}

		// get id if available
		$id			= JRequest::getInt('id', 0);

		// bind the table
		$post	= DiscussHelper::getTable( 'Post' );
		$post->load( $id );

		// set is new value
		$isNew		= !$post->id ? true : false;

		require_once( DISCUSS_CLASSES . '/recaptcha.php' );

		if( DiscussRecaptcha::isRequired() )
		{
			$obj = DiscussRecaptcha::recaptcha_check_answer( $config->get( 'antispam_recaptcha_private' ) , $_SERVER['REMOTE_ADDR'] , $data['recaptcha_challenge_field'] , $data['recaptcha_response_field'] );

			if(!$obj->is_valid)
			{
				DiscussHelper::storeSession( $data , 'NEW_POST_TOKEN');
				DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_POST_INVALID_RECAPTCHA_RESPONSE' ) , DISCUSS_QUEUE_ERROR );
				$app->redirect( DiscussRouter::getAskRoute() );
				return;
			}
		}

		$previousTags = array();

		if(!$isNew)
		{
			//check if admin or is owner before allowing edit.
			$isMine		= DiscussHelper::isMine($post->user_id);
			$isAdmin	= DiscussHelper::isSiteAdmin();
			$isEditor 	= $acl->allowed( 'edit_question' );

			if( !$my->id && !$isMine && !$isAdmin && !$isEditor )
			{
				DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_NO_PERMISSION_TO_PERFORM_THE_REQUESTED_ACTION' ) , DISCUSS_QUEUE_ERROR );
				$this->setRedirect( DiscussRouter::_( 'index.php?option=com_easydiscuss&view=post&id='.$id , false) );
				return;
			}

			// If this is an edited post, we need to remove existing tags and add them back again.
			$postsTagsModel = $this->getModel('PostsTags');
			$tmppreviousTags = $postsTagsModel->getPostTags($id);
			if(!empty($tmppreviousTags))
			{
				foreach($tmppreviousTags as $previoustag)
				{
					$previousTags[] = $previoustag->id;
				}
			}

			if($acl->allowed('add_tag', '0'))
			{
				$postsTagsModel->deletePostTag( $id );
			}
		}

		// Get raw content from request as we may need to respect the html codes.
		$content 	= JRequest::getVar( 'dc_reply_content' , '' , 'post' , 'none' , JREQUEST_ALLOWRAW );

		// Ensure that the posted content is respecting the correct values.
		$data[ 'dc_reply_content' ]	= $content;

		// Cleanup alias.
		$alias 				= DiscussHelper::wordFilter( $data[ 'title' ] );
		$data[ 'alias' ]	= DiscussHelper::getAlias( $alias , 'post' , $post->id );

		// Detect the poster type.
		$data[ 'user_type' ]	= empty( $my->id ) ? 'guest' : 'member';

		// Akismet configurations.
		if( $config->get( 'antispam_akismet' ) && ( $config->get('antispam_akismet_key') ) )
		{
			require_once DISCUSS_CLASSES . '/akismet.php';

			$akismet = new Akismet( DISCUSS_JURIROOT , $config->get( 'antispam_akismet_key' ) , array(
							'author'    => $my->name,
							'email'     => $my->email,
							'website'   => DISCUSS_JURIROOT ,
							'body'      => $data['content'] ,
							'alias' => ''
							) );

			// Detect if there's any errors in Akismet.
			if( !$akismet->errorsExist() && $akismet->isSpam() )
			{
				DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_AKISMET_SPAM_DETECTED' ) , DISCUSS_QUEUE_ERROR );
				$app->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss&view=ask' , false ) );
				return $app->close();
			}
		}

		// Get previous status before binding.
		$prevPostStatus			= $post->published;

		// If post is being edited, do not change the owner of the item.
		if( !$post->id )
		{
			$data[ 'user_id' ]	= !$post->user_id ? $my->id : $post->user_id;
		}

		// Check permission to modify assignee
		$category = DiscussHelper::getTable( 'Category' );
		$access	= $post->getAccess( $category );
		if( $access->canAssign() )
		{
			$assignment = DiscussHelper::getTable( 'PostAssignment' );
			$assignment->load($post->id);

			// Add new record if assignee was changed
			if( array_key_exists('assignee_id', $data) && ($assignment->assignee_id != $data['assignee_id']) )
			{
				$newAssignment = DiscussHelper::getTable( 'PostAssignment' );

				$newAssignment->post_id		= $post->id;
				$newAssignment->assignee_id	= (int) $data['assignee_id'];
				$newAssignment->assigner_id	= (int) JFactory::getUser()->id;

				if( !$newAssignment->store() )
				{
					$ajax->fail( 'Storing failed' );
					return $ajax->send();
				}
			}
		}

		// Bind posted data against the table.
		$post->bind( $data , true );

		// Set all post to be published by default.
		$post->published	= DISCUSS_ID_PUBLISHED;

		// Detect if post should be moderated.
		if( $config->get( 'main_moderatepost' ) && !DiscussHelper::isSiteAdmin( $post->user_id ) )
		{
			$post->published 	= DISCUSS_ID_PENDING;
		}

		// Bind posted parameters such as custom tab contents.
		$post->bindParams( $data );

		// Check for maximum length of content if category has specific settings.
		$category 		= DiscussHelper::getTable( 'Category' );
		$category->load( $post->category_id );

		// If there's a maximum content length specified per category base, then we need to check against the content.
		if( $category->getParam( 'maxlength' ) )
		{
			$length 	= JString::strlen( $post->content );

			if( $length > $category->getParam( 'maxlength_size' , 1000 ) )
			{
				DiscussHelper::storeSession( $data , 'NEW_POST_TOKEN');
				DiscussHelper::setMessageQueue( JText::sprintf('COM_EASYDISCUSS_MAXIMUM_LENGTH_EXCEEDED' , $category->getParam( 'maxlength_size' , 1000 ) ) , DISCUSS_QUEUE_ERROR );
				$app->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss&view=ask' , false ) );
				return $app->close();
			}
		}

		// @trigger: onBeforeSave
		DiscussEventsHelper::importPlugin( 'content' );
		DiscussEventsHelper::onContentBeforeSave('post', $post, $isNew);

		// Try to store the post object.
		if ( !$post->store() )
		{
			DiscussHelper::setMessageQueue( $post->getError() , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::getAskRoute( $category->id , false ) );
			return $app->close();
		}

		//Clear off previous records before storing
		$ruleModel = DiscussHelper::getModel( 'CustomFields' );
		$ruleModel->deleteCustomFieldsValue( $post->id, 'update' );

		// Process custom fields.
		$fieldIds = JRequest::getVar( 'customFields' );
		if( !empty($fieldIds) )
		{
			foreach( $fieldIds as $fieldId )
			{
				$fields	= JRequest::getVar( 'customFieldValue_'.$fieldId );

				if( !empty($fields) )
				{
					// Cater for custom fields select list
					// To detect if there is no value selected for the select list custom fields
					if( in_array( 'defaultList', $fields ) )
					{
						$tempKey = array_search( 'defaultList', $fields );
						$fields[ $tempKey ] = '';
					}
				}

				$post->bindCustomFields( $fields, $fieldId );
			}
		}

		// @trigger: onAfterSave
		DiscussEventsHelper::onContentAfterSave('post', $post, $isNew);

		// Process poll items.
		if( $config->get( 'main_polls' ) )
		{
			$polls			= JRequest::getVar( 'pollitems' );

			if( !is_array( $polls ) )
			{
				$polls 		= array( $polls );
			}

			// If the post is being edited and
			// there is only 1 poll item which is also empty,
			// we need to delete existing polls tied to this post.
			if( count( $polls ) == 1 && empty( $polls[0] ) && !$isNew )
			{
				$post->removePoll();
			}

			if( count( $polls ) > 0 )
			{
				$hasPolls 		= false;

				foreach( $polls as $poll )
				{
					// As long as there is 1 valid poll, we need to store them.
					if( !empty( $poll ) )
					{
						$hasPolls 	= true;
						break;
					}
				}

				if( $hasPolls )
				{
					// Check if the multiple polls checkbox is it checked?
					$multiplePolls	= JRequest::getVar( 'multiplePolls' , '0' );

					// Get the poll question here.
					$pollQuestion	= JRequest::getVar( 'poll_question' , '' );

					// Try to detect which poll items needs to be removed.
					$removePolls	= JRequest::getVar( 'pollsremove' );

					// Get the poll items.
					$pollItems 		= JRequest::getVar( 'pollitems' );

					// Store the polls now.
					$post->bindPolls( $isNew , $pollItems , $removePolls , $multiplePolls , $pollQuestion );
				}
			}
		}

		// Bind file attachments
		if( $acl->allowed( 'add_attachment' ) && $config->get( 'attachment_questions' ) )
		{
			$post->bindAttachments();
		}

		// If admin configures to send notifications to all users, just ignore this block.
		if( !$config->get( 'notify_all') && $config->get( 'notify_owner' ) && $isNew && isset( $data[ 'self_subscribe' ] ) )
		{
			$subscribe = DiscussHelper::getHelper( 'Subscriber' );
			$subscribe->add( $my, $post, 'instant' ,$data );
		}

		// Detect if the current post should be moderated or not.
		$isModerate = ($post->published == DISCUSS_ID_PENDING) ? true : false;

		// Process auto posting for posts that are really published and is in a public category.
		if( $post->published == DISCUSS_ID_PUBLISHED && !$category->private )
		{
			$post->autopost();
		}

		// Detect known names in the post.
		$names 	= DiscussHelper::getHelper( 'String' )->detectNames( $post->content );

		if( $names )
		{
			foreach( $names as $name )
			{
				$name			= JString::str_ireplace( '@' , '' , $name );
				$id 			= DiscussHelper::getUserId( $name );

				if( !$id || $id == $post->get( 'user_id') )
				{
					continue;
				}

				$notification	= DiscussHelper::getTable( 'Notifications' );

				$notification->bind( array(
						'title'		=> JText::sprintf( 'COM_EASYDISCUSS_MENTIONED_QUESTION_NOTIFICATION_TITLE' , $post->get( 'title' ) ),
						'cid'		=> $post->get( 'id' ),
						'type'		=> DISCUSS_NOTIFICATIONS_MENTIONED,
						'target'	=> $id,
						'author'	=> $post->get( 'user_id' ),
						'permalink'	=> 'index.php?option=com_easydiscuss&view=post&id=' . $post->get( 'id' )
					) );
				$notification->store();
			}
		}

		if( ($isNew || $prevPostStatus == DISCUSS_ID_PENDING ) && $post->published == DISCUSS_ID_PUBLISHED )
		{
			$post->ping();
		}

		$notify	= DiscussHelper::getNotification();

		// badwords filtering for email data.
		$post->title		= DiscussHelper::wordFilter( $post->title);
		$post->content		= DiscussHelper::wordFilter( $post->content);

		if($acl->allowed('add_tag', '0'))
		{
			//@task: Save tags
			$postTagModel	= $this->getModel( 'PostsTags' );
			$tags			= JRequest::getVar( 'tags' , '' , 'POST' );

			if( !empty( $tags ) )
			{
				$tagModel	= $this->getModel( 'Tags' );

				foreach ( $tags as $tag )
				{
					if ( !empty( $tag ) )
					{
						$tagTable	= DiscussHelper::getTable( 'Tags' );

						//@task: Only add tags if it doesn't exist.
						if( !$tagTable->exists( $tag ) )
						{
							$tagTable->set( 'title' 	, JString::trim( $tag ) );
							$tagTable->set( 'alias' 	, DiscussHelper::getAlias( $tag, 'tag' ) );
							$tagTable->set( 'created'	, DiscussHelper::getDate()->toMySQL() );
							$tagTable->set( 'published' , 1 );
							$tagTable->set( 'user_id'	, $my->id );

							$tagTable->store();
						}
						else
						{
							$tagTable->load( $tag , true );
						}

						$postTagInfo = array();

						//@task: Store in the post tag
						$postTagTable	= DiscussHelper::getTable( 'PostsTags' );
						$postTagInfo['post_id']	= $post->id;
						$postTagInfo['tag_id']	= $tagTable->id;

						$postTagTable->bind( $postTagInfo );
						$postTagTable->store();
					}
				}
			}
		}

		// prepare email content and information.
		$profile = DiscussHelper::getTable( 'Profile' );
		$profile->load( $my->id );

		// For use within the emails.
		$emailData					= array();
		$emailData['postTitle']		= $post->title;
		$emailData['postAuthor']	= $profile->id ? $profile->getName() : $post->poster_name;
		$emailData['postAuthorAvatar' ] = $profile->getAvatar();
		$emailData['postLink']		= DiscussRouter::getRoutedURL('index.php?option=com_easydiscuss&view=post&id=' . $post->id, false, true);
		$emailData['postContent']	= DiscussHelper::parseContent($post->trimEmail( $post->content ));
		$emailData['post_id']		= $post->id;
		$emailData['cat_id']		= $post->category_id;
		$emailData['emailTemplate']	= 'email.subscription.site.new.php';
		$emailData['emailSubject']	= JText::sprintf('COM_EASYDISCUSS_NEW_QUESTION_ASKED', $post->id , $post->title);

		if( $isModerate )
		{
			// Generate hashkeys to map this current request
			$hashkey		= DiscussHelper::getTable( 'HashKeys' );
			$hashkey->uid	= $post->id;
			$hashkey->type	= DISCUSS_QUESTION_TYPE;
			$hashkey->store();

			require_once DISCUSS_HELPERS . '/router.php';
			$approveURL		= DiscussHelper::getExternalLink('index.php?option=com_easydiscuss&controller=posts&task=approvePost&key=' . $hashkey->key );
			$rejectURL		= DiscussHelper::getExternalLink('index.php?option=com_easydiscuss&controller=posts&task=rejectPost&key=' . $hashkey->key );
			$emailData[ 'moderation' ]	= '<div style="display:inline-block;width:100%;padding:20px;border-top:1px solid #ccc;padding:20px 0 10px;margin-top:20px;line-height:19px;color:#555;font-family:\'Lucida Grande\',Tahoma,Arial;font-size:12px;text-align:left">';
			$emailData[ 'moderation' ] .= '<a href="' . $approveURL . '" style="display:inline-block;padding:5px 15px;background:#fc0;border:1px solid #caa200;border-bottom-color:#977900;color:#534200;text-shadow:0 1px 0 #ffe684;font-weight:bold;box-shadow:inset 0 1px 0 #ffe064;-moz-box-shadow:inset 0 1px 0 #ffe064;-webkit-box-shadow:inset 0 1px 0 #ffe064;border-radius:2px;moz-border-radius:2px;-webkit-border-radius:2px;text-decoration:none!important">' . JText::_( 'COM_EASYDISCUSS_EMAIL_APPROVE_POST' ) . '</a>';
			$emailData[ 'moderation' ] .= ' ' . JText::_( 'COM_EASYDISCUSS_OR' ) . ' <a href="' . $rejectURL . '" style="color:#477fda">' . JText::_( 'COM_EASYDISCUSS_REJECT' ) . '</a>';
			$emailData[ 'moderation' ] .= '</div>';

			$emailData['emailTemplate']	= 'email.subscription.site.moderate.php';
			$emailData['emailSubject']	= JText::sprintf('COM_EASYDISCUSS_NEW_QUESTION_MODERATE', $post->id , $post->title);
		}
		else
		{
			// Notify site and category subscribers
			if($config->get('main_sitesubscription') && ( $isNew || $prevPostStatus == DISCUSS_ID_PENDING ) && $post->published == DISCUSS_ID_PUBLISHED && !$config->get( 'notify_all' ) )
			{
				DiscussHelper::getHelper( 'Mailer' )->notifySubscribers( $emailData );
			}

			// Notify EVERYBODY
			if( $config->get( 'notify_all' ) && !$isModerate )
			{

				DiscussHelper::getHelper( 'Mailer' )->notifyAllMembers( $emailData, array( $my->email ) );
			}
		}

		// Notify admins and category moderators
		if( ( $isNew || $prevPostStatus == DISCUSS_ID_PENDING ) && !$config->get( 'notify_all') )
		{
			DiscussHelper::getHelper( 'Mailer' )->notifyAdministrators( $emailData, array(), $config->get( 'notify_admin' ), $config->get( 'notify_moderator' ) );
		}

		// @rule: Jomsocial activity integrations & points & ranking
		if( ( $isNew || $prevPostStatus == DISCUSS_ID_PENDING ) && $post->published == DISCUSS_ID_PUBLISHED )
		{
			DiscussHelper::getHelper( 'jomsocial' )->addActivityQuestion( $post );

			// Add logging for user.
			DiscussHelper::getHelper( 'History' )->log( 'easydiscuss.new.discussion' , $my->id , JText::sprintf( 'COM_EASYDISCUSS_BADGES_HISTORY_NEW_POST' , $post->title ) );

			DiscussHelper::getHelper( 'Badges' )->assign( 'easydiscuss.new.discussion' , $my->id );
			DiscussHelper::getHelper( 'Points' )->assign( 'easydiscuss.new.discussion' , $my->id );

			// assign new ranks.
			DiscussHelper::getHelper( 'ranks' )->assignRank( $my->id );

			// aup
			DiscussHelper::getHelper( 'Aup' )->assign( DISCUSS_POINTS_NEW_DISCUSSION , $my->id , $post->title );
		}

		$message 	= ( $isNew ) ? JText::_( 'COM_EASYDISCUSS_POST_STORED' ) : JText::_( 'COM_EASYDISCUSS_EDIT_SUCCESS' );
		$state 	 	= 'success';

		if( $post->isPending() )
		{
			$message 	= JText::_( 'COM_EASYDISCUSS_NOTICE_POST_SUBMITTED_UNDER_MODERATION' );
			$state 		= 'error';
		}

		// Let's set our custom message here.
		DiscussHelper::setMessageQueue( $message , $state );

		$this->setRedirect( DiscussRouter::getPostRoute( $post->id , false ) );
	}

	function _fieldValidate($post)
	{
		$mainframe	= JFactory::getApplication();
		$valid		= true;
		$user		= JFactory::getUser();
		$config 	= DiscussHelper::getConfig();

		$message    = '<ul class="unstyled">';

		if( !isset( $post[ 'title' ] ) || JString::strlen($post['title']) == 0 || $post['title'] == JText::_('COM_EASYDISCUSS_POST_TITLE_EXAMPLE'))
		{
			$message    .= '<li>' . JText::_('COM_EASYDISCUSS_POST_TITLE_CANNOT_EMPTY') . '</li>';
			$valid	= false;
		}

		if( !isset( $post[ 'dc_reply_content' ] ) || (JString::strlen($post['dc_reply_content']) == 0) )
		{
			$message    .= '<li>' . JText::_('COM_EASYDISCUSS_POST_CONTENT_IS_EMPTY') . '</li>';
			$valid	= false;
		}

		if(JString::strlen($post['dc_reply_content']) < $config->get('main_post_min_length'))
		{
			$message    .= '<li>' . JText::sprintf('COM_EASYDISCUSS_POST_CONTENT_LENGTH_IS_INVALID', $config->get('main_post_min_length')) . '</li>';
			$valid	= false;
		}

		if(empty($post['category_id']))
		{
			$message    .= '<li>' . JText::_('COM_EASYDISCUSS_POST_CATEGORY_IS_EMPTY') . '</li>';
			$valid	= false;
		}

		if(empty($user->id))
		{
			if(empty($post['poster_name']))
			{
				$message    .= '<li>' . JText::_('COM_EASYDISCUSS_POST_NAME_IS_EMPTY') . '</li>';
				$valid	= false;
			}

			if(empty($post['poster_email']))
			{
				$message    .= '<li>' . JText::_('COM_EASYDISCUSS_POST_EMAIL_IS_EMPTY') . '</li>';
				$valid	= false;
			}
			else
			{
				require_once DISCUSS_HELPERS . '/email.php';

				if(!DiscussEmailHelper::isValidInetAddress($post['poster_email']))
				{
					$message    .= '<li>' . JText::_('COM_EASYDISCUSS_POST_EMAIL_IS_INVALID') . '</li>';
					$valid	= false;
				}
			}
		}

		$message    .= '</ul>';

		DiscussHelper::setMessageQueue( $message , 'error');

		return $valid;
	}

	/**
	 * Responsible to feature a discussion post.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function feature()
	{
		JRequest::checkToken('request') or jexit( 'Invalid Token' );

		$my			= JFactory::getUser();
		$id 		= JRequest::getInt( 'id' , 0 );
		$app 		= JFactory::getApplication();

		// Load the post.
		$post	= DiscussHelper::getTable( 'Post' );
		$state 	= $post->load( $id );

		if( !$state || !$id )
		{
			DiscussHelper::setMessageQueue( JText::_( 'COM_EASYDISCUSS_INVALID_POST_ID' ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss' , false ) );
			$app->close();
		}

		// Load acl.
		$acl 		= DiscussHelper::getHelper( 'ACL' );

		// Only allow selected users to feature a discussion post.
		if( !DiscussHelper::isSiteAdmin() && !$acl->get( 'feature_post' , 0 ) && !DiscussHelper::isModerator( $post->category_id ) )
		{
			DiscussHelper::setMessageQueue( JText::_( 'COM_EASYDISCUSS_NO_PERMISSION_TO_PERFORM_THE_REQUESTED_ACTION' ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss' , false ) );
			$app->close();
		}

		// Set the featured status.
		$task 			= $this->getTask();
		$post->featured = $task == 'feature' ? 1 : 0;
		$post->store();

		// Send notification to the thread starter that their post is being featured.
		// Only send when the person featuring the post is not himself.
		if( $post->user_id != $my->id && $task == 'feature' )
		{
			$notification	= DiscussHelper::getTable( 'Notifications' );
			$notification->bind( array(
					'title'		=> JText::sprintf( 'COM_EASYDISCUSS_FEATURED_DISCUSSION_NOTIFICATION_TITLE' , $post->title ),
					'cid'		=> $post->id,
					'type'		=> DISCUSS_NOTIFICATIONS_FEATURED,
					'target'	=> $post->user_id,
					'author'	=> $my->id,
					'permalink'	=> 'index.php?option=com_easydiscuss&view=post&id=' . $post->get( 'id' )
				) );
			$notification->store();

			// @TODO: Send email to author?
		}

		// Redirect user back to the post.
		$message 	= $task == 'feature' ? JText::_( 'COM_EASYDISCUSS_FEATURE_POST_IS_FEATURED' ) : JText::_( 'COM_EASYDISCUSS_FEATURE_POST_IS_UNFEATURED' );

		DiscussHelper::setMessageQueue( $message , DISCUSS_QUEUE_SUCCESS );
		$app->redirect( DiscussRouter::getPostRoute( $post->id , false ) );
		$app->close();
	}
}
