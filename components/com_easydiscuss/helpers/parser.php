<?php
// ----------------------------------------------------------------------------
// markItUp! BBCode Parser
// v 1.0.6
// Dual licensed under the MIT and GPL licenses.
// ----------------------------------------------------------------------------
// Copyright (C) 2009 Jay Salvat
// http://www.jaysalvat.com/
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
// ----------------------------------------------------------------------------
// Thanks to Arialdo Martini, Mustafa Dindar for feedbacks.
// ----------------------------------------------------------------------------


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

define ("EMOTICONS_DIR", DISCUSS_MEDIA_URI . '/images/markitup/');

require_once JPATH_ROOT . '/components/com_easydiscuss/constants.php';
require_once JPATH_ROOT . '/components/com_easydiscuss/helpers/helper.php';

class Parser
{
	public static function bbcode($text)
	{
		$text	= self::html2bbcode( $text );

		// $text	= htmlspecialchars($text , ENT_NOQUOTES );
		$text	= trim($text);

		// @rule: Replace [code]*[/code]
		$codesPattern	= '/\[code( type=&quot;(.*?)&quot;)?\](.*?)\[\/code\]/ms';

		// preg_match( $codesPattern , $text , $codes );
		$text = preg_replace_callback( $codesPattern , array( 'Parser' , 'escape' ) , $text );

		// BBCode to find...
		$bbcodeSearch = array( 	 '/\[b\](.*?)\[\/b\]/ims',
						 '/\[i\](.*?)\[\/i\]/ims',
						 '/\[u\](.*?)\[\/u\]/ims',
						 '/\[img\](.*?)\[\/img\]/ims',
						 '/\[email\](.*?)\[\/email\]/ims',
						 '/\[url\="?(.*?)"?\](.*?)\[\/url\]/ims',
						 '/\[size\="?(.*?)"?\](.*?)\[\/size\]/ims',
						 '/\[color\="?(.*?)"?\](.*?)\[\/color\]/ims',
						 '/\[quote](.*?)\[\/quote\]/ims',
						 '/\[list\=(.*?)\](.*?)\[\/list\]/ims',
						 '/\[list\](.*?)\[\/list\]/ims',
						 '/\[\*\]\s?(.*?)\n/ims',
						 '/\[\*\]\s?(.*?)/ims'
		);

		$config			= DiscussHelper::getConfig();
		$targetBlank	= $config->get( 'main_link_new_window' ) ? '<a href="\1" target="_blank">\2</a>' : '<a href="\1">\2</a>';

		// And replace them by...
		$bbcodeReplace = array(	 '<strong>\1</strong>',
						 '<em>\1</em>',
						 '<u>\1</u>',
						 '<img src="\1" alt="\1" />',
						 '<a href="mailto:\1">\1</a>',
						 $targetBlank,
						 '<span style="font-size:\1%">\2</span>',
						 '<span style="color:\1">\2</span>',
						 '<blockquote>\1</blockquote>',
						 '<ol start="\1">\2</ol>',
						 '<ul>\1</ul>',
						 '<li>\1</li>',
						 '<li>\1</li>'
		);

		//$text .= "\n";

		// @rule: Replace URL links.
		// We need to strip out bbcode's data first.
		$tmp	= preg_replace( $bbcodeSearch , '' , $text );

		// Replace video codes
		$tmp	= DiscussHelper::getHelper( 'Videos' )->strip( $tmp );

		// Replace URLs
		$text	= DiscussHelper::getHelper( 'URL' )->replace( $tmp , $text );

		// @rule: Replace video links
		$text	= DiscussHelper::getHelper( 'Videos' )->replace( $text );

		// Smileys to find...
		$in = array( 	 ':D',
						 ':)',
						 ':o',
						 ':p',
						 ':(',
						 ';)'
		);
		// And replace them by...
		$out = array(	 '<img alt=":D" class="bb-smiley" src="'.EMOTICONS_DIR.'emoticon-happy.png" />',
						 '<img alt=":)" class="bb-smiley" src="'.EMOTICONS_DIR.'emoticon-smile.png" />',
						 '<img alt=":o" class="bb-smiley" src="'.EMOTICONS_DIR.'emoticon-surprised.png" />',
						 '<img alt=":p" class="bb-smiley" src="'.EMOTICONS_DIR.'emoticon-tongue.png" />',
						 '<img alt=":(" class="bb-smiley" src="'.EMOTICONS_DIR.'emoticon-unhappy.png" />',
						 '<img alt=";)" class="bb-smiley" src="'.EMOTICONS_DIR.'emoticon-wink.png" />'
		);
		$text = str_replace($in, $out, $text);

		// Replace bbcodes
		$text 	= preg_replace( $bbcodeSearch , $bbcodeReplace, $text);

		return $text;
	}

	public static function removeBr($s) {
		$string = str_replace("<br />", "", $s[0]);
		$string = str_replace("<br>", "", $s[0]);

		return $string;
	}

	public static function removeNewline($s) {
		return str_replace("\r\n", "", $s[0]);
	}

