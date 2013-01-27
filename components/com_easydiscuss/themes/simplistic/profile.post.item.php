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
$readCss = '';
if( $system->profile->id != 0)
{
	$readCss = 	( $system->profile->isRead( $post->id ) || $post->legacy ) ? ' is-read' : ' is-unread';
}
$isRecent = ( $post->isnew ) ? ' is-recent' : '';
?>
<div class="discuss-item<?php echo $post->islock ? ' is-locked' : '';?><?php echo $post->isresolve ? ' is-resolved' : '';?><?php echo $post->isFeatured ? ' is-featured' : '';?> <?php echo $readCss . $isRecent; ?><?php echo isset( $favourites ) ? ' is-favourited' : '';?>">

	<div class="discuss-status">
		<i class="icon-ed-featured" rel="ed-tooltip" data-placement="top" data-original-title="Featured"></i>
		<i class="icon-ed-resolved" rel="ed-tooltip" data-placement="top" data-original-title="Resolved"></i>
	</div>

	<div class="discuss-item-right">
		<div class="discuss-story">

			<!-- Discussion Title -->
			<div class="discuss-story-hd">
				<div class="ph-10">
					<h2 class="discuss-post-title" itemprop="name">
						<i class="icon-ed-locked" rel="ed-tooltip" data-placement="top" data-original-title="Locked"></i>
						<a class="break-word" href="<?php echo DiscussRouter::getPostRoute( $post->id );?>"><?php echo $post->title; ?></a>
						<small class="label"><?php echo JText::_( 'COM_EASYDISCUSS_UNREAD' );?></small>
					</h2>

					<div class="small">
						<?php echo JText::_('COM_EASYDISCUSS_POSTED_IN'); ?><a href="<?php echo DiscussRouter::getCategoryRoute( $post->category_id ); ?>"> <?php echo $post->category; ?></a>
						<?php echo $post->duration; ?>
						<time datetime="<?php echo $post->replied; ?>"></time>
					</div>
				</div>
			</div>

			<div class="discuss-story-ft">
				<div class="discuss-action-options">

					<div class="discuss-statistic discuss-statistic-mini pull-left">

						<a href="<?php echo DiscussRouter::getPostRoute( $post->id );?>" rel="ed-tooltip" data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_STAT_TOTAL_REPLIES' , true );?>">
							<i class="icon-comments"></i> <?php echo $post->totalreplies;?>
						</a>
						<a href="<?php echo DiscussRouter::getPostRoute( $post->id );?>" rel="ed-tooltip" data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_STAT_TOTAL_HITS' , true );?>">
							<i class="icon-bar-chart"></i> <?php echo $post->hits; ?>
						</a>
						<a href="<?php echo DiscussRouter::getPostRoute( $post->id );?>" rel="ed-tooltip" data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_STAT_TOTAL_FAVOURITES' , true ); ?>">
							<i class="icon-heart"></i> <?php echo $post->totalFavourites ?>
						</a>
						<a href="<?php echo DiscussRouter::getPostRoute( $post->id );?>" rel="ed-tooltip" data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_STAT_TOTAL_VOTES' , true ); ?>">
							<i class="icon-thumbs-up"></i> <?php echo $post->sum_totalvote; ?>
						</a>

						<?php if( $post->polls ){ ?>
							<span class="with-polls"><?php echo JText::_( 'COM_EASYDISCUSS_WITH_POLLS' );?> <i class="icon-tasks"></i></span>
						<?php } ?>

						<?php if( $post->attachments ){ ?>
							<span class="with-attachments"><?php echo JText::_( 'COM_EASYDISCUSS_WITH_ATTACHMENTS' );?></span>
						<?php } ?>

					</div>
					<!-- discuss-statistic -->

					<div class="pull-right">

						<div class="discuss-status pull-left">
						<?php //if( $post->isresolve ) { ?>

						<?php //} else if( ! $post->answered ) { ?>
							<i class="icon-ed-unanswered" rel="ed-tooltip" data-placement="top" data-original-title="Unanswered"></i>
						<?php //} else { ?>
							<i class="icon-ed-inprogress" rel="ed-tooltip" data-placement="top" data-original-title="In Progress"></i>
						<?php //} ?>
						</div>
						<!-- /.status -->

						<div class="discuss-last-replied pull-left">
							<?php if( isset( $post->reply ) ){ ?>
								<span class="pull-left">
									<?php echo JText::_('COM_EASYDISCUSS_LAST_REPLIED_BY');?>:
								</span>

								<?php if( $post->reply->id ){ ?>
								<a href="<?php echo DiscussRouter::getUserRoute( $post->reply->id );?>" class="pull-left ml-5">
									<?php if( $system->config->get( 'layout_avatar' ) ) { ?>
									<img src="<?php echo $post->reply->getAvatar();?>" alt="<?php echo $this->escape( $post->reply->getName() );?>" />
									<?php } ?>
									<?php echo $post->reply->getName();?>
								</a>
								<?php } else { ?>
									<?php echo $post->reply->poster_name; ?>
								<?php } ?>
							<?php } ?>
						</div>

					</div>
					<!-- pull-right -->

				</div>
				<!-- discuss-action-options -->
			</div>

		</div>
	</div>

</div>
