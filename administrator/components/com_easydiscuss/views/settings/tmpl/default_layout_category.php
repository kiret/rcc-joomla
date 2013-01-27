<?php
/**
* @package		EasyDiscuss
* @copyright	Copyright (C) 2010 Stack Ideas Private Limited. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasyDiscuss is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Restricted access');
?>
<div class="row-fluid">
	<div class="span12 panel-title">
		<h2><?php echo JText::_( 'COM_EASYDISCUSS_SETTINGS_LAYOUT_CATEGORY_TITLE' );?></h2>
		<p style="margin: 0 0 15px;">
			<?php echo JText::_( 'COM_EASYDISCUSS_SETTINGS_LAYOUT_CATEGORY_DESC' );?>
		</p>
	</div>
</div>

<div class="row-fluid ">
	<div class="span6">
		<div class="widget accordion-group">
			<div class="whead accordion-heading">
				<a href="javascript:void(0);" data-foundry-toggle="collapse" data-target="#option01">
				<h6><?php echo JText::_( 'COM_EASYDISCUSS_CATEGORY' ); ?></h6>
				<i class="icon-chevron-down"></i>
				</a>
			</div>

			<div id="option01" class="accordion-body collapse in">
				<div class="wbody">
					<div class="si-form-row">
						<div class="span5 form-row-label">
							<label>
								<?php echo JText::_( 'COM_EASYDISCUSS_CATEGORY_SHOWTREE' ); ?>
							</label>
						</div>
						<div class="span7"
							rel="ed-popover"
							data-placement="left"
							data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_CATEGORY_SHOWTREE' ); ?>"
							data-content="<?php echo JText::_('COM_EASYDISCUSS_CATEGORY_SHOWTREE_DESC'); ?>"
						>
							<?php echo $this->renderCheckbox( 'layout_category_showtree' , $this->config->get( 'layout_category_showtree' ) );?>
						</div>
					</div>
					<div class="si-form-row">
						<div class="span5 form-row-label">
							<label>
								<?php echo JText::_( 'COM_EASYDISCUSS_ALWAYS_HIDE_CATEGORY_DESCRIPTION' ); ?>
							</label>
						</div>
						<div class="span7"
							rel="ed-popover"
							data-placement="left"
							data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_ALWAYS_HIDE_CATEGORY_DESCRIPTION' ); ?>"
							data-content="<?php echo JText::_('COM_EASYDISCUSS_ALWAYS_HIDE_CATEGORY_DESCRIPTION_DESC'); ?>"
						>
							<?php echo $this->renderCheckbox( 'layout_category_description_hidden' , $this->config->get( 'layout_category_description_hidden' ) );?>
						</div>
					</div>
					<div class="si-form-row">
						<div class="span5 form-row-label">
							<label>
								<?php echo JText::_( 'COM_EASYDISCUSS_CATEGORY_SORTING' ); ?>
							</label>
						</div>
						<div class="span7"
							rel="ed-popover"
							data-placement="left"
							data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_CATEGORY_SORTING' ); ?>"
							data-content="<?php echo JText::_('COM_EASYDISCUSS_CATEGORY_SORTING_DESC'); ?>"
						>
							<?php
								$sortingType = array();
								$sortingType[] = JHTML::_('select.option', 'alphabet', JText::_( 'Alphabetical' ) );
								$sortingType[] = JHTML::_('select.option', 'latest', JText::_( 'Latest' ) );
								$sortingType[] = JHTML::_('select.option', 'ordering', JText::_( 'Ordering' ) );
								$categorySortHTML = JHTML::_('select.genericlist', $sortingType, 'layout_sorting_category', 'class="full-width" size="1" ', 'value', 'text', $this->config->get('layout_sorting_category' , 'ordering' ) );
								echo $categorySortHTML;
							?>
						</div>
					</div>
					<div class="si-form-row">
						<div class="span5 form-row-label">
							<label>
								<?php echo JText::_( 'COM_EASYDISCUSS_CATEGORY_PATH' ); ?>
							</label>
						</div>
						<div class="span7"
							rel="ed-popover"
							data-placement="left"
							data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_CATEGORY_PATH' ); ?>"
							data-content="<?php echo JText::_('COM_EASYDISCUSS_CATEGORY_PATH_DESC'); ?>"
						>
							<input type="text" name="main_categoryavatarpath" class="full-width" value="<?php echo $this->config->get('main_categoryavatarpath', 'images/eblog_cavatar/' );?>" />
						</div>
					</div>
					<div class="si-form-row">
						<div class="span5 form-row-label">
							<label>
								<?php echo JText::_( 'COM_EASYDISCUSS_CATEGORY_SHOWMODERATORS' ); ?>
							</label>
						</div>
						<div class="span7"
							rel="ed-popover"
							data-placement="left"
							data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_CATEGORY_SHOWMODERATORS' ); ?>"
							data-content="<?php echo JText::_('COM_EASYDISCUSS_CATEGORY_SHOWMODERATORS_DESC'); ?>"
						>
							<?php echo $this->renderCheckbox( 'layout_show_moderators' , $this->config->get( 'layout_show_moderators' ) );?>
						</div>
					</div>
					<div class="si-form-row">
						<div class="span5 form-row-label">
							<label>
								<?php echo JText::_( 'COM_EASYDISCUSS_CATEGORY_SHOW_STATS' ); ?>
							</label>
						</div>
						<div class="span7"
							rel="ed-popover"
							data-placement="left"
							data-original-title="<?php echo JText::_( 'COM_EASYDISCUSS_CATEGORY_SHOW_STATS' ); ?>"
							data-content="<?php echo JText::_('COM_EASYDISCUSS_CATEGORY_SHOW_STATS_DESC'); ?>"
						>
							<?php echo $this->renderCheckbox( 'layout_category_stats' , $this->config->get( 'layout_category_stats' ) );?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="span6">

	</div>
</div>
