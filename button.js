var wdspq_text = '';
var wdspq_visual = false;

jQuery(function($) {
	var
	quote = $( '#wdspq-quote' ),
	attrib = $( '#wdspq-attrib' ),
	attrib_link = $( '#wdspq-attrib-link' ),
	align = $( '#wdspq-align' ),
	width = $( '#wdspq-width' ),
	doquote = $( '#wdspq-doquote' ),
	errors = $( '.wdspq-errors' );
	errorsdefault = $('p',errors).text(),
	allFields = $( [] ).add( quote ).add( align ).add( attrib ).add( attrib_link ).add( width ).add( doquote ),
	defaults = {};
	for (var i = allFields.length - 1; i >= 0; i--) {
		defaults[i] = $(allFields[i]).val();
	};

	function updateTips( t ) {
		errors
		.html( '<p>'+t+'</p>' )
		.addClass( 'ui-state-highlight' );
		setTimeout(function() {
			errors.removeClass( 'ui-state-highlight' );
		}, 1500 );
		$( '#wdspq-form' ).height('auto');
	}

	function checkNumber( o, n ) {
		var val = parseFloat( o.val() );

		if ( !isNumber( val ) || val < 1 ) {
			o.addClass( 'error' );
			updateTips( n + ' ' + window.wdspqtext.check_number );
			return false;
		} else {
			return true;
		}
	}

	function checkEmptyQuote() {
		wdspq_text = quote.val();
		if ( !wdspq_text ) {
			quote.addClass( 'error' );
			updateTips( window.wdspqtext.check_empty_quote );
			return false;
		} else {
			return true;
		}
	}

	function checkPreRequisite( o, pre ) {

		var val = o.val().trim();
		var preval = pre.val().trim();

		if ( val && !preval ) {

			var label = $('[for="'+ o.attr('id') +'"]').text();
			var prelabel = $('[for="'+ pre.attr('id') +'"]').text();

			o.addClass( 'error' );
			pre.addClass( 'error' );
			updateTips( '<b>'+ prelabel +'</b> '+ window.wdspqtext.required_pre +' <b>'+ label +'</b>' );
			return false;
		} else {
			return true;
		}
	}

	function isNumber(n) {
		return !isNaN(parseFloat(n)) && isFinite(n);
	}

	$( '#wdspq-form' ).dialog({
		'dialogClass': 'wp-dialog',
		'modal': true,
		'autoOpen': false,
		'draggable': false,
		'height': 'auto',
		'width': 395,
		'closeOnEscape': true,
		'buttons': {
			Cancel: function() {
				$( this ).dialog( 'close' );
			},
			'Insert Shortcode': function() {
				$('#wdspq-form .error').removeClass( 'error' );
				var bValid = true;
				allFields.removeClass( 'error' );

				bValid = bValid && checkNumber( width, 'Width' );
				bValid = bValid && checkPreRequisite( attrib_link, attrib );
				bValid = bValid && checkEmptyQuote();

				if ( bValid ) {
					var shortcode = '', val;
					var alignWhiteList = [ 'aligncenter', 'alignright' ]

					val = attrib.val().trim();
					if ( val )
						shortcode += ' attribution="'+ val +'"';
					val = val ? attrib_link.val().trim() : false;
					if ( val )
						shortcode += ' attribution_link="'+ val +'"';
					val = parseFloat( width.val().trim() );
					if ( isNumber( val ) && 52 !== val )
						shortcode += ' width="'+ val +'"';
					val = $.inArray(align.val(), alignWhiteList);
					if ( val !== -1 )
						shortcode += ' align="'+ alignWhiteList[val] +'"';
					if ( ! doquote.is(':checked') )
						shortcode += ' quote="false"';

					shortcode = '[pullquote'+ shortcode +']' + wdspq_text + '[/pullquote]'

					if ( wdspq_visual && wdspq_visual_editor ) {
						wdspq_visual_editor.execCommand('mceInsertContent', 0, shortcode);
					} else {
						QTags.insertContent(shortcode);
					}
					wdspq_text = '';
					$( this ).dialog( 'close' );
				}
			}
		},
		create: function() {
			var $this = $(this);
			// focus first button and bind enter to it
			$this.parent().find('.ui-dialog-buttonpane button:last-child').focus();
			$this.keypress(function(e) {
				if( e.keyCode == 13 ) {
					$this.parent().find('.ui-dialog-buttonpane button:last-child').click();
					return false;
				}
			});
		},
		close: function() {
			errors.html('<p>' + errorsdefault + '</p>');
			$('#wdspq-form .error').removeClass( 'error' );
			for (var i = allFields.length - 1; i >= 0; i--) {
				$(allFields[i]).val( defaults[i] );
			};
		}
	});

});

function launch_pq_dialog( isVisual ) {
	wdspq_visual = isVisual === true;

	if ( wdspq_text )
		setTimeout( function() { jQuery( '#wdspq-quote' ).val(wdspq_text).select() }, 100 );
	jQuery( '#wdspq-form' ).dialog( 'open' );
}

// text editor button
QTags.addButton( 'wdspq', window.wdspqtext.button_name, function(el, canvas) {
	// check for selection...
	wdspq_text = getSelectedText(canvas);
	launch_pq_dialog();
});

// helper function to get selected text from text editor
function getSelectedText(canvas){
	canvas.focus();
	if (document.selection) { // IE
		return document.selection.createRange().text;
	} else { // standards
		return canvas.value.substring(canvas.selectionStart, canvas.selectionEnd);
	}
}