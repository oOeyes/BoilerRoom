<?php

/**
 * Allows wiki users to create boilerplate text in the Boilerplate namespace which can 
 * then be used on new pages.  It includes numerous ways of loading the text onto a page,
 * and a unique <boilerroom> tag used to separate the boilerplate content from the rest of
 * a page, allowing boilerplate articles to be categorized and have descriptive text.
 *
 * @addtogroup Extensions
 *
 * @link 
 *
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2011-2013 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

// If this is run directly from the web die as this is not a valid entry point.
if( !defined( 'MEDIAWIKI' ) ) die( 'Invalid entry point.' );

// Extension credits.
$wgExtensionCredits[ 'other' ][] = array(
  'name'           => 'BoilerRoom',
  'url'            => 'http://www.mediawiki.org/wiki/Extension:BoilerRoom', 
  'description'    => 'Allows wiki users to create boilerplate text in the Boilerplate ' . 
                      'namespace which can then be used on new pages.',
  'descriptionmsg' => 'boilerroom-desc',
  'author'         => '[http://www.mediawiki.org/wiki/User:OoEyes Shawn Bruckner]',
  'version'        => '1.21',
);

/**
 * Options:
 *
 * $wgbrNamespaceIndex --
 *       Specifies at what index to create the Boilerplate namespace this extension
 *       uses to store boilerplates.  Needs to be changed if you have other extensions
 *       that create custom namespaces or have any custom namespaces of your own,
 *       unless, of course, they don't use indexes 450 or 451.
 *       Set this BEFORE including this file in LocalSettings.php!
 */
if ( !isset( $wgbrNamespaceIndex ) )
  $wgbrNamespaceIndex = 450;

/*
 * $wgbrUseLargeSelector --
 *       When set to true, this uses the large version of the four button selector
 *       that sits over the standard toolbar.  The default is false.
 */
if ( !isset( $wgbrUseLargeSelector ) )
  $wgbrUseLargeSelector = false;

/**
 * Perform setup tasks.
*/
$wgExtensionMessagesFiles['boilerroom'] = dirname( __FILE__ ) . '/BoilerRoom.i18n.php';

require_once( dirname( __FILE__ ) . '/includes/BoilerplateNamespace.php' );
BoilerplateNamespace::initialize();

require_once( dirname( __FILE__ ) . '/includes/BoilerplateTag.php' );
$tagParser = new BoilerplateTag;

$wgAutoloadClasses['BoilerplateNamespace'] = dirname( __FILE__ ) . '/includes/BoilerplateNamespace.php';
$wgAutoloadClasses['ApiQueryBoilerplate'] = dirname( __FILE__ ) . '/includes/ApiQueryBoilerplate.php';
$wgAutoloadClasses['BoilerplateTag'] = dirname( __FILE__ ) . '/includes/BoilerplateTag.php';
#$wgAutoloadClasses['BoilerRoomTransclude'] = dirname( __FILE__ ) . '/includes/BoilerRoomTransclude.php';
$wgAutoloadClasses['BoilerRoomBox'] = dirname( __FILE__ ) . '/includes/BoilerRoomBox.php';
$wgAutoloadClasses['BoilerRoomSelector'] = dirname( __FILE__ ) . '/includes/BoilerRoomSelector.php';

$wgHooks['ParserFirstCallInit'][] = Array( $tagParser, 'initialize' );
$wgHooks['ParserBeforeStrip'][] = Array( $tagParser, 'processPage' );
$wgHooks['EditFormPreloadText'][] = 'BoilerRoomBox::preloadBoilerplateOnNewPage';

$wgHooks['ParserFirstCallInit'][] = 'BoilerRoomBox::parserFunctionAndTagSetup';

$wgHooks['EditPageBeforeEditToolbar'][] = 'BoilerRoomSelector::renderOutput';

$wgAPIModules['boilerplate'] = "ApiQueryBoilerplate";

$wgResourceModules['ext.BoilerRoom.ajaxSelector'] = array(
  'scripts' => 'includes/ajaxBoilerRoomSelector.js',
  'localBasePath' => dirname( __FILE__ ),
);

?>