var boilerRoom = boilerRoom || {
  messageTimerId : null,

  /**
   * Main function for initializing the BoilerRoom selector.
   */
  initializeAjaxSelector : function() {
    mw.loader.using( 'user.options', function() {
      if ( mw.user.options.get( 'usebetatoolbar' ) ) {
        $.when(
				  mw.loader.using( 'ext.wikiEditor' ), $.ready
			  ).then( function () {
          $( '#wikiEditor-ui-toolbar' ).prepend(
            '<div id="boilerRoomSelectorContainer" style="float: right; line-height: 22px; margin: 5px;"></div>');
          boilerRoom.continueInitializeAjaxSelector();
        } );
      } else {
        if ( $( '#toolbar' ).length > 0 ) {
          $( '#toolbar' ).append('<div id="boilerRoomSelectorContainer" style="margin-top: -22px;"></div>');
        } else {
          $( '#wpTextbox1' ).before('<div id="boilerRoomSelectorContainer"></div>');
        }
        boilerRoom.continueInitializeAjaxSelector();
      }
    } );
  },

  /**
   * Continues the initialization work after the selector container is correctly placed.
   */
  continueInitializeAjaxSelector : function() {
    if ( $( '#boilerRoomSelectorContainer' ).length > 0 ) {
      $( '#boilerRoomSelectorContainer' ).css( {
        'position' : 'relative',
        'overflow' : 'visible'
      } );
      var messageBox = $( '<p id="boilerRoomMessage" />' );
      messageBox.addClass( 'mw-notification' );
      messageBox.css( {
        'display' : 'none',
        'opacity' : '1',
        'position' : 'absolute',
        'clear' : 'none',
        'bottom' : '100%',
        'right' : '0',
        'font-weight' : 'bold',
        'transform' : 'none',
        '-webkit-transform': 'none'
      } );
      if ( mw.config.get( 'wgbrUseLargeSelector' ) && !mw.user.options.get( 'usebetatoolbar' ) ) {
        boilerRoom.initializeLargeAjaxSelector();
      } else {
        messageBox.css( 'font-size', '80%' );
        boilerRoom.initializeSmallAjaxSelector();
      }
      messageBox.appendTo( '#boilerRoomSelectorContainer' );
    }

    var initialMessage = mw.config.get( 'wgbrInitialMessage' );
    if ( initialMessage !== '' ) {
      boilerRoom.displayMessage( initialMessage );
    }
  },

  /**
   * Constructs the HTML elements for the small AJAX selector and inserts them into the selector container.
   */
  initializeSmallAjaxSelector : function() {
    var htmlOptions = mw.config.get( 'wgbrBoilerplateHtmlOptions');
    if ( htmlOptions !== '' ) {
      $( '#boilerRoomSelectorContainer' ).html(
        '<div style="float: right;" ><select id="boilerRoomSelect" size="1">' + htmlOptions + '</select>' +
        '<img src="' + mw.config.get( 'wgExtensionAssetsPath' ) + '/BoilerRoom/images/button-insert.png" ' +
        'alt="' + mw.message( 'br-selector-insert' ).escaped() + '" ' +
        'title="' + mw.message( 'br-selector-insert' ).escaped() + '" ' +
        'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateInsert );">' +
        '<img src="' + mw.config.get( 'wgExtensionAssetsPath' ) + '/BoilerRoom/images/button-prepend.png" ' +
        'alt="' + mw.message( 'br-selector-prepend' ).escaped() + '" ' +
        'title="' + mw.message( 'br-selector-prepend' ).escaped() + '" ' +
        'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplatePrepend );">' +
        '<img src="' + mw.config.get( 'wgExtensionAssetsPath' ) + '/BoilerRoom/images/button-append.png" ' +
        'alt="' + mw.message( 'br-selector-append' ).escaped() + '" ' +
        'title="' + mw.message( 'br-selector-append' ).escaped() + '" ' +
        'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateAppend );">' +
        '<img src="' + mw.config.get( 'wgExtensionAssetsPath' ) + '/BoilerRoom/images/button-replace.png" ' +
        'alt="' + mw.message( 'br-selector-replace' ).escaped() + '" ' +
        'title="' + mw.message( 'br-selector-replace' ).escaped() + '" ' +
        'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateReplace );">' +
        '<img src="' + mw.config.get( 'wgExtensionAssetsPath' ) + '/BoilerRoom/images/button-edit.png" ' +
        'alt="' + mw.message( 'br-selector-edit' ).escaped() + '" ' +
        'title="' + mw.message( 'br-selector-edit' ).escaped() + '" ' +
        'onclick="boilerRoom.boilerplateEdit( );">' +
        '<img src="' + mw.config.get( 'wgExtensionAssetsPath' ) + '/BoilerRoom/images/button-create.png" ' +
        'alt="' + mw.message( 'br-selector-create' ).escaped() + '" ' +
        'title="' + mw.message( 'br-selector-create' ).escaped() + '" ' +
        'onclick="boilerRoom.boilerplateCreate( );">' +
        '</div>'
      );
    } else {
      $( '#boilerRoomSelectorContainer' ).html(
        '<div style="float: right;" >'+
        '<a onclick="boilerRoom.boilerplateCreate( );" style="cursor: pointer;"' +
        'title="' + mw.message( 'br-no-boilerplates' ).escaped() + '">' +
        mw.message( 'br-selector-create' ).escaped() + '</a>' +
        '</div>'
      );
    }
  },

  /**
   * Constructs the HTML elements for the large AJAX selector and inserts them into the selector container.
   */
  initializeLargeAjaxSelector : function() {
    var htmlOptions = mw.config.get( 'wgbrBoilerplateHtmlOptions');
    if ( htmlOptions !== '' ) {
      $( '#boilerRoomSelectorContainer' ).html(
        '<fieldset class="boilerRoomFieldSet" style="clear: both;">' +
        '<legend>' + mw.message( 'br-selector-legend' ).escaped() + '</legend>' +
        '<table class="boilerRoomSelector" style="width: 100%;"><tr>' +
        '<td style="padding: 0 2em 0 0; width: 40%;">' +
        '<select id="boilerRoomSelect" size="6" style="width: 100%;">' + htmlOptions +
        '</select>' +
        '</td><td style="padding: 0 0 0 2em; width: 30%;">' +
        '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
        'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateInsert );">' +
        mw.message( 'br-selector-insert' ).escaped() +
        '</button>' +
        '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
        'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateReplace );">' +
        mw.message( 'br-selector-replace' ).escaped() +
        '</button>' +
        '</td><td style="padding: 0 0 0 2em; width: 30%;">' +
        '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
        'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplatePrepend );">' +
        mw.message( 'br-selector-prepend' ).escaped() +
        '</button>' +
        '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
        'onclick="boilerRoom.boilerplateFetch( boilerRoom.boilerplateAppend );">' +
        mw.message( 'br-selector-append' ).escaped() +
        '</button>' +
        '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
        'onclick="boilerRoom.boilerplateEdit( );">' + mw.message( 'br-selector-edit' ).escaped() +
        '</button>' +
        '<button class="boilerRoomButton" style="width: 100%;" type="button" ' +
        'onclick="boilerRoom.boilerplateCreate( );">' + mw.message( 'br-selector-create' ).escaped() +
        '</button>' +
        '</td></tr></table>' +
        '</fieldset>'
      );
    } else {
      $( '#boilerRoomSelectorContainer' ).html(
        '<fieldset class="boilerRoomFieldSet" style="clear: both;">' +
        '<legend>' + mw.message( 'br-selector-legend' ).escaped() + '</legend>' +
        '<table class="boilerRoomSelector" style="width: 100%;"><tr>' +
        '<td style="padding: 0 2em 0 0; width: 100%; text-align: center;">' +
        '<p>' + mw.message( 'br-no-boilerplates' ).escaped() + '</p>' +
        '<button class="boilerRoomButton" style="width: 30%;" type="button" ' +
        'onclick="boilerRoom.boilerplateCreate( );">' + mw.message( 'br-selector-create' ).escaped() +
        '</button>' +
        '</td></tr></table>' +
        '</fieldset>'
      );
    }
  },

  /**
   * Displays a message above the selector for a short while.
   * @param {string} text The message to display.
   */
  displayMessage : function( text ) {
    if ( boilerRoom.messageTimerId !== null ) {
      window.clearTimeout( boilerRoom.messageTimerId );
    }

    var messageBox = $( '#boilerRoomMessage' );
    if ( text !== null && text !== '' ) {
      messageBox.html( text );
    }

    if ( messageBox.html() !== '' ) {
      messageBox.show();
      window.setTimeout( function() {
        $( '#boilerRoomMessage' ).hide();
      }, 5000);
    }
  },

  /**
   * Returns the title of the currently selected boilerplate, or
   * an empty string if none is selected.
   */
  getSelectedTitle : function() {
    return $("#boilerRoomSelect").children("option").filter(":selected").text();
  },

  /**
   * Calls into the BoilerRoom extension to fetch the boilerplate and then directs it to the indicated target function.
   * @param {function} target The target function that will perform the requested action after the fetch.
   */
  boilerplateFetch : function ( target ) {
    if ( boilerRoom.confirmAction( target ) ) {
      var title = boilerRoom.getSelectedTitle();
      if ( title !== '' ) {
        $.ajax( {
          type: 'GET',
          url: mw.config.get( 'wgScriptPath' ) + "/api.php",
          data: { action: 'boilerplate', format: 'json', title: title },
          dataType: 'json',
          success: target
        } );
      }
    }
  },

  /**
   * Raises a confirmation dialog for replacement and returns the result. Simply returns true for inserting, prepending,
   * and appending.
   * @param {function} target The target function, used to identify if confirmation should be automatic.
   */
  confirmAction : function( target ) {
    if ( target === boilerRoom.boilerplateInsert )
      return true;
    else if ( target === boilerRoom.boilerplatePrepend )
      return true;
    else if ( target === boilerRoom.boilerplateAppend )
      return true;
    else if ( target === boilerRoom.boilerplateReplace )
      return confirm( mw.message( 'br-replace-confirm' ).text() );
    else
      return false;  // invalid target
  },

  /**
   * Prepends the indicated content to the content within the edit box.
   * @param {string} content The content to insert.
   */
  boilerplateInsert : function( content ) {
    var editarea = $( '#wpTextbox1' );
    boilerRoom.insertAtCursor( editarea, content );
    boilerRoom.displayMessage( mw.message( 'br-insert', boilerRoom.getSelectedTitle() ).escaped() );
    editarea.focus();
  },

  /**
   * Prepends the indicated content to the content within the edit box.
   * @param {string} content The content to prepend.
   */
  boilerplatePrepend : function( content ) {
    var editarea = $( '#wpTextbox1' );
    var insertText = boilerRoom.getInsertText( content );
    editarea.val( insertText.text + editarea.val() );
    boilerRoom.displayMessage( mw.message( 'br-prepend', boilerRoom.getSelectedTitle() ).escaped() );
    boilerRoom.selectRange( editarea, insertText.selectionStart, insertText.selectionEnd );
    editarea.focus();
  },

  /**
   * Appends the indicated content to the content within the edit box.
   * @param {string} content The content to append.
   */
  boilerplateAppend : function( content ) {
    var editarea = $( '#wpTextbox1' );
    var insertText = boilerRoom.getInsertText( content );
    var oldLength = editarea.val().length;
    editarea.val( editarea.val() + insertText.text );
    boilerRoom.displayMessage( mw.message( 'br-append', boilerRoom.getSelectedTitle() ).escaped() );
    boilerRoom.selectRange( editarea, oldLength + insertText.selectionStart, oldLength + insertText.selectionEnd );
    editarea.focus();
  },

  /**
   * Replaces the content within the edit box with the boilerplate content.
   * @param {string} content The content to replace.
   */
  boilerplateReplace : function( content ) {
    var editarea = $( '#wpTextbox1' );
    var insertText = boilerRoom.getInsertText( content );
    editarea.val( insertText.text );
    boilerRoom.displayMessage( mw.message( 'br-replace', boilerRoom.getSelectedTitle() ).escaped() );
    boilerRoom.selectRange( editarea, insertText.selectionStart, insertText.selectionEnd );
    editarea.focus();
  },

  /**
   * Performs the steps needed to insert text at the cursor position in a textarea.
   * @param {element} textarea The textarea element to insert text into.
   * @param {string} content The text to insert into the textarea.
   */
  insertAtCursor : function( textarea, content ) {
    var selection, start, end, insertText;
    if ( typeof( document.selection ) !== "undefined" ) {
      textarea.focus();
      selection = document.selection.createRange();
      // Ugly hack that gets the selection start in IE
      var dupSelection = selection.duplicate();
      dupSelection.moveToElementText( element );
      dupSelection.setEndPoint( 'EndToEnd', range );
      start = dupSelection.text.length - selection.text.length;
      // End of hideous hack
      insertText = boilerRoom.createInsertText( content, selection.text );
      selection.text = insertText.text;
      boilerRoom.selectRange( textarea, start + insertText.selectionStart, start + insertText.selectionEnd );
    } else if ( typeof( textarea[0].selectionStart ) !== "undefined"  ) {
      start = textarea[0].selectionStart;
      end = textarea[0].selectionEnd;
      insertText = boilerRoom.createInsertText( content, textarea.val().substring( start, end ) );
      textarea.val( textarea.val().substring( 0, start ) +
                    insertText.text +
                    textarea.val().substring( end, textarea.val().length )
                  );
      boilerRoom.selectRange( textarea, start + insertText.selectionStart, start + insertText.selectionEnd );
    } else {
      insertText = boilerRoom.getInsertText( content );
      start = textarea.val().length;
      textarea.val( textarea.val() + insertText.text );
      boilerRoom.selectRange( textarea, start + insertText.selectionStart, start + insertText.selectionEnd );
    }
  },

  /**
   * Selects a given range of text in a textarea.
   * @param {element} textarea The textarea element to select text within.
   * @param {int} start The start position for the text to select.
   * @param {int} end The end position for the text to select.
   */
  selectRange : function( textarea, start, end ) {
    if ( typeof( document.selection ) !== "undefined" ) {
      var range = textarea.createTextRange();
      range.collapse( true );
      range.moveStart( 'character', Math.max( 0, start ) );
      range.moveEnd( 'character', Math.min( textarea.val().length - start, end - start ) );
      range.select();
    } else if ( typeof( textarea[0].selectionStart ) !== "undefined"  ) {
      textarea[0].selectionStart = Math.max( 0, start );
      textarea[0].selectionEnd = Math.min( textarea.val().length, end );
    }
  },

  /**
   * Retrieves the boilerplate content returned by an AJAX call to the boilerplate API.
   * @param {object} jsonData The json data returned by an AJAX call to the boilerplate API.
   * @returns {object} An object with possible boilerplate, openboilerplate, and closeboilerplate string properties.
   */
  getBoilerplateContent : function( jsonData ) {
    var content = {};
    if ( jsonData.boilerplate && jsonData.boilerplate['*'] ) {
      content.boilerplate = jsonData.boilerplate['*'];
    }
    if ( jsonData.openboilerplate && jsonData.openboilerplate['*'] ) {
      content.openboilerplate = jsonData.openboilerplate['*'];
    }
    if ( jsonData.closeboilerplate && jsonData.closeboilerplate['*'] ) {
      content.closeboilerplate = jsonData.closeboilerplate['*'];
    }
    return content;
  },

  /**
   * Creates the text to insert from returned boilerplate content from an AJAX call to the boilerplate API.
   * @param {object} jsonData The json data returned by an AJAX call to the boilerplate API.
   * @param {string} selectedText The text already selected, which becomes part of the returned text in some cases.
   * @returns {object} An object with the created text and a start and end position for the new selection.
   */
  createInsertText : function( jsonData, selectedText ) {
    var content = boilerRoom.getBoilerplateContent( jsonData );
    var insertText = { text : '', selectionStart : 0, selectionEnd : 0 };

    if ( typeof( content.openboilerplate ) !== "undefined" ) {
      insertText.text += content.openboilerplate;
      insertText.selectionEnd = insertText.selectionStart = content.openboilerplate.length;
    }
    if ( typeof( content.openboilerplate ) !== "undefined" || typeof( content.closeboilerplate ) !== "undefined" ) {
      if ( selectedText ) {
        insertText.text += selectedText;
        insertText.selectionEnd += selectedText.length;
      } else if ( typeof( content.boilerplate ) !== "undefined" ) {
        insertText.text += content.boilerplate;
        insertText.selectionEnd += content.boilerplate.length;
      }
    } else if ( typeof( content.boilerplate ) !== "undefined" ) {
      insertText.text += content.boilerplate;
    }
    if ( typeof( content.closeboilerplate ) !== "undefined" ) {
      insertText.text += content.closeboilerplate;
    }

    return insertText;
  },

  /**
   * Gets the text to insert from returned boilerplate content from an AJAX call to the boilerplate API.
   * @param {object} jsonData The json data returned by an AJAX call to the boilerplate API.
   * @returns {object} An object with the text to insert and a start and end position for the new selection.
   */
  getInsertText : function( jsonData ) {
    return boilerRoom.createInsertText( jsonData, "" );
  },

  /**
   * Opens the selected boilerplate for editing.
   */
  boilerplateEdit : function ( ) {
    var title = boilerRoom.getSelectedTitle();
    if ( title !== '' ) {
      var url = mw.config.get( 'wgScriptPath' ) +
                "/index.php?title=Boilerplate:" +
                encodeURIComponent( title ) +
                "&action=edit";
      var win = window.open( url, '_blank' );
      if ( win ) {
        win.focus();
      } else {
        window.location.href = url;
      }
    }
  },

  /**
   * Opens the Special:Boilerplate page.
   */
  boilerplateCreate : function ( ) {
    var url = mw.config.get( 'wgArticlePath' ).replace('$1', "Special:Boilerplate" )
    var win = window.open( url, '_blank' );
      if ( win ) {
        win.focus();
      } else {
        window.location.href = url;
      }
  }
};

window.boilerRoom = boilerRoom;

$( document ).ready( boilerRoom.initializeAjaxSelector );
