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
if( !defined( 'MEDIAWIKI' ) ) {
  die( 'Invalid entry point.' );
}

// Extension credits.
$wgExtensionCredits[ 'other' ][] = array(
  'name'           => 'BoilerRoom',
  'url'            => 'http://www.mediawiki.org/wiki/Extension:BoilerRoom', 
  'description'    => 'Allows wiki users to create boilerplate text in the Boilerplate ' . 
                      'namespace which can then be used on new pages.',
  'descriptionmsg' => 'boilerroom-desc',
  'author'         => '[http://www.mediawiki.org/wiki/User:OoEyes Shawn Bruckner]',
  'version'        => '1.3',
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
if ( !isset( $wgbrNamespaceIndex ) ) {
  $wgbrNamespaceIndex = 450;
}

/*
 * $wgbrUseLargeSelector --
 *       When set to true, this uses the large version of the four button selector
 *       that sits over the standard toolbar.  The default is false.
 */
if ( !isset( $wgbrUseLargeSelector ) ) {
  $wgbrUseLargeSelector = false;
}

/**
 * Set up constants.
 */
define( "BR_NON_BP_CONTENT", 0 ); // Identifies a section of the page not within actual boilerplate content.
define( "BR_STANDARD_BP_CONTENT", 1 ); // Identifies that this section of the page is within a standard boilerplate.
define( "BR_OPENING_BP_CONTENT", 2 ); // Identifies that this section of the page in within the opening portion of a 
                                      // wrapping boilerplate.
define( "BR_CLOSING_BP_CONTENT", 3 ); // Identifies that this section of the page in within the closeing portion of a 
                                      // wrapping boilerplate.
  
/**
 * Perform setup tasks.
*/
$wgMessagesDirs['BoilerRoom'] = dirname ( __FILE__ ) . '/i18n';
$wgExtensionMessagesFiles['BoilerRoomMagic'] = dirname( __FILE__ ) . '/BoilerRoom.i18n.php';
$wgExtensionMessagesFiles['BoilerRoomAlias'] = dirname( __FILE__ ) . '/BoilerRoom.alias.php';

require_once( dirname( __FILE__ ) . '/includes/BoilerplateNamespace.php' );
BoilerplateNamespace::getInstance()->initialize();

$wgAutoloadClasses['BoilerplateNamespace'] = dirname( __FILE__ ) . '/includes/BoilerplateNamespace.php';
$wgAutoloadClasses['ApiQueryBoilerplate'] = dirname( __FILE__ ) . '/includes/ApiQueryBoilerplate.php';
$wgAutoloadClasses['BoilerplateTag'] = dirname( __FILE__ ) . '/includes/BoilerplateTag.php';
$wgAutoloadClasses['BoilerplatePage'] = dirname( __FILE__ ) . '/includes/BoilerplatePage.php';
#$wgAutoloadClasses['BoilerRoomTransclude'] = dirname( __FILE__ ) . '/includes/BoilerRoomTransclude.php';
$wgAutoloadClasses['BoilerRoomBox'] = dirname( __FILE__ ) . '/includes/BoilerRoomBox.php';
$wgAutoloadClasses['BoilerRoomBoxHooks'] = dirname( __FILE__ ) . '/includes/BoilerRoomBoxHooks.php';
$wgAutoloadClasses['SpecialBoilerplate'] = dirname( __FILE__ ) . '/includes/SpecialBoilerplate.php';
$wgAutoloadClasses['BoilerRoomSelector'] = dirname( __FILE__ ) . '/includes/BoilerRoomSelector.php';

$wgHooks['ParserFirstCallInit'][] = Array( BoilerplateTag::getInstance(), 'initialize' );
$wgHooks['ParserBeforeStrip'][] = Array( BoilerplateTag::getInstance(), 'processPage' );
$wgHooks['EditFormPreloadText'][] = Array ( BoilerplateNamespace::getInstance(), 'preloadOnNewPage' );

$wgHooks['ParserFirstCallInit'][] = Array( BoilerRoomBoxHooks::getInstance(), 'parserFunctionAndTagSetup' );

$wgHooks['EditPage::showEditForm:initial'][] = Array( BoilerRoomSelector::getInstance(), 'renderOutput' );

$wgAPIModules['boilerplate'] = "ApiQueryBoilerplate";
$wgSpecialPages['Boilerplate'] = 'SpecialBoilerplate';

$wgResourceModules['ext.BoilerRoom.ajaxSelector'] = Array(
  'scripts' => 'includes/ajaxBoilerRoomSelector.js',
  'localBasePath' => dirname( __FILE__ ),
  'dependencies' => Array( 'mediawiki.jqueryMsg', 'mediawiki.notify', 'mediawiki.notification' ),
  'position' => 'top',
  'messages' => Array(
    'br-page-exists-ajax',
    'br-prepend',
    'br-append',
    'br-replace',
    'br-insert',
    'br-no-boilerplates',
    'br-selector-legend',
    'br-selector-insert',
    'br-selector-replace',
    'br-selector-prepend',
    'br-selector-append',
    'br-selector-edit',
    'br-selector-create',
    'br-replace-confirm',
  ),
);