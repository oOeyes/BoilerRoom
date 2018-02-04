<?php

/**
 * This class takes the content of a boilerplate page and divides into sections according to usage of the <boilerplate>,
 * <openboilerplate>, and <closeboilerplate> tags. The sections can then be accessed directly or the entire page can
 * be rendered for display.
 * 
 * To avoid the need for any text escaping, the tags function in a non-compliant way. Everything between the first 
 * instance of one of these tags on a page to the last instance is considered to be inside the tag, regardless of if 
 * standard XML or HTML tag parser would consider that to be all inside the tag. Additionally, the actual content begins
 * immediately after the first newline after the first tag and ends immediately before the newline before the last tag.
 *
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2011 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class BoilerplatePage {
  /**
   * Identifies a section of the page not within actual boilerplate content.
   * @var int meta 
   */
  const metaContent = 0;
  
  /**
   * Identifies that this section of the page is within a standard boilerplate.
   * @var int
   */
  const standardContent = 1;
  
  /**
   * Identifies that this section of the page in within the opening portion of a wrapping boilerplate.
   * @var int
   */
  const openingContent = 2;
  
  /**
   * Identifies that this section of the page in within the closeing portion of a wrapping boilerplate.
   * @var int
   */
  const closingContent = 3;
  
  /**
   * The content of the page divided into sections. Each element has array with "type" and "content".
   * @var string
   */
  private $mSections = Array();
  
  /**
   * An array with array elements with "openText", "openLength", "closeText", and "closeLength" elements keyed by the. 
   * tag type they are associated with. Used to help search content for the tags.
   * @var Array
   */
  private static $mTags = null;
  
  /**
   * Initializes the $mTags array if that has not yet been done.
   */
  private static function initializeTags( ) {
    if ( self::$mTags === null ) {
      self::$mTags = Array( self::standardContent => Array( 'openText' => '<boilerplate>',
                                                            'openLength' => 13,
                                                            'closeText' => '</boilerplate>',
                                                            'closeLength' => 14,
                                                        ),
                            self::openingContent => Array( 'openText' => '<openboilerplate>',
                                                           'openLength' => 17,
                                                           'closeText' => '</openboilerplate>',
                                                           'closeLength' => 18,
                                                       ),
                            self::closingContent => Array( 'openText' => '<closeboilerplate>',
                                                           'openLength' => 18,
                                                           'closeText' => '</closeboilerplate>',
                                                           'closeLength' => 19,
                                                       ),
                          );
    }
  }
  
  /**
   * Creates a BoilerplatePage based on the given page content with that content divided into appropriate sections,
   * ready for rendering if that is needed.
   * @param string $text The page content of a boilerplate page to parse and potentially render.
   */
  public function __construct( $text ) {
    self::initializeTags();
    
    $this->splitText( $text );
  }
  
  /**
   * Returns true if there is any boilerplate content in the page.
   * @return bool true if there is boilerplate content.
   */
  public function hasBoilerplateContent() {
    foreach ( $this->mSections as $section ) {
      if ( $section['type'] !== self::metaContent && 
           $section['content'] !== null && 
           trim( $section['content'] ) !== '' 
         ) {
        return true;
      }
    }
    
    return false;
  }
  
  /**
   * Returns the boilerplate content within the <boilerplate> tag.
   * @return string The content within the <boilerplate> tag according to its processing rules.
   */
  public function getStandardContent( ) {
    return $this->getContent( self::standardContent );
  }
  
  /**
   * Returns the boilerplate content within the <openboilerplate> tag.
   * @return string The content within the <openboilerplate> tag according to its processing rules.
   */
  public function getOpeningContent( ) {
    return $this->getContent( self::openingContent );
  }
  
  /**
   * Returns the boilerplate content within the <closeboilerplate> tag.
   * @return string The content within the <closeboilerplate> tag according to its processing rules.
   */
  public function getClosingContent( ) {
    return $this->getContent( self::closingContent );
  }
  
  /**
   * Returns the boilerplate content within the appropriate tag.
   * @param int $type One of these class constants: standardContent, openingContent, or closingContent
   * @return string The content within the appropriate tag according to its processing rules or null if there is none.
   */
  public function getContent( $type ) {
    foreach ( $this->mSections as $section ) {
      if ( $section['type'] === $type ) {
        return $section['content'];
      }
    }
    
    return null;
  }
  
  /**
   * Returns the boilerplate content in an array indexed by section type.
   * @return Array Array indexed by the BR_?_BP_CONTENT constants with content or null if that section has none.
   */
  public function getBoilerplateContent() {
    $content = Array( self::openingContent => null, self::openingContent => null, self::closingContent => null );
    
    foreach ( $this->mSections as $section ) {
      if ( $section['type'] !== self::metaContent && $section['content'] !== null ) {
        $content[$section['type']] = $section['content'];
      }
    }
    
    return $content;
  }
  
  /**
   * Returns the boilerplate content within all boilerplates tag in a single string.
   * @return string The content within all boilerplate tags or an empty string if there is none.
   */
  public function getCombinedBoilerplateContent() {
    $sections = $this->getBoilerplateContent();
    
    $content = '';
    if ( array_key_exists( self::openingContent, $sections ) && $sections[self::openingContent] !== null ) {
      $content .= $sections[self::openingContent];
    }
    if ( array_key_exists( self::standardContent, $sections ) && $sections[self::standardContent] !== null ) {
      $content .= $sections[self::standardContent];
    }
    if ( array_key_exists( self::closingContent, $sections ) && $sections[self::closingContent] !== null ) {
      $content .= $sections[self::closingContent];
    }
    return $content;
  }
  
  /**
   * <boilerplate>, <openboilerplate>, and <closeboilerplate> need different behavior than most tags.  We want to return
   * everything between the *first* opening tag and the *last* closing tag, ignoring any in between. Thus, preprocessing
   * escapes the content in <nowiki> and needs to be called before any parsing is done.
   * @return The rendered content with anything within the appropriate tags properly escaped with <nowiki>.
   */  
  public function getRenderedContent( ) {    
    $text = "";
    for ( $i = 0; $i < count( $this->mSections ); ++$i ) {
      if ( $this->mSections[$i]['type'] === self::metaContent ) {
        $text .= $this->mSections[$i]['content'];
      } else {
        $text .= "\n <nowiki>" . htmlspecialchars( $this->mSections[$i]['content'] ) . "</nowiki>";
      }
    }
    
    return $text;
  }
  
  /**
   * This helper function returns rendered content directly for those functions that do not need to deal with
   * individual section content.
   * @param string $text The text of the boilerplate page to preprocess and render as wikicode.
   * @return The rendered content with anything within the appropriate tags properly escaped with <nowiki>.
   */
  public static function renderContent( $text ) {
    $page = new self( $text );
    return $page->getRenderedContent();
  }
  
  /**
   * This function divides the text into sections as divided by the <boilerplate>, <openboilerplate>, and 
   * <closeboilerplate> tags.
   * @param $text The text to split.
   */
  private function splitText( $text ) {
    $this->mSections = Array();
    if ( $text != '' ) {
      $tags = self::filterFoundTags( self::findAllTags( $text ) );
      
      $pos = 0; // skipping tags without newlines in them, so need to remember where we leave off
      if ( count( $tags ) > 0 ) {
        if ( $tags[0]['openPos'] > 0 ) {
          $this->mSections[] = Array( 'type' => self::metaContent, 
                                      'content' => substr( $text, 0, $tags[0]['openPos'] )
                                    ); 
          $pos = $tags[0]['openPos'] + self::$mTags[$tags[0]['type']]['openLength']; 
        }
      }
      
      for ( $i = 0; $i < count( $tags ); ++$i ) {
        $tagInfo = self::$mTags[$tags[$i]['type']];
        $contentStart = strpos( $text, "\n", $tags[$i]['openPos'] + $tagInfo['openLength'] ) + 1;
        $contentEnd = strrpos( $text, "\n", -strlen( $text ) + $tags[$i]['closePos'] );
        if ( $contentStart !== false && $contentEnd !== false && $contentStart <= $contentEnd ) {
          $this->mSections[] = Array( 'type' => $tags[$i]['type'],
                                      'content' => substr( $text, $contentStart, $contentEnd - $contentStart ),
                                    );
          $pos = $tags[$i]['closePos'] + $tagInfo['closeLength'];
          
          if ( $i < count( $tags ) - 1 ) {
            $this->mSections[] = Array( 'type' => self::metaContent,
                                        'content' => substr( $text, $pos, $tags[$i + 1]['openPos'] - $pos ),
                                      );
          }
        }
      }
      
      if ( $pos < strlen( $text ) ) {
        $this->mSections[] = Array( 'type' => self::metaContent,
                                    'content' => substr( $text, $pos, strlen( $text ) - $pos ),
                                  );
      }
    }
  }
  
  /**
   * Searches text for all <boilerplate>, <openboilerplate>, and <closeboilerplate> and returns them sorted.
   * @param string $text The text to search.
   * @return Array Indexed array with array elements having "type", "isOpen", "pos" element sorted by "pos" elements.
   */
  private function findAllTags( $text ) {
    $foundTags = Array();
    $length = strlen( $text );
    
    foreach ( self::$mTags as $type => $tag ) {
      $openPos = -$tag['openLength'];
      while ( $openPos + $tag['openLength'] < $length && 
              ( $openPos = strpos( $text, $tag['openText'], $openPos + $tag['openLength'] ) ) !== false 
            ) {
        $foundTags[] = Array( 'type' => $type, 'isOpen' => true, 'pos' => $openPos );
      }
      
      $closePos = -$tag['closeLength'];
      while ( $closePos + $tag['closeLength'] < $length &&
              ( $closePos = strpos( $text, $tag['closeText'], $closePos + $tag['closeLength'] ) ) !== false 
            ) {
        $foundTags[] = Array( 'type' => $type, 'isOpen' => false, 'pos' => $closePos );
      }
    }
    
    usort( $foundTags, "BoilerplatePage::posCompare" );
    
    return $foundTags;
  }
  
  /**
   * Compares two positions from the "type" and "positions" array returned by getSectionPositions.
   * @param Array $left First array to compare.
   * @param Array $right Second array to compare.
   * @return int -1 if $left is less, 0 if they are equal, 1 if right is less
   */
  private function posCompare( $left, $right ) {
    if ( $left['pos'] == $right['pos'] ) { 
      return 0; 
    } 
    return ( $left['pos'] < $right['pos'] ) ? -1 : 1;
  }
  
  /**
   * Takes an array of tags from findAllTags, prunes out any between other tag pairs in a greedy fashion, and filters 
   * the rest down to the first opener for each tag and the last closer for each tag. Also combines openers and closers
   * into a single array element to simplify further processing.
   * @param Array $foundTags Array from findAllTags
   * @return Array Indexed array with array elements having "type", "openPos", and "closePos" elements.
   */
  private function filterFoundTags( $foundTags ) {
    // We need to prune out any tags inside the sections formed by other tags, because those need to be 
    // treated as plain text of the boilerplate content itself, not relevant markup. It also prunes any that do not
    // have closing tags, and conversely, any closing tags without opening tags.
    $filteredTags = Array();
    for ( $top = 0; $top < count( $foundTags ); ++$top ) {
      if ( $foundTags[$top]['isOpen'] ) {
        for ( $bottom = count( $foundTags ) - 1; $bottom > $top; --$bottom ) {
          if ( !$foundTags[$bottom]['isOpen'] ) {
            if ( $foundTags[$top]['type'] === $foundTags[$bottom]['type'] ) {
              // Found a matching pair
              $filteredTags[] = Array( 'type' => $foundTags[$top]['type'],
                                       'openPos' => $foundTags[$top]['pos'],
                                       'closePos' => $foundTags[$bottom]['pos'],
                                     );
              $top = $bottom; // all in between should be ignored, so just skip processing them at all
            } else {
              // Not a match, but it might close a valid section. We need to see if there's a matching open tag.
              // If so, all tags in between must be ignored.
              for ( $bottom2 = $bottom - 1; $bottom2 > $top; --$bottom2 ) {
                if ( $foundTags[$bottom2]['type'] === $foundTags[$bottom]['type'] && $foundTags[$bottom2]['isOpen'] ) {
                  $bottom = $bottom2;
                  break;
                }
              }
            }
          }
        }
      }
    }
    
    return $filteredTags; // we should have only first and last of each tag now as well
  }
}