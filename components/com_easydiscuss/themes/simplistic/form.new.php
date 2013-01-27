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
?>
<script type="text/javascript">
EasyDiscuss
.require()
.script('posts')
.done(function($){
	$('.discuss-form').implement( EasyDiscuss.Controller.Post.Ask );
});
</script>

<script type="text/javascript">
EasyDiscuss.ready(function(){
	discuss.composer.init("<?php echo $composer->classname; ?>");
});
</script>

<script type="text/javascript">
EasyDiscuss.ready(function($){

	discuss.getContent = function(){
		<?php if( $system->config->get( 'layout_editor') == 'bbcode' ) { ?>
			return $( '#dc_reply_content' ).val();
		<?php } else { ?>
		<?php echo 'return ' . JFactory::getEditor( $system->config->get( 'layout_editor' ) )->getContent( 'dc_reply_content' ); ?>
		<?php } ?>
	};

	<?php if( $system->config->get( 'main_similartopic' ) ) { ?>

	var textField = $('input#ez-title');
	var queryJob = null;
	var menuLock = false;
	textField.keydown(function( e )
	{
		var keynum; // set the variable that will hold the number of the key that has been pressed.

		//now, set keynum = the keystroke that we determined just happened...
		if(window.event)// (IE)
		{
			keynum = e.keyCode;
		}
		else if(e.which) // (other browsers)
		{
			keynum = e.which;
		}
		else
		{ // something funky is happening and no keycode can be determined...
			keynum = 0;
		}

		if( keynum == 9 || keynum == 27)
		{
			$('#dc_similar-questions').hide();
			return;
		}

		clearTimeout(queryJob);

		// Start this job after 1 second
		queryJob = setTimeout(function()
		{

			if( textField.val().length <= 3 )
				return;

			//show loading icon
			$('#dc-search-loader').show();

			var params	= { query: textField.val() };

			params[ $( '.easydiscuss-token' ).val() ]	= 1;

			EasyDiscuss.ajax('site.views.post.similarQuestion', params ,
			function(data){
				//hide loading icon
				$('#dc-search-loader').hide();
				if( data != '' )
				{
					// Do whatever you like with the data returned from server.
					$('#dc_similar-questions').html(data);
					$('#dc_similar-questions').show();

					$('#similar-question-close').click( function()
					{
						$('#dc_similar-questions').hide();
						return;
					});
				}
			});
		}, 1500);
	});

	$('#dc_similar-questions').bind('mousemove click', function(){
		textField.focus();
		menuLock = true;
	})
	.mouseout(function(){
		menuLock = false;
	});

	textField.blur( function()
	{
		if (menuLock) return;

		$('#dc_similar-questions').hide();
		return;
	});

	<?php } ?>

	// Try to test if there is a 'default' class in all of the tabs
	if( $( 'ul.form-tab' ).children().find( '.default' ).html() != null )
	{
		var id 	= $( 'ul.form-tab' ).children().find( '.default' ).attr( 'id' );
		var tab = id.substr( id.indexOf( '-' ) + 1 , id.length );

		$( 'ul.form-tab' ).children().find( '.default' ).parent().addClass( 'active' );

		$( 'div.form-tab-contents' ).children().hide();
		$( '.tab-' + tab ).show();
	}
	else
	{
		// First tab always gets the active class.
		$( 'ul.form-tab' ).children( ':first' ).addClass( 'active' );
		$( 'div.form-tab-contents' ).children().hide();
		$( 'div.form-tab-contents' ).children( ':first' ).show();
	}

// 	$( '.submitDiscussion' ).bind( 'click' , function(){
//
// // 		var selectedCategory = $( '.discuss-form *[name=category_id]' ).val();
// //
// // 		if( selectedCategory == 0 || selectedCategory.length == 0 )
// // 		{
// // 			$( '.categorySelection' ).addClass( 'error' );
// // 			disjax.loadingDialog();
// // 			disjax.load( 'post' , 'selectCategory' );
// // 			return false;
// // 		}
// //
// // 		// Disable the submit button if it's already pressed to avoid duplicate clicks.
// // 		$(this).attr( 'disabled' , 'disabled' );
// //
// // 		// Submit the form now.
// // 		$( '#dc_submit' ).submit();
//
// 		var text = discuss.getContent();
//
// 		console.log( text );
//
// 		// $( '#hidden-content-placeholder').val( discuss.getContent() );
// 		return false;
// 	});
});
</script>

<!-- do not remove this div -->
<div class="ask-notification"></div>

<form id="dc_submit" name="dc_submit" action="<?php echo DiscussRouter::_('index.php?option=com_easydiscuss&controller=posts&task=submit'); ?>" method="post" enctype="multipart/form-data" class="form-horizontal">
<div class="discuss-form <?php echo $composer->id; ?> discuss-composer-<?php echo $composer->operation ?>"
	 data-id="<?php echo $composer->id; ?>"
	 data-editor="<?php echo $system->config->get('layout_editor') ?>">

	<?php if( $isEditMode ){ ?>
	<legend><?php echo JText::_( 'COM_EASYDISCUSS_ENTRY_EDITING_TITLE');?></legend>
	<input type="hidden" name="id" id="id" value="<?php echo $post->id; ?>" />
	<?php } else { ?>
	<legend><?php echo JText::_( 'COM_EASYDISCUSS_TOOLBAR_NEW_DISCUSSION');?></legend>
	<?php } ?>

	<div id="dc_post_notification"><div class="msg_in"></div></div>

	<div class="row-fluid control-group discuss-category-selection categorySelection">
		<div class="form-inline">
			<?php if( $config->get( 'layout_category_selection' ) == 'multitier' ) { ?>
				<?php echo $this->loadTemplate( 'category.select.multitier.php' ); ?>
			<?php } else { ?>
				<?php echo $nestedCategories; ?>
			<?php } ?>
		</div>
	</div>

	<hr />
	<div class="row-fluid">
		<div class="control-group">
			<input type="text" id="ez-title" name="title" placeholder="<?php echo JText::_('COM_EASYDISCUSS_POST_TITLE_EXAMPLE' , true ); ?>" class="full-width input input-title" autocomplete="off" value="<?php echo $this->escape( $post->title );?>" />
			<div id="dc-search-loader" style="display:none;">
				<div class="discuss-loader"></div>
			</div>
			<div id="dc_similar-questions" style="display:none"></div>
		</div>
	</div>

	<?php if( $system->config->get( 'layout_editor') == 'bbcode' ) { ?>
		<textarea class="dc_reply_content full-width" name="dc_reply_content" id="dc_reply_content"><?php echo $this->escape( $post->content ); ?></textarea>
	<?php } else { ?>
		<?php echo $editor->display( 'dc_reply_content', $this->escape( $post->content ), '100%', '350', '10', '10' , array( 'pagebreak' , 'readmore' ) ); ?>
	<?php } ?>

	<div class="control-group">
		<?php echo $this->loadTemplate( 'form.location.php' ); ?>
	</div>

	<?php echo $composer->getComposerFields(); ?>

	<?php if( !$system->my->id && $acl->allowed('add_question', 0)) { ?>
	<hr />

	<div class="control-group">
		<div class="row-fluid">
			<div class="span5">
				<label for="poster_name" class="fs-12 mr-10"><?php echo JText::_('COM_EASYDISCUSS_YOUR_NAME'); ?> :</label>
				<input class="input width-200" type="text" id="poster_name" name="poster_name" value="<?php echo empty($post->poster_name) ? '' : $post->poster_name; ?>"/>
			</div>
			<div class="span7">
				<label for="poster_email" class="fs-12 mr-10"><?php echo JText::_('COM_EASYDISCUSS_YOUR_EMAIL'); ?> :</label>
				<input class="input width-200" type="text" id="poster_email" name="poster_email" value="<?php echo empty($post->poster_email) ? '' : $post->poster_email; ?>"/>
			</div>
		</div>
		<div class="form-inline">

		</div>
	</div>
	<div class="control-group">
		<div class="form-inline">

		</div>
	</div>

	<?php } ?>

	<?php if( $recaptcha = $this->getRecaptcha() ){ ?>
	<hr />
	<div class="control-group">
		<div id="post_new_antispam"><?php echo $recaptcha; ?></div>
	</div>
	<?php } ?>

	<div class="modal-footer">
		<div class="row-fluid">
			<div class="pull-left">
				<a href="<?php echo DiscussRouter::_( 'index.php?option=com_easydiscuss' );?>" class="btn btn-medium btn-danger"><?php echo JText::_('COM_EASYDISCUSS_BUTTON_CANCEL'); ?></a>
			</div>

			<div class="pull-right">
				<input type="button" class="btn btn-medium btn-primary submitDiscussion" value="<?php echo JText::_('COM_EASYDISCUSS_BUTTON_SUBMIT' , true ); ?>" />
			</div>
		</div>
	</div>

	<div class="clearfix"></div>
</div>

<?php echo JHTML::_( 'form.token' ); ?>
</form>