	public static function escape($s)
	{
		$code	= $s[3];
		$code	= str_ireplace( "<br />" , "" , $code );

		$code	= str_replace("[", "&#91;", $code);
		$code	= str_replace("]", "&#93;", $code);

		$brush	= isset( $s[2] ) && !empty( $s[2] ) ? $s[2] : 'xml';

		$code	= html_entity_decode( $code );
		$code	= DiscussHelper::getHelper( 'String' )->escape( $code );

		return '<pre>'.$code.'</pre>';
	}

	public static function removeCodes( $content )
	{

		$codesPattern	= '/\[code( type="(.*?)")?\](.*?)\[\/code\]/ms';

		return preg_replace( $codesPattern , '' , $content );
	}

	public static function filter($text)
	{

		$text	= htmlspecialchars($text , ENT_NOQUOTES );
		$text	= trim($text);

		// @rule: Replace [code]*[/code]
		$text = preg_replace_callback('/\[code( type="(.*?)")?\](.*?)\[\/code\]/ms', array( 'Parser' , 'escape' ) , $text );

		// BBCode to find...
		$bbcodeSearch = array( 	 '/\[b\](.*?)\[\/b\]/ims',
						 '/\[i\](.*?)\[\/i\]/ims',
						 '/\[u\](.*?)\[\/u\]/ims',
						 '/\[img\](.*?)\[\/img\]/ims',
						 '/\[email\](.*?)\[\/email\]/ims',
						 '/\[url\="?(.*?)"?\](.*?)\[\/url\]/ims',
						 '/\[size\="?(.*?)"?\](.*?)\[\/size\]/ims',
						 '/\[color\="?(.*?)"?\](.*?)\[\/color\]/ims',
						 '/\[quote](.*?)\[\/quote\]/ims',
						 '/\[list\=(.*?)\](.*?)\[\/list\]/ims',
						 '/\[list\](.*?)\[\/list\]/ims',
						 '/\[\*\]\s?(.*?)\n/ims'
		);

		// @rule: Replace URL links.
		// We need to strip out bbcode's data first.
		$text	= preg_replace( $bbcodeSearch , '' , $text );
		$text	= DiscussHelper::getHelper( 'URL' )->replace( $text , $text );


		// Smileys to find...
		$in = array( 	 ':)',
						 ':D',
						 ':o',
						 ':p',
						 ':(',
						 ';)'
		);
		// And replace them by...
		$out = array(	 '<img alt=":)" src="'.EMOTICONS_DIR.'emoticon-smile.png" />',
						 '<img alt=":D" src="'.EMOTICONS_DIR.'emoticon-happy.png" />',
						 '<img alt=":o" src="'.EMOTICONS_DIR.'emoticon-surprised.png" />',
						 '<img alt=":p" src="'.EMOTICONS_DIR.'emoticon-tongue.png" />',
						 '<img alt=":(" src="'.EMOTICONS_DIR.'emoticon-unhappy.png" />',
						 '<img alt=";)" src="'.EMOTICONS_DIR.'emoticon-wink.png" />'
		);
		$text = str_replace($in, $out, $text);

		return $text;
	}

	public static function html2bbcode( $text )
	{

		if( (stripos($text, '<p') === false) && (stripos($text, '<div') === false) &&  (stripos($text, '<br') === false))
		{
			return $text;
		}

		$bbcodeSearch = array(
			'/<strong>(.*?)<\/strong>/ims',
			'/<b>(.*?)<\/b>/ims',
			'/<big>(.*?)<\/big>/ims',
			'/<em>(.*?)<\/em>/ims',
			'/<i>(.*?)<\/i>/ims',
			'/<u>(.*?)<\/u>/ims',
			'/<img.*?src=["|\'](.*?)["|\'].*?\>/ims',
			'/<[pP]>/ims',
			'/<\/[pP]>/ims',
			'/<blockquote>(.*?)<\/blockquote>/ims',
			'/<ol.*?\>(.*?)<\/ol>/ims',
			'/<ul.*?\>(.*?)<\/ul>/ims',
			'/<li.*?\>(.*?)<\/li>/ims',
			'/<a.*?href=["|\']mailto:(.*?)["|\'].*?\>.*?<\/a>/ims',
			'/<a.*?href=["|\'](.*?)["|\'].*?\>(.*?)<\/a>/ims',
			'/<pre.*?\>(.*?)<\/pre>/ims'
		);

		$bbcodeReplace = array(
			'[b]\1[/b]',
			'[b]\1[/b]',
			'[b]\1[/b]',
			'[i]\1[/i]',
			'[i]\1[/i]',
			'[u]\1[/u]',
			'[img]\1[/img]',
			'',
			'<br />',
			'[quote]\1[/quote]',
			'[list=1]\1[/list]',
			'[list]\1[/list]',
			'[*] \1',
			'[email]\1[/email]',
			'[url="\1"]\2[/url]',
			'[code type="xml"]\1[/code]'
		);

		// Replace bbcodes
		$text	= strip_tags($text, '<br><strong><em><u><img><a><p><blockquote><ol><ul><li><b><big><i><pre>');
		$text	= preg_replace( $bbcodeSearch , $bbcodeReplace, $text);
		$text	= str_ireplace('<br />', "\r\n", $text);
		$text	= str_ireplace('<br>', "\r\n", $text);

		return $text;
	}
}
