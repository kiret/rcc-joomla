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

$access			= trim( $system->config->get( 'tab_site_access' ) );

// Nobody can view this if access is not set yet.
if( !$access )
{
	return;
}

$access			= explode( ',' , $access );
$gids 			= DiscussHelper::getUserGids();

$url 			= $this->getFieldData( 'siteurl' , $post->params );

if( stristr( $url[0] , 'http://') === false && stristr( $url[0] , 'https://') === false)
{
	$url[0]	= 'http://' . $url[0];
}
$siteusername 	= $this->getFieldData( 'siteusername' , $post->params );
$password		= $this->getFieldData( 'sitepassword' , $post->params );
$siteinfo		= $this->getFieldData( 'siteinfo' , $post->params );
$ftpurl			= $this->getFieldData( 'ftpurl' , $post->params );
$ftpusername	= $this->getFieldData( 'ftpusername' , $post->params );
$ftppassword	= $this->getFieldData( 'ftppassword' , $post->params );

if( empty( $siteusername[0] ) && empty( $password[0] ) && empty( $siteinfo[0] ) && empty( $ftpurl[0] ) && empty( $ftpusername[0] ) && empty( $ftppassword[0] ) )
{
	return false;
}

$view 	= JRequest::getVar( 'view' );
?>
<?php if( $view == 'ask' && $system->config->get( 'tab_site_question') || $view == 'post' && $system->config->get( 'tab_site_reply') ){ ?>
	<?php foreach( $gids as $gid ){ ?>
		<?php if( in_array( $gid , $access ) ){ ?>
		<div class="pt-20">
			<h3><?php echo JText::_( 'COM_EASYDISCUSS_TAB_SITE_DETAILS' ); ?></h3>
			<hr />
			<table width="100%" class="table table-striped">
				<tr>
					<td width="20%"><?php echo JText::_( 'COM_EASYDISCUSS_TAB_SITE_FORM_URL' );?>:</td>
					<td>
						<a href="<?php echo $this->escape( $url[0] ); ?>" target="_blank"><?php echo $this->escape( $url[0] ); ?></a>
					</td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'COM_EASYDISCUSS_TAB_SITE_FORM_USERNAME' );?>:</td>
					<td><?php echo $this->escape( $siteusername[0] ); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'COM_EASYDISCUSS_TAB_SITE_FORM_PASSWORD' );?>:</td>
					<td><?php echo $this->escape( $password[0] ); ?></td>
				</tr>
				<tr>
					<td width="20%"><?php echo JText::_( 'COM_EASYDISCUSS_TAB_SITE_FORM_FTP_URL' );?>:</td>
					<td>
						<?php echo $this->escape( $ftpurl[0] ); ?>
					</td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'COM_EASYDISCUSS_TAB_SITE_FORM_FTP_USERNAME' );?>:</td>
					<td><?php echo $this->escape( $ftpusername[0] ); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'COM_EASYDISCUSS_TAB_SITE_FORM_FTP_PASSWORD' );?>:</td>
					<td><?php echo $this->escape( $ftppassword[0] ); ?></td>
				</tr>
				<tr>
					<td colspan="2"><?php echo JText::_( 'COM_EASYDISCUSS_TAB_SITE_FORM_OPTIONAL' );?>:</td>
				</tr>
					<td colspan="2">
						<?php echo str_ireplace( '\n' , "<br />" , nl2br( $siteinfo[0] ) ); ?>

					</td>
				</tr>
			</table>
		</div>
			<?php
			// If there is match, just return here.
			return;
			?>
		<?php } ?>
	<?php } ?>
<?php } ?>