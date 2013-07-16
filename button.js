var nbcpq_text = '';
var nbcpq_visual = false;

jQuery(function($) {
	var
	quote = $( '#nbcpq-quote' ),
	attrib = $( '#nbcpq-attrib' ),
	attrib_link = $( '#nbcpq-attrib-link' ),
	align = $( '#nbcpq-align' ),
	width = $( '#nbcpq-width' ),
	doquote = $( '#nbcpq-doquote' ),
	errors = $( '.nbcpq-errors' );
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
		$( '#nbcpq-form' ).height('auto');
	}

	function checkNumber( o, n ) {
		var val = parseFloat( o.val() );

		if ( !isNumber( val ) || val < 1 ) {
			o.addClass( 'error' );
			updateTips( n + ' ' + window.nbcpqtext.check_number );
			return false;
		} else {
			return true;
		}
	}

	function checkEmptyQuote() {
		nbcpq_text = quote.val();
		if ( !nbcpq_text ) {
			quote.addClass( 'error' );
			updateTips( window.nbcpqtext.check_empty_quote );
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
			updateTips( '<b>'+ prelabel +'</b> '+ window.nbcpqtext.required_pre +' <b>'+ label +'</b>' );
			return false;
		} else {
			return true;
		}
	}

	function isNumber(n) {
		return !isNaN(parseFloat(n)) && isFinite(n);
	}

	$( '#nbcpq-form' ).dialog({
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
				$('#nbcpq-form .error').removeClass( 'error' );
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

					shortcode = '[nbc-pq'+ shortcode +']' + nbcpq_text + '[/nbc-pq]'

					if ( nbcpq_visual && nbcpq_visual_editor ) {
						nbcpq_visual_editor.execCommand('mceInsertContent', 0, shortcode);
					} else {
						QTags.insertContent(shortcode);
					}
					nbcpq_text = '';
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
			$('#nbcpq-form .error').removeClass( 'error' );
			for (var i = allFields.length - 1; i >= 0; i--) {
				$(allFields[i]).val( defaults[i] );
			};
		}
	});

});

function launch_pq_dialog( isVisual ) {
	nbcpq_visual = isVisual === true;

	if ( nbcpq_text )
		setTimeout( function() { jQuery( '#nbcpq-quote' ).val(nbcpq_text).select() }, 100 );
	jQuery( '#nbcpq-form' ).dialog( 'open' );
}

// text editor button
QTags.addButton( 'nbcpq', window.nbcpqtext.button_name, function(el, canvas) {
	// check for selection...
	nbcpq_text = getSelectedText(canvas);
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