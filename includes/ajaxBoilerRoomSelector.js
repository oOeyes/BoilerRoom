/**
 * Displays the appropriate page exists message for the AJAX
 * selector
*/
function displayPageExistsMsg () {
  document.getElementById('boilerRoomMessage').innerHTML =
    wgbrPageExistsAjaxMsg;
}

/**
 * Returns the title of the currently selected boilerplate, or
 * an empty string if none is selected.
*/
function getSelectedTitle() {
  var boilerRoomSelect = document.getElementById('boilerRoomSelect');
  if ( boilerRoomSelect.selectedIndex > -1 ) {
    var selectedOption = boilerRoomSelect.options[boilerRoomSelect.selectedIndex];
    return selectedOption.text;
  } else {
    return '';
  }
}

/**
 * Calls into the BoilerRoom extension to fetch the boilerplate
 * text and then directs it to the indicated target function.
*/
function boilerplateFetch( target ) {
  if ( confirmAction( target ) ) {
    var title = getSelectedTitle()
    if ( title != '' ) {
      if ( mw != null && mw.util != null && mw.util.wikiScript ) {
        $.get(
	  mw.util.wikiScript(), {
	    action: 'ajax', 
	    rs: 'BoilerRoomSelector::ajaxGetBoilerplateContent',
	    rsargs: [getSelectedTitle()]
	  }
        );
      } else {
        sajax_do_call( 'BoilerRoomSelector::ajaxGetBoilerplateContent', 
		       [getSelectedTitle()], 
		       target 
	  	     );
      }
    }
  }
}

/**
 * Raises a confirmation dialog for replacement and returns the result.
 * Simply returns true for prepending and appending.
*/
function confirmAction( target ) {
  if ( target == boilerplateInsert )
    return true;
  else if ( target == boilerplatePrepend )
    return true;
  else if ( target == boilerplateAppend )
    return true;
  else if ( target == boilerplateReplace )
    return confirm("Are you sure you want to replace the existing text?\n\n" +
                   "You may not be able to undo this action."
                  );
  else
    return false;  // shouldn't get here.
}

/**
 * Prepends the indicated content to the content within the edit box.
*/
function boilerplateInsert( content ) {
  var editarea = document.getElementById("wpTextbox1");
  insertAtCursor( editarea, content.responseText );
  document.getElementById('boilerRoomMessage').innerHTML =
    wgbrInsertMsg.replace('$1', getSelectedTitle() );
}

/**
 * Prepends the indicated content to the content within the edit box.
*/
function boilerplatePrepend( content ) {
  var editarea = document.getElementById("wpTextbox1");
  editarea.value = content.responseText + editarea.value;
  document.getElementById('boilerRoomMessage').innerHTML =
    wgbrPrependMsg.replace('$1', getSelectedTitle() );
}

/**
 * Appends the indicated content to the content within the edit box.
*/
function boilerplateAppend( content ) {
  var editarea = document.getElementById("wpTextbox1");
  editarea.value = editarea.value + content.responseText;
  document.getElementById('boilerRoomMessage').innerHTML =
    wgbrAppendMsg.replace('$1', getSelectedTitle() );
}

/**
 * Replaces the content within the edit box with the boilerplate content.
*/
function boilerplateReplace( content ) {
  var editarea = document.getElementById("wpTextbox1");
  editarea.value = content.responseText;
  document.getElementById('boilerRoomMessage').innerHTML =
    wgbrReplaceMsg.replace('$1', getSelectedTitle() );
}

 /**
  * Performs the steps needed to insert text at the cursor position in a
  * textarea.
 */
function insertAtCursor( textarea, text ) {
  if (document.selection) {
    textarea.focus();
    selection = document.selection.createRange();
    selection.text = text;
  } else if (textarea.selectionStart || textarea.selectionStart == '0') {
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    textarea.value = textarea.value.substring(0, start) + text + 
      textarea.value.substring(end, textarea.value.length);
  } else {
    textarea.value += text;
  }
}