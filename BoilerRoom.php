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
 * @copyright Copyright © 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

// If this is run directly from the web die as this is not a valid entry point.
if( !defined( 'MEDIAWIKI' ) ) die( 'Invalid entry point.' );

// Extension credits.
$wgExtensionCredits[ 'other' ][] = array(
  'name'           => 'BoilerRoom',
  'description'    => 'Allows wiki users to create boilerplate text in the Boilerplate ' . 
                      'namespace which can then be used on new pages.',
  'descriptionmsg' => 'boilerroom-desc',
  'author'         => 'Eyes',
  'version'        => '0.92',
);

$wgbrIncludes = dirname( __FILE__ ) . '/includes';

/**
 * Options:
 *
 * $wgbrNamespaceIndex --
 *       Specifies at what index to create the Boilerplate namespace this extension
 *       uses to store boilerplates.  Needs to be changed if you have other extensions
 *       that create custom namespaces or have any custom namespaces of your own,
 *       unless, of course, they don't use indexes 300 or 301.
 *       Set this BEFORE including this file in LocalSettings.php!
 *       
 */
if ( !isset( $wgbrNamespaceIndex ) )
  $wgbrNamespaceIndex = 300;

/**
 * Perform setup tasks.
*/
$wgExtensionMessagesFiles['boilerroom'] = dirname( __FILE__ ) . '/BoilerRoom.i18n.php';

require_once( $wgbrIncludes . '/BoilerplateNamespaces.php' );
BoilerplateNamespaces::initialize();

require_once( $wgbrIncludes . '/BoilerplateTag.php' );
$tagParser = new BoilerplateTag;

// Make sure the BoilerRoom classes are loaded early
$wgAutoloadClasses['BoilerplateNamespace'] = $wgbrIncludes . '/BoilerplateNamespaces.php';
$wgAutoloadClasses['BoilerplateTag'] = $wgbrIncludes . '/BoilerplateTag.php';
#$wgAutoloadClasses['BoilerRoomTransclude'] = $wgbrIncludes . '/BoilerRoomTransclude.php';
$wgAutoloadClasses['BoilerRoomBox'] = $wgbrIncludes . '/BoilerRoomBox.php';
$wgAutoloadClasses['BoilerRoomSelector'] = $wgbrIncludes . '/BoilerRoomSelector.php';

$wgHooks['ParserFirstCallInit'][] = array($tagParser, 'initialize');
$wgHooks['ParserBeforeStrip'][] = array($tagParser, 'processPage');
$wgHooks['EditFormPreloadText'][] = 'BoilerRoomBox::preloadBoilerplateOnNewPage';

$wgHooks['ParserFirstCallInit'][] = 'BoilerRoomBox::parserFunctionAndTagSetup';
$wgHooks['LanguageGetMagic'][]       = 'BoilerRoomBox::parserFunctionMagic';

$wgHooks['EditPageBeforeEditToolbar'][] = 'BoilerRoomSelector::renderOutput';

require_once( $wgbrIncludes . '/BoilerRoomSelector.php' );
$wgAjaxExportList[] = 'BoilerRoomSelector::ajaxGetBoilerplateContent';

?>