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

require_once DISCUSS_ROOT . '/views.php';
require_once DISCUSS_CLASSES . '/composer.php';

class EasyDiscussViewPost extends EasyDiscussView
{
	function display( $tpl = null )
	{
		$app 	= JFactory::getApplication();
		$doc 	= JFactory::getDocument();
		$config	= DiscussHelper::getConfig();

		// Sorting and filters.
		$sort			= JRequest::getString('sort', DiscussHelper::getDefaultRepliesSorting() );
		$filteractive	= JRequest::getString('filter', 'allposts');
		$id				= JRequest::getInt( 'id' );
		$acl			= DiscussHelper::getHelper( 'ACL' );

		// Add noindex for print view by default.
		if( JRequest::getInt( 'print' ) == 1 )
		{
			$doc->setMetadata( 'robots' , 'noindex,follow' );
		}

		// Get current logged in user.
		$my 		= JFactory::getUser();

		// Determine if the logged in user is an admin.
		$isAdmin	= DiscussHelper::isSiteAdmin();

		// Load the post table out.
		$post	= DiscussHelper::getTable( 'Post' );
		$state	= $post->load( $id );


		// If id is not found, we need to redirect gracefully.
		if( !$state || !$post->published || !$id )
		{
			DiscussHelper::setMessageQueue( JText::_( 'COM_EASYDISCUSS_SYSTEM_POST_NOT_FOUND' ) , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss&view=index' , false ) );
			$app->close();
		}

		// Check whether this is a valid discussion
		if( $post->parent_id != 0 || ($post->published == DISCUSS_ID_PENDING && ( !$isAdmin && $post->user_id != $my->id )) )
		{
			DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_SYSTEM_INVALID_ID')  , DISCUSS_QUEUE_ERROR );
			$app->redirect( DiscussRouter::_( 'index.php?option=com_easydiscuss&view=index' , false ) );
			$app->close();
		}

		// Load the category.
		$category	= DiscussHelper::getTable( 'Category' );
		$category->load( (int) $post->category_id );


		if( !$post->category_id )
		{
			if( !$category->canAccess() )
			{
				DiscussHelper::setMessageQueue( JText::_('COM_EASYDISCUSS_NO_PERMISSION_TO_VIEW_POST')  , 'error' );
				$app->redirect( DiscussRouter::_('index.php?option=com_easydiscuss&view=index', false)) ;
			}
		}

		// Add pathway for category here.
		DiscussHelper::getHelper( 'Pathway' )->setCategoryPathway( $category );

		// Set breadcrumbs for this discussion.
		$this->setPathway( $this->escape( $post->title ) );

		// Mark as viewed for notifications.
		$this->logView();

		// Update hit count for this discussion.
		$post->hit();

		// Set page title.
		$doc->setTitle( $post->getTitle() );

		// Set canonical link to avoid URL duplication.
		$doc->addHeadLink( DiscussRouter::getPostRoute( $post->id ) , 'canonical' , 'rel' );

		// Add syntax highlighted css codes.
		if( $config->get( 'main_syntax_highlighter') )
		{
			$doc->addStylesheet( DISCUSS_MEDIA_URI . '/styles/syntaxhighlighter/' . $config->get( 'sh_theme' ) . '.css');
		}

		// Before sending the title and content to be parsed, we need to store this temporarily in case it needs to be accessed.
		$post->title_clear 	= $post->title;
		$post->content_raw	= $post->content;

		// Filter badwords
		$post->title		= DiscussHelper::wordFilter( $post->title );
		$post->content		= DiscussHelper::wordFilter( $post->content );

		// Only run through bbcode if needed.
		$post->content 		= DiscussHelper::parseContent( $post->content );


		// Get the tags for this discussion
		$postsTagsModel	= $this->getModel('PostsTags');
		$tags 			= $postsTagsModel->getPostTags( $id );

		// Get adsense codes here.
		$adsense 		= DiscussHelper::getAdsense();

		// Clear up any notifications that are visible for the user.
		$notifications	= $this->getModel( 'Notification' );
		$notifications->markRead(	$my->id ,
									$post->id ,
									array(
											DISCUSS_NOTIFICATIONS_REPLY,
											DISCUSS_NOTIFICATIONS_RESOLVED,
											DISCUSS_NOTIFICATIONS_ACCEPTED,
											DISCUSS_NOTIFICATIONS_FEATURED,
											DISCUSS_NOTIFICATIONS_COMMENT,
											DISCUSS_NOTIFICATIONS_MENTIONED,
											DISCUSS_NOTIFICATIONS_LIKES_DISCUSSION,
											DISCUSS_NOTIFICATIONS_LIKES_REPLIES
										)
								);

		$postsModel 	= DiscussHelper::getModel( 'Posts' );

		// Get the answer for this discussion.
		$answer		= $postsModel->getAcceptedReply( $post->id );


		// Get a list of replies for this discussion
		$replies 		= array();
		$hasMoreReplies	= false;
		$totalReplies 	= 0;
		$readMoreURI	= '';

		if( $category->canViewReplies() )
		{
			$repliesLimit	= $config->get('layout_replies_list_limit');
			$totalReplies	= $postsModel->getTotalReplies( $post->id );

			$hasMoreReplies	= false;

			$limitstart		= null;
			$limit			= null;

			if( $repliesLimit && !JRequest::getBool('viewallreplies') )
			{
				$limit		= $repliesLimit;

				$hasMoreReplies = ( $totalReplies - $repliesLimit ) > 0;
			}

			$replies 		= $postsModel->getReplies( $post->id, $sort, $limitstart, $limit );

			if( count( $replies ) > 0 )
			{
				$repliesIds = array();
				$authorIds  = array();

				foreach( $replies as $reply )
				{
					$repliesIds[]	= $reply->id;
					$authorIds[]    = $reply->user_id;
				}

				if( $answer )
				{
					$repliesIds[]   = $answer[0]->id;
					$authorIds[]    = $answer[0]->user_id;
				}

				$post->loadBatch( $repliesIds );
				$post->setAttachmentsData( 'replies', $repliesIds);

				// here we include the discussion id into the array as well.
				$repliesIds[]   = $post->id;
				$authorIds[]    = $post->user_id;

				$post->setLikeAuthorsBatch( $repliesIds );
				DiscussHelper::getHelper( 'Post' )->setIsLikedBatch( $repliesIds );

				$post->setPollQuestionsBatch( $repliesIds );
				$post->setPollsBatch( $repliesIds );

				$post->setLikedByBatch( $repliesIds, $my->id );
				$post->setVoterBatch( $repliesIds );
				$post->setHasVotedBatch( $repliesIds );

				$post->setTotalCommentsBatch( $repliesIds );
				$commentLimit	= $config->get( 'main_comment_pagination' ) ? $config->get( 'main_comment_pagination_count' ) : null;
				$post->setCommentsBatch( $repliesIds, $commentLimit );

				// Reduce SQL queries by pre-loading all author object.
				$authorIds  = array_unique($authorIds);
				$profile	= DiscussHelper::getTable( 'Profile' );
				$profile->init( $authorIds );
			}

			$readMoreURI	= JURI::getInstance()->toString();
			$delimiteter	= JString::strpos($readMoreURI, '&') ? '&' : '?';
			$readMoreURI	= $hasMoreReplies ? $readMoreURI . $delimiteter . 'viewallreplies=1' : $readMoreURI;

			// Format the reply items.
			$replies		= DiscussHelper::formatReplies( $replies , $category );
		}

		// Format the answer object.
		if( $answer )
		{
			$answer 	= DiscussHelper::formatReplies( $answer , $category );
			$answer 	= $answer[0];
		}

		// Get comments for the post
		$commentLimit			= $config->get( 'main_comment_pagination' ) ? $config->get( 'main_comment_pagination_count' ) : null;
		$comments				= $post->getComments( $commentLimit );
		$post->comments 		= DiscussHelper::formatComments( $comments );

		// get reply comments count
		$post->commentsCount	= $post->getTotalComments();

		// Get the post access object here.
		$access	= $post->getAccess( $category );
		$post->access = $access;

		// Add custom values.
		$post->user 	= $post->getOwner();

		// update user's post read flag
		if( $my->id != 0 )
		{
			$profile	= DiscussHelper::getTable( 'Profile' );
			$profile->load( $my->id );
			$profile->read( $post->id );
		}

		// Get Likes model here.
		$post->likesAuthor	= DiscussHelper::getHelper( 'Likes' )->getLikesHTML( $post->id , $my->id , 'post' );

		$post->isVoted		= DiscussHelper::getHelper( 'Post' )->isVoted( $post->id );

		// Test if trigger is necessary here.
		if ( $config->get( 'main_content_trigger_posts' ) )
		{
			$post->event = new stdClass();

			// Triger onContentPrepare here. Since it doesn't have any return value, just ignore this.
			DiscussHelper::triggerPlugins( 'content' , 'onContentPrepare' , $post );

			$post->event->afterDisplayTtle		= DiscussHelper::triggerPlugins( 'content' , 'onContentAfterTitle' , $post , true );
			$post->event->beforeDisplayContent	= DiscussHelper::triggerPlugins( 'content' , 'onContentBeforeDisplay' , $post , true );
			$post->event->afterDisplayContent 	= DiscussHelper::triggerPlugins( 'content' , 'onContentAfterDisplay' , $post , true );
		}


		$theme 	= new DiscussThemes();

		$isQuestion = $post->isQuestion();
		$isReply	= $post->isReply();

		$moderators = array();
		$composer = new DiscussComposer("replying", $post);

		// Set the discussion object.
		$theme->set( 'post'					, $post );
		$theme->set( 'composer'             , $composer );

		// Set the replies for this discussion.
		$theme->set( 'replies'				, $replies );

		// This is the DiscussPost object for the accepted answer in this discussion.
		$theme->set( 'answer'				, $answer );
		$theme->set( 'sort'					, $sort );
		$theme->set( 'adsense'				, $adsense );
		$theme->set( 'tags'					, $tags );
		$theme->set( 'totalReplies'			, $totalReplies );
		$theme->set( 'hasMoreReplies'		, $hasMoreReplies );
		$theme->set( 'access'				, $access );
		$theme->set( 'category'				, $category );
		$theme->set( 'isQuestion'			, $isQuestion );
		$theme->set( 'isReply'				, $isReply );
		$theme->set( 'moderators'			, $moderators );
		$theme->set( 'readMoreURI'			, $readMoreURI );

		echo $theme->fetch( 'post.php' );

	}
}
