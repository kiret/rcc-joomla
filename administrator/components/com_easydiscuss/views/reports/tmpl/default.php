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
function reportAction( id )
{
	var actionType  = discussQuery('#report-action-' + id).val();

	switch (actionType)
	{
		case "E" :
			if(discussQuery('#email-text-' + id).val().length <= 0)
			{
				alert( '<?php echo JText::_( 'COM_EASYDISCUSS_PLEASE_ENTER_CONTENTS' );?>' );
				return false;
			}

			var inputs  = [];

			//post_id
			inputs.push( 'post_id=' + escape( id ) );

			//content
			val = discussQuery('#email-text-' + id).val().replace(/"/g, "&quot;");
			val = encodeURIComponent(val);
			inputs.push( 'content=' + escape( val ) );

			disjax.load('Reports', 'ajaxSubmitEmail', inputs);
			break;

		case "D" :

			if( confirm( '<?php echo $this->escape( JText::_( 'COM_EASYDISCUSS_CONFIRM_DELETE_POST') );?>' ) )
			{
				discussQuery('#post_id').val(id);
				discussQuery('#task').val('deletePost');
				discussQuery('#adminForm').submit();
			}

			break;

		case "C" :
			discussQuery('#post_id').val(id);
			discussQuery('#task').val('removeReports');
			discussQuery('#adminForm').submit();
			break;

		case "P" :
			discussQuery('#post_id').val(id);
			discussQuery('#post_val').val('1');
			discussQuery('#task').val('togglePublish');
			discussQuery('#adminForm').submit();
			break;

		case "U" :
			discussQuery('#post_id').val(id);
			discussQuery('#post_val').val('0');
			discussQuery('#task').val('togglePublish');
			discussQuery('#adminForm').submit();
			break;

		default :
			break;
	}
}

EasyDiscuss(function($){
	$.Joomla( 'submitbutton' , function(action){
		$.Joomla( 'submitform' , [action] );
	});
});
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span12 panel-title">
			<h2><?php echo JText::_( 'COM_EASYDISCUSS_REPORTS_TITLE' );?></h2>
			<p style="margin: 0 0 15px;">
				<?php echo JText::_( 'COM_EASYDISCUSS_REPORTS_DESC' );?>
			</p>
		</div>
	</div>

	<div class="row-fluid filter-bar">
		<div class="span12">
			<div class="pull-left form-inline">
				<input type="text" name="search" id="search" value="<?php echo $this->escape( $this->search ); ?>" class="input-medium" onchange="document.adminForm.submit();" placeholder="<?php echo JText::_( 'COM_EASYDISCUSS_SEARCH' , true );?>"/>
				<button class="btn btn-success" type="submit" onclick="this.form.submit();"><?php echo JText::_( 'COM_EASYDISCUSS_SEARCH' ); ?></button>
				<button class="btn" type="submit" onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'COM_EASYDISCUSS_RESET' ); ?></button>
			</div>

			<div class="pull-right">
				<?php echo JText::_( 'COM_EASYDISCUSS_FILTER' ); ?>: <?php echo $this->state; ?>
			</div>
		</div>
	</div>

		<table class="table table-striped table-discuss">
			<thead>
				<tr>
					<th width="5">
						<?php echo JText::_( 'Num' ); ?>
					</th>
					<th width="5">
						<input type="checkbox" name="toggle" class="discussCheckAll" />
					</th>
					<th class="title" nowrap="nowrap" style="text-align:left;"><?php echo JText::_( 'COM_EASYDISCUSS_REPORTED_REASON' ); ?></th>
					<th width="15%" nowrap="nowrap"><?php echo JText::_( 'COM_EASYDISCUSS_REPORTED_BY' ); ?></th>
					<th width="3%" nowrap="nowrap"><?php echo JText::_( 'COM_EASYDISCUSS_NUM_REPORT' );?></th>
					<th width="10%" nowrap="nowrap"><?php echo JText::_( 'Last report date' ); ?></th>
					<th width="1%" nowrap="nowrap"><?php echo JText::_( 'Published' ); ?></th>
					<th width="20%" nowrap="nowrap"><?php echo JText::_( 'Action' ); ?></th>
					<th width="20" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'ID', 'a.id', $this->orderDirection, $this->order ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			if( $this->reports )
			{
				$k = 0;
				$x = 0;
				$config	= DiscussHelper::getJConfig();
				for ($i=0, $n = count( $this->reports ); $i < $n; $i++)
				{
					$row 		= $this->reports[$i];

					$user		= JFactory::getUser( $row->user_id );
					$editLink	= JRoute::_('index.php?option=com_easydiscuss&controller=reports&task=edit&id='.$row->id);
					$published 	= JHTML::_('grid.published', $row, $i );

					$date		= DiscussHelper::getDate( $row->lastreport );
					$date->setOffset(  $config->get('offset')  );

					$actions	= array();
					$actions[]	= JHTML::_('select.option',  '', '- '. JText::_( 'COM_EASYDISCUSS_SELECT_ACTION' ) .' -' );
					$actions[]	= JHTML::_('select.option',  'D', JText::_( 'COM_EASYDISCUSS_DELETE_POST' ) );
					$actions[]	= JHTML::_('select.option',  'C', JText::_( 'COM_EASYDISCUSS_REMOVE_REPORT' ) );
					$actions[]	= JHTML::_('select.option',  'P', JText::_( 'Published' ) );
					$actions[]	= JHTML::_('select.option',  'U', JText::_( 'Unpublished' ) );

					if($row->user_id != 0)
					{
						$actions[] = JHTML::_('select.option',  'E', JText::_( 'COM_EASYDISCUSS_EMAIL_AUTHOR' ) );
					}
					$actionsDropdown	= JHTML::_('select.genericlist',   $actions, 'report-action-' . $row->id, ' style="width:250px;" size="1" onchange="admin.reports.change(\''. $row->id .'\');"', 'value', 'text', '*' );


					$viewLink	= JURI::root() . 'index.php?option=com_easydiscuss&view=post&id=' . $row->id;

					if( $row->parent_id != 0 )
					{
						$viewLink	= JURI::root() . 'index.php?option=com_easydiscuss&view=post&id=' . $row->parent_id;
					}
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $this->pagination->getRowOffset( $i ); ?>
					</td>
					<td width="7">
						<?php echo JHTML::_('grid.id', $x++, $row->id); ?>
					</td>
					<td align="left">
						<div>
							<?php echo $this->escape( $row->reason ); ?>
						</div>
						<div>
							[ <a href="<?php echo $viewLink;?>" target="_blank"><?php echo JText::_( 'COM_EASYDISCUSS_VIEW_POST' ); ?></a> ]
						</div>
					</td>
					<td align="center">
						<span class="editlinktip hasTip">
							<?php if($row->user_id == 0) : ?>
								<?php echo JText::_('GUEST'); ?>
							<?php else : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_easydiscuss&controller=user&id=' . $row->user_id . '&task=edit'); ?>"><?php echo $user->name; ?></a>
							<?php endif; ?>
						</span>
					</td>
					<td align="center">
						<?php echo $row->reportCnt; ?>
					</td>
					<td align="center">
						<?php echo $date->toFormat();?>
					</td>
					<td align="center">
						<?php echo $published; ?>
					</td>
					<td align="left">
						<div id="action-container-<?php echo $row->id;?>">
							<?php echo $actionsDropdown; ?>
							<input type="button" name="actions-btn-<?php echo $row->id;?>" id="actions-btn-<?php echo $row->id;?>" value="Submit" onclick="reportAction('<?php echo $row->id;?>'); return false;" />
							<div><span id="report-entry-msg-<?php echo $row->id;?>"></span></div>
							<div id="email-container-<?php echo $row->id;?>" style="display:none;">
								<br />
								<div><?php echo JText::_('COM_EASYDISCUSS_YOUR_TEXT'); ?> : </div>
								<textarea name="email_text" id="email-text-<?php echo $row->id;?>" class="inputbox textarea" style="width:300px;"></textarea>
							</div>
						</div>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php $k = 1 - $k; } ?>
			<?php
			}
			else
			{
			?>
				<tr>
					<td colspan="9" align="center" class="center">
						<?php echo JText::_('COM_EASYDISCUSS_NO_REPORTS');?>
					</td>
				</tr>
			<?php
			}
			?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="9">
						<div class="footer-pagination">
							<?php echo $this->pagination->getListFooter(); ?>
						</div>
					</td>
				</tr>
			</tfoot>
		</table>






	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_easydiscuss" />
	<input type="hidden" name="view" value="reports" />
	<input type="hidden" id="task" name="task" value="" />
	<input type="hidden" id="post_id" name="post_id" value="" />
	<input type="hidden" id="post_val" name="post_val" value="" />
	<input type="hidden" name="controller" value="reports" />
	<input type="hidden" name="filter_order" value="<?php echo $this->order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
</form>
