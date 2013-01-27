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

<?php if( $access->canEdit() || $access->canFeature() || $access->canDelete() || $access->canResolve() || $access->canLock() ){ ?>
<div class="row-fluid discuss-admin-bar">

	<div class="pull-right mr-10">
		<?php if( $system->config->get( 'main_report' ) ){ ?>
		<a onclick="discuss.reports.add('<?php echo $post->id;?>');" href="javascript:void(0);" class="btn btn-danger btn-mini" rel="ed-tooltip" data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_REPORT_THIS' , true );?>">
			&nbsp;<i class="icon-warning-sign"></i>&nbsp;
		</a>
		<?php } ?>
	</div>

	<?php if( $access->canMove() || ($access->canFeature() && $post->isQuestion()) || ($access->canLock() && $post->isQuestion()) ){ ?>
	<div class="btn-group dropdown_ pull-right mr-5">

		<a class="btn btn-yellow btn-mini" data-foundry-toggle="dropdown">
			<i class="icon-cog"></i> <?php echo JText::_( 'COM_EASYDISCUSS_MODERATION_TOOLS' ); ?>
			<span class="caret"></span>
		</a>
		<ul class="dropdown-menu">
			<?php if( $access->canMove() ){ ?>
			<li>
				<a href="javascript:void(0);" onclick="discuss.post.move('<?php echo $post->id;?>')">
				<i class="icon-move"></i> <?php echo JText::_( 'COM_EASYDISCUSS_MOVE_POST' ); ?></a>
			</li>
			<li>
				<a href="javascript:void(0);" onclick="discuss.post.mergeForm('<?php echo $post->id;?>')">
				<i class="icon-retweet"></i> <?php echo JText::_( 'COM_EASYDISCUSS_MERGE_WITH' ); ?></a>
			</li>
			<?php } ?>

			<?php if( $access->canFeature() && $post->isQuestion() ){ ?>
			<li>
				<a class="admin-featured" href="javascript:void(0);" onclick="discuss.post.feature('<?php echo $post->id;?>' );" class="featurePost">
					<i class="icon-pushpin"></i> <?php echo JText::_('COM_EASYDISCUSS_ENTRY_FEATURE_THIS');?></a>
				<a class="admin-unfeatured" href="javascript:void(0);" onclick="discuss.post.unfeature('<?php echo $post->id;?>' );" class="unfeaturePost">
					<i class="icon-pushpin"></i> <?php echo JText::_('COM_EASYDISCUSS_ENTRY_UNFEATURE_THIS');?></a>
			</li>
			<?php } ?>

			<?php if( $access->canLock() && $post->isQuestion() ){ ?>
			<li>
				<a class="admin-unlock" href="javascript:void(0);" class="unlockPost" onclick="discuss.post.unlock('<?php echo $post->id; ?>');">
					<i class="icon-unlock"></i> <?php echo JText::_('COM_EASYDISCUSS_ENTRY_UNLOCK'); ?>
				</a>
				<a class="admin-lock" href="javascript:void(0);" class="lockPost" onclick="discuss.post.lock('<?php echo $post->id; ?>');">
					<i class="icon-lock"></i> <?php echo JText::_('COM_EASYDISCUSS_ENTRY_LOCK'); ?>
				</a>
			</li>
			<?php } ?>
		</ul>
	</div>
	<?php } ?>

	<div class="pull-right mr-5">

		<a href="javascript:void(0);" class="btn btn-mini quotePost" rel="ed-tooltip" data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_QUOTE' , true );?>">
			&nbsp;<i class="icon-share-alt"></i>&nbsp;
			<input type="hidden" name="raw_message" value="<?php echo $this->escape( $post->content_raw );?>" />
		</a>

		<?php if( $post->isQuestion() ){ ?>
		<a href="<?php echo DiscussRouter::getPrintRoute( $post->id );?>; ?>"
			onclick="window.open(this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;"
			class="btn btn-mini" rel="ed-tooltip" data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_PRINT' , true );?>">
			&nbsp;<i class="icon-print"></i>&nbsp;
		</a>
		<?php } ?>

		<?php if( $post->isQuestion() ){ ?>
			<a href="<?php echo DiscussRouter::getEditRoute( $post->id );?>" class="btn btn-mini">
		<?php } else { ?>
			<a href="javascript:void(0);" class="editReplyButton btn btn-mini">
		<?php } ?>
		<i class="icon-pencil"></i> <?php echo JText::_('COM_EASYDISCUSS_ENTRY_EDIT'); ?></a>

		<?php if( $access->canDelete() ){ ?>
			<?php if( $post->isQuestion() ){ ?>
				<a href="javascript:void(0);" onclick="discuss.post.del('<?php echo $post->id; ?>', 'post' , '<?php echo DiscussRouter::_( 'index.php?option=com_easydiscuss&view=index' );?>' );" class="btn btn-mini">
			<?php } else { ?>
				<a href="javascript:void(0);" onclick="discuss.post.del('<?php echo $post->id; ?>', 'reply' , '<?php echo DiscussRouter::getPostRoute( $post->id );?>' );" class="btn btn-mini">
			<?php }?>
			<i class="icon-remove"></i> <?php echo JText::_('COM_EASYDISCUSS_ENTRY_DELETE'); ?></a>
		<?php } ?>

		<?php if( $access->canResolve() && $post->isQuestion() ){ ?>
			<a class="admin-unresolve btn btn-mini" href="javascript:void(0);" onclick="discuss.post.unresolve('<?php echo $post->id; ?>');">
				<i class="icon-remove-sign"></i> <?php echo JText::_('COM_EASYDISCUSS_ENTRY_MARK_UNRESOLVED'); ?></a>

			<a class="admin-resolve btn btn-mini" href="javascript:void(0);" onclick="discuss.post.resolve('<?php echo $post->id; ?>');">
				<i class="icon-ok-sign"></i> <?php echo JText::_('COM_EASYDISCUSS_ENTRY_MARK_RESOLVED'); ?></a>
		<?php } ?>


	</div>


</div>
<?php } ?>
