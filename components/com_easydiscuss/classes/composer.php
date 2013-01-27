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

class DiscussComposer
{
	public $id;

	private $post;
	private $parent;
	private $isDiscussion;
	public $content = '';

	public $renderMode = 'onload'; // onload|explicit
	public $theme;

	public function __construct($operation, $post)
	{
		$this->id = 'composer-' . rand();
		$this->classname = '.' . $this->id;
		$this->operation = $operation;

		switch ($operation) {

			case "creating":
				$this->post = $post;
				$this->parent = $post;
				$this->isDiscussion = true;
				break;

			case "editing":
				$this->post = $post;
				$this->parent = DiscussHelper::getTable( 'Post' );
				$this->parent->load($post->parent_id);
				$this->content = $post->content;
				$this->isDiscussion = false;
				break;

			case "replying":
				$this->post = DiscussHelper::getTable( 'Post' );
				$this->parent = $post;
				$this->isDiscussion = false;
				break;
		}
	}

	public function getComposer()
	{
		$theme = new DiscussThemes();

		$config		= DiscussHelper::getConfig();

		if( $this->isDiscussion )
		{
			$editorType 	= $config->get( 'layout_editor' , 'bbcode' );
		}
		else
		{
			$editorType 	= $config->get( 'layout_reply_editor' , 'bbcode' );	
		}
		
		$editor 	= JFactory::getEditor( $editorType );

		$theme->set( 'editor'		, $editor );
		$theme->set('composer'		, $this);
		$theme->set('post'			, $this->post);
		$theme->set('parent'		, $this->parent);
		$theme->set('content'		, $this->content);
		$theme->set('isDiscussion'	, $this->isDiscussion);
		$theme->set('renderMode'	, $this->renderMode);

		return $theme->fetch('form.reply.php');
	}

	public function getComposerFields()
	{
		// select top 20 tags.
		$tagmodel	= DiscussHelper::getModel( 'Tags' );
		$tags		= $tagmodel->getTagCloud('','post_count','DESC');

		$theme = new DiscussThemes();

		$theme->set('tags'			, $tags);
		$theme->set('composer'		, $this);
		$theme->set('post'			, $this->post);
		$theme->set('parent'		, $this->parent);
		$theme->set('isDiscussion'	, $this->isDiscussion);
		$theme->set('renderMode'	, $this->renderMode);

		return $theme->fetch('form.tabs.php');
	}

	public function setIsDiscussion( $value )
	{
		$this->isDiscussion = (bool) $value;
	}
}
