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

$readCss	= '';
$isRead		= false;
if( $system->profile->id != 0)
{
	$readCss	= 	( $system->profile->isRead( $post->id ) || $post->legacy ) ? ' is-read' : ' is-unread';
	$isRead		=  ( $system->profile->isRead( $post->id ) || $post->legacy ) ? false : true;
}

$isRecent	= ( $post->isnew ) ? ' is-recent' : '';
?>
<li class="postItem">
	<div class="discuss-item<?php echo $post->islock ? ' is-locked' : '';?><?php echo $post->isresolve ? ' is-resolved' : '';?><?php echo $post->isFeatured ? ' is-featured' : '';?> <?php echo $readCss . $isRecent; ?>">

		<div class="discuss-status">
			<i class="icon-ed-featured" rel="ed-tooltip" data-placement="top" data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_FEATURED' , true );?>"></i>
			<i class="icon-ed-resolved" rel="ed-tooltip" data-placement="top" data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_RESOLVED' , true );?>"></i>

		</div>

		<div class="discuss-item-left ">

			<h2 class="discuss-post-title" itemprop="name">
				<?php if( $isRead ) { ?>
				<span class="label label-unread"><?php echo JText::_( 'COM_EASYDISCUSS_NEW' );?></span>
				<?php } ?>
				<i class="icon-ed-locked" rel="ed-tooltip" data-placement="top" data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_LOCKED' , true );?>" ></i>
				<a class="break-word" href="<?php echo DiscussRouter::getPostRoute( $post->id );?>"><?php echo $post->title; ?></a>

			</h2>


			<div class="discuss-user discuss-user discuss-user-role-<?php echo $post->user->getRoleId(); ?>">
				<a href="<?php echo $post->user->getLink();?>" class="" title="<?php echo $this->escape( $post->user->getName() );?>">
					<?php if ($system->config->get( 'layout_avatar' ) && $system->config->get( 'layout_avatar_in_post' )) { ?>
					<div class="discuss-avatar avatar-small <?php echo $post->user->getRoleLabelClassname(); ?>">
						<img src="<?php echo $post->user->getAvatar();?>" alt="<?php echo $this->escape( $post->user->getName() );?>" />

						<?php if($system->config->get( 'layout_profile_roles' ) && $post->user->getRole() ) { ?>
						<div class="discuss-role-title"><?php //echo $this->escape($post->user->getRole()); ?></div>
						<?php } ?>

					</div>
					<?php } ?>
					<div class="discuss-user-name mv-5">
						<?php echo $post->user->getName();?>
					</div>
				</a>
				<!-- User ranks -->
				<span class="discuss-user-rank">
					<?php //echo DiscussHelper::getUserRanks( $post->user->id ); ?>
				</span>

				<!-- User graph -->
				<!-- <div class="discuss-user-graph">
					<div class="rank-bar mini" title="<?php //echo $this->escape( DiscussHelper::getUserRanks( $post->user->id ) ); ?>">
						<div class="rank-progress" style="width: <?php //echo DiscussHelper::getUserRankScore( $post->user->id ); ?>%"></div>
					</div>
				</div> -->

				<?php //echo $this->loadTemplate( 'post.conversation.php' , array( 'userId' => $post->user->id ) ); ?>

			</div><!-- discuss-user -->




		</div>


		<div class="discuss-item-right">
			<div class="discuss-story">

				<!-- Discussion Title -->
				<div class="discuss-story-hd">
					<div class="ph-10">

					</div>
				</div>

				<div class="discuss-statistic hide">
					<div class="status">
						<div class="item-count"><?php echo $replies = !empty( $post->reply ) ? $post->totalreplies : 0; ?></div>

						<div><?php echo $this->getNouns('COM_EASYDISCUSS_REPLIES', $replies); ?></div>
					</div>
					<div class="views">
						 <div class="item-count"><?php echo $post->hits; ?></div>
						 <div><?php echo $this->getNouns( 'COM_EASYDISCUSS_VIEWS' , $post->hits );?></div>
					</div>
					<div class="votes">
						<div class="item-count"> <?php echo $post->sum_totalvote; ?></div>
						<div><?php echo $this->getNouns( 'COM_EASYDISCUSS_VOTES_STRING' , $post->sum_totalvote );?></div>
					</div>
				</div>


				<!-- Introtext -->
				<div class="discuss-story-bd">
					<div class="ph-10">

						<div class="discuss-date fs-11">
							<i class="icon-ed-time"></i> <?php echo $this->formatDate( $system->config->get('layout_dateformat', '%A, %B %d %Y, %I:%M %p') , $post->created);?>
							<time datetime="<?php echo $this->formatDate( '%Y-%m-%d' , $post->created ); ?>"></time>
						</div>

						<?php if($system->config->get( 'layout_enableintrotext' ) ){ ?>
						<div class="discuss-intro-text">
							<?php echo $post->introtext; ?>
						</div>
						<?php } ?>

						<?php if( $system->config->get( 'main_tags' ) && $post->tags ){ ?>
						<div class="discuss-tags">
							<?php foreach( $post->tags as $tag ){ ?>
								<a class="label" href="<?php echo DiscussRouter::getTagRoute( $tag->id ); ?>"><i class="icon-tag"></i><?php echo $tag->title; ?></a>
							<?php } ?>
						</div>
						<?php } ?>

					</div>
				</div>


				<div class="discuss-story-ft">
					<div class="discuss-action-options">



						<div class="discuss-statistic pull-left">
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
								<!-- <span class="with-polls"><i class="icon-tasks"></i> <?php //echo JText::_( 'COM_EASYDISCUSS_WITH_POLLS' );?> </span> -->
							<?php } ?>

							<?php if( $post->attachments ){ ?>
								<!-- <span class="with-attachments"><i class="icon-file"></i> <?php //echo JText::_( 'COM_EASYDISCUSS_WITH_ATTACHMENTS' );?> </span> -->
							<?php } ?>

						</div><!-- discuss-statistic -->

						<div class="pull-right">
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
						</div><!-- pull-right -->

					</div><!-- discuss-action-options -->
				</div>

			</div>
		</div>

	</div><!-- item -->
</li>
