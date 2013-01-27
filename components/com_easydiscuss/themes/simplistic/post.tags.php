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
<?php if( $system->config->get( 'main_tags' ) && $tags ){ ?>
<div class="discuss-tags fs-11 mt-15">
	<?php if ( !empty( $tags ) ) { ?>
		<!-- <span class="tag-label"><strong><?php //echo JText::_('COM_EASYDISCUSS_TAGS'); ?>:</strong></span> -->
		<?php $tagCount = count( $tags); $x=1;?>
		<?php foreach( $tags as $tag ){ ?>
			<a href="<?php echo DiscussRouter::_('index.php?option=com_easydiscuss&view=tags&id=' . $tag->id); ?>" class="label">#<?php echo $tag->title; ?></a>
		<?php } ?>
	<?php } ?>
</div>
<?php } ?>
