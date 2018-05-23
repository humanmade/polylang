jQuery( document ).ready( function ( $ ) {
	let transitionTimeout;

	// languages list table
	// accessibility to row actions on focus
	// mainly copy paste of WP code from common.js
	$( 'table.languages' ).on( { // restricted to languages list table
		focusin: function () {
			clearTimeout( transitionTimeout );
			focusedRowActions = $( this ).find( '.row-actions' );
			// transitionTimeout is necessary for Firefox, but Chrome won't remove the CSS class without a little help.
			$( '.row-actions' ).not( this ).removeClass( 'visible' );
			focusedRowActions.addClass( 'visible' );
		},
		focusout: function () {
			// Tabbing between post title and .row-actions links needs a brief pause, otherwise
			// the .row-actions div gets hidden in transit in some browsers ( ahem, Firefox ).
			transitionTimeout = setTimeout( function () {
				focusedRowActions.removeClass( 'visible' );
			}, 30 );
		},
	}, 'tr' ); // acts on the whole tr instead of single td as we have actions links in several columns

	// extends selectmenu to add flags in menu items
	$.widget( 'custom.iconselectmenu', $.ui.selectmenu, {
		_renderItem: function ( ul, item ) {
			let li = $( '<li>', { text: item.label } );

			if ( item.value ) {
				$( '<img>', {
					src: pll_flag_base_url + item.value + '.png',
					'class': 'ui-icon',
				} ).appendTo( li );
			}

			return li.appendTo( ul );
		},
	} );

	// allows to display the flag for the selected menu item
	function add_icon( event, ui ) {
		let value = $( this ).val();
		if ( value ) {
			let txt = $( this ).iconselectmenu( 'widget' ).children( ':last' );
			let img = $( '<img class="ui-icon" >' ).appendTo( txt );
			img.attr( 'src', pll_flag_base_url + value + '.png' );
		}
	}

	// overrides the flag dropdown list with our customized jquery ui selectmenu
	$( '#flag_list' ).iconselectmenu( {
		create: add_icon,
		select: add_icon,
	} );

	// languages form
	// fills the fields based on the language dropdown list choice
	$( '#lang_list' ).change( function () {
		let value = $( this ).val().split( ':' );
		let selected = $( 'select option:selected' ).text().split( ' - ' );
		$( '#lang_slug' ).val( value[0] );
		$( '#lang_locale' ).val( value[1] );
		$( 'input[name="rtl"]' ).val( [ value[2] ] );
		$( '#lang_name' ).val( selected[0] );
		$( '#flag_list option[value="' + value[3] + '"]' ).attr( 'selected', 'selected' );

		// recreate the jquery ui selectmenu
		$( '#flag_list' ).iconselectmenu( 'destroy' ).iconselectmenu( {
			create: add_icon,
			select: add_icon,
		} )
	} );

	// strings translations
	// save translations when pressing enter
	$( '.translation input' ).keypress( function ( event ){
		if ( event.keyCode === 13 ) {
			event.preventDefault();
			$( '#submit' ).click();
		}
	} );

	// settings page
	// click on configure link
	$( '#the-list' ).on( 'click', '.configure>a', function (){
		$( '.pll-configure' ).hide().prev().show();
		$( this ).closest( 'tr' ).hide().next().show();
		return false;
	} );

	// cancel
	$( '#the-list' ).on( 'click', '.cancel', function (){
		$( this ).closest( 'tr' ).hide().prev().show();
	} );

	// save settings
	$( '#the-list' ).on( 'click', '.save', function (){
		let tr = $( this ).closest( 'tr' );
		let parts = tr.attr( 'id' ).split( '-' );

		let data = {
			action: 'pll_save_options',
			pll_ajax_settings: true,
			module: parts[parts.length - 1],
			_pll_nonce: $( '#_pll_nonce' ).val(),
		}

		data = tr.find( ':input' ).serialize() + '&' + $.param( data );

		$.post( ajaxurl, data, function ( response ) {
			let res = wpAjax.parseAjaxResponse( response, 'ajax-response' );
			$.each( res.responses, function () {
				switch ( this.what ) {
					case 'license-update':
						$( '#pll-license-' + this.data ).replaceWith( this.supplemental.html );
						break;
					case 'success':
						tr.hide().prev().show(); // close only if there is no error
					case 'error':
						$( '.settings-error' ).remove(); // remove previous messages if any
						$( 'h1' ).after( this.data );

						// Make notices dismissible
						// copy paste of common.js from WP 4.2.2
						$( '.notice.is-dismissible' ).each( function () {
							let $this = $( this ),
								$button = $( '<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>' ),
								btnText = commonL10n.dismiss || '';

							// Ensure plain text
							$button.find( '.screen-reader-text' ).text( btnText );

							$this.append( $button );

							$button.on( 'click.wp-dismiss-notice', function ( event ) {
								event.preventDefault();
								$this.fadeTo( 100, 0, function () {
									$( this ).slideUp( 100, function () {
										$( this ).remove();
									} );
								} );
							} );
						} );
						break;
				}
			} );
		} );
	} );

	// act when pressing enter or esc in configurations
	$( '.pll-configure' ).keypress( function ( event ){
		if ( event.keyCode === 13 ) {
			event.preventDefault();
			$( this ).find( '.save' ).click();
		}

		if ( event.keyCode === 27 ) {
			event.preventDefault();
			$( this ).find( '.cancel' ).click();
		}
	} );

	// settings URL modifications
	// manages visibility of fields
	$( 'input[name=\'force_lang\']' ).change( function () {
		function pll_toggle( a, test ) {
			test ? a.show() : a.hide();
		}

		let value = $( this ).val();
		pll_toggle( $( '#pll-domains-table' ), value == 3 );
		pll_toggle( $( '#pll-hide-default' ), value < 3 );
		pll_toggle( $( '#pll-rewrite' ), value < 2 );
		pll_toggle( $( '#pll-redirect-lang' ), value < 2 );
	} );

	// settings license
	// deactivate button
	$( '.pll-deactivate-license' ).on( 'click', function () {
		let data = {
			action: 'pll_deactivate_license',
			pll_ajax_settings: true,
			id: $( this ).attr( 'id' ),
			_pll_nonce: $( '#_pll_nonce' ).val(),
		}
		$.post( ajaxurl, data, function ( response ){
			$( '#pll-license-' + response.id ).replaceWith( response.html );
		} );
	} );

} );
