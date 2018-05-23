jQuery( document ).ready( function ( $ ) {
	// biography
	// FIXME there is probably a more efficient way to do this
	let td = $( '#description' ).parent();
	let d = $( '#description' ).clone();
	let span = td.children( '.description' ).clone();
	td.children().remove();

	$( '.biography' ).each( function (){
		lang = $( this ).attr( 'name' ).split( '___' );
		desc = d.clone();
		desc.attr( 'name', 'description_' + lang[0] );
		desc.html( $( this ).val() );
		td.append( '<div>' + lang[1] + '</div' );
		td.append( desc );
	} );

	td.append( '<br />' );
	td.append( span );
} );
