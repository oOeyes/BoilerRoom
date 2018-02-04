BoilerRoom is a MediaWiki extension that allows wiki users to create boilerplate text in the Boilerplate namespace which
can then be used on new pages. It includes numerous ways of loading the text onto a page, and provides <boilerplate>,
<openboilerplate>, and <closeboilerplate> tags to separate the boilerplate content from the rest of a page, allowing 
boilerplate articles to be categorized and have descriptive text.

MediaWiki extension page: http://www.mediawiki.org/wiki/Extension:BoilerRoom
GitHub repository: https://github.com/oOeyes/BoilerRoom
Author: Shawn Bruckner (http://www.mediawiki.org/wiki/User:OoEyes)
License: GNU General Public License 2.0 or later (http://www.gnu.org/copyleft/gpl.html)

Options:
  $wgbrUseLargeSelector --
      When set to true, this uses the large version of the four button selector that sits over the standard toolbar. The
      default is false.

  $wgbrNamespaceIndex --
      Specifies at what index to create the Boilerplate namespace this extension uses to store boilerplates. The default
      is 450. The Boilerplate talk namespace will automatically take the index just above this setting, so 451 by 
      default. You may need to change this setting if:
          1. Another extension uses the indexes 450 and 451.
          2. The Boilerplate namespace existed previously a different index. Merely changing this setting will not move
             the pages to the correct index: in fact, they will simply become inaccessible.

             In very early versions of BoilerRoom, the default setting was 300. This was changed to avoid a conflict 
             with the PollNY extension. Wikis that used early versions of this extension may still have the Boilerplate
             articles there.

     If it is necessary to change this after boilerplates and boilerplate talk pages already exist, the following steps
     can be used to transfer them to the correct index:
          1. Create a custom namespace at the Boilerplate namespace's current index, using something like the following
             in LocalSettings.php:


// Define constants for my additional namespaces.
define("NS_OLDBP", 300); // This should be set to the current index where the boilerplate pages reside.
define("NS_OLDBP_TALK", 301); // This MUST be the following odd integer.

// Add namespaces.
$wgExtraNamespaces[NS_OLDBP] = "OldBP";
$wgExtraNamespaces[NS_FOO_TALK] = "OldBP_talk"; // Note underscores in the namespace name.


          2. Change $wgbrNamespaceIndex to the NEW namespace index.
          3. The pages previously in Boilerplate and Boierplate talk will now be in OldBP and OldBP talk. Move each one
             from OldBP and OldBP talk to Boilerplate and Boilerplate talk, respectively. It is best in this case NOT to
             leave redirects behind. (They'll be useless and unreachable after the next step, but remain in the 
             database.)
          4. Once the OldBP and OldBP talk namespaces have been emptied, the code from step 1 can be removed from 
             LocalSettings.php.

      See https://www.mediawiki.org/wiki/Manual:Using_custom_namespaces#Dealing_with_existing_pages for other options
      to update namespace indexes of pages. Some of these may be much more convenient if there are a large number of
      pages to update.