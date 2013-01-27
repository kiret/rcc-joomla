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
<?php if ($renderMode=="onload") { ?>
	<script type="text/javascript">
	EasyDiscuss.ready(function(){
		discuss.composer.init("<?php echo $composer->classname ?>");
	});
	</script>
<?php } ?>

<div class="discuss-composer <?php echo $composer->id; ?> discuss-composer-<?php echo $composer->operation ?>"
	 data-id="<?php echo $composer->id; ?>"
	 data-editor="<?php echo $system->config->get('layout_reply_editor') ?>"
	 >

	<div class="alert replyNotification" style="display: none;"></div>

	<div id="dc_post" class="discuss-story">
		<div class="discuss-content">
			<form id="dc_submit" name="dc_submit" class="form-horizontal" action="<?php echo DiscussRouter::_('index.php?option=com_easydiscuss&controller=posts&task=reply'); ?>" method="post">

				<div class="discuss-form">

					<div class="control-group control-group-guest">
					<?php if( !$system->my->id ){ ?>
						<input type="text" name="poster_name" class="input-xlarge" placeholder="<?php echo JText::_('COM_EASYDISCUSS_GUEST_NAME'); ?>">
						<input type="text" name="poster_email" class="input-xlarge" placeholder="<?php echo JText::_('COM_EASYDISCUSS_GUEST_EMAIL'); ?>">
					<?php } else { ?>
						<input type="hidden" name="poster_name" id="poster_name" value="" />
						<input type="hidden" name="poster_email" id="poster_email" value="" />
					<?php } ?>
					</div>

					<div class="row-fluid">
						<?php if( $system->config->get( 'layout_reply_editor') == 'bbcode' ) { ?>
						<textarea class="dc_reply_content full-width" name="dc_reply_content" class="full-width"><?php echo $composer->content; ?></textarea>
						<?php } else { ?>
						<?php echo $editor->display( 'dc_reply_content', '', '100%', '350', '10', '10' , array( 'pagebreak' , 'readmore' ) ); ?>
						<?php } ?>
					</div>

					<?php if( $system->config->get( 'main_location_reply' ) ){ ?>
					<div class="control-group">
						<?php echo $this->loadTemplate( 'form.location.php' ); ?>
					</div>
					<?php } ?>

					<?php echo $composer->getComposerFields(); ?>

					<?php if( $captcha = $this->getRecaptcha() ){ ?>
					<div class="control-group">
						<hr/>
						<div id="reply_new_antispam" class="respond-recaptcha mt-10"><?php echo $captcha; ?></div>
					</div>
					<?php } ?>

					<div class="form-actions">
						<div class="pull-right">

							<?php if ($composer->operation=="editing") { ?>
							<input type="button" name="cancel-reply" class="btn btn-medium cancel-reply" value="<?php echo JText::_('COM_EASYDISCUSS_CANCEL'); ?>" />
							<input type="button" name="save-reply" class="btn btn-primary btn-medium save-reply" value="<?php echo JText::_('COM_EASYDISCUSS_BUTTON_SAVE'); ?>" />
							<?php } else { ?>
							<input type="button" name="submit-reply" class="btn btn-primary btn-medium submit-reply" value="<?php echo JText::_('COM_EASYDISCUSS_BUTTON_SUBMIT_RESPONSE'); ?>" onclick="discuss.reply.submit('<?php echo $composer->id; ?>');return false;" />
							<?php } ?>
							<div class="pull-right" id="reply_loading"></div>
						</div>
					</div>

					<input type="hidden" id="title" name="title" value="Re: <?php echo DiscussStringHelper::escape($parent->title); ?>" />
					<input type="hidden" name="post_id" value="<?php echo $post->id; ?>" />
					<input type="hidden" name="parent_id" value="<?php echo $parent->id; ?>" />
					<input type="hidden" name="parent_catid" value="<?php echo $parent->category_id; ?>" />
					<input type="hidden" name="user_type" id="user_type" value="<?php echo $system->my->id == 0 ? 'guest' : ''; ?>" />
				</div>
			</form>
		</div>
	</div>
</div>
