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
<div class="row-fluid">

	<h2 class="discuss-component-title pull-left"><?php echo JText::_('COM_EASYDISCUSS_MEMBERS'); ?></h2>

	<!-- div class="pull-right">
		<div class="discuss-filter mr-10">
			<ul class="nav nav-pills">
				<li class="user-l-name<?php echo ($sort == 'name') ? ' active' : ''; ?>"><a href="<?php echo DiscussRouter::_('index.php?option=com_easydiscuss&view=users&sort=name'); ?>"><?php echo JText::_('COM_EASYDISCUSS_SORT_NAME_ASC'); ?></a></li>
				<li class="user-l-visit<?php echo ($sort == 'lastvisit') ? ' active' : ''; ?>"><a href="<?php echo DiscussRouter::_('index.php?option=com_easydiscuss&view=users&sort=lastvisit'); ?>"><?php echo JText::_('COM_EASYDISCUSS_SORT_VISITDATE_DESC'); ?></a></li>
				<li class="user-l-joined<?php echo ($sort == 'latest') ? ' active' : ''; ?>"><a href="<?php echo DiscussRouter::_('index.php?option=com_easydiscuss&view=users&sort=latest'); ?>"><?php echo JText::_('COM_EASYDISCUSS_SORT_REGISTERDATE_DESC'); ?></a></li>
			</ul>
		</div>
	</div -->

</div>
<hr />
<div class="row-fluid">
	<ul class="unstyled discuss-list discuss-list-grid discuss-users-list">
		<?php foreach( $users as $user ){ ?>
			<?php echo $this->loadTemplate( 'users.item.php' , array( 'user' => $user ) ); ?>
		<?php } ?>
	</ul>
</div>

<?php echo $pagination->getPagesLinks();?>

