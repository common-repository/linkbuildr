jQuery(
	function($){
		$( document ).ready(
			function($) {
				$( '#publish' ).on(
					'click',
					function( event ) {
						var ckb = $( "#linkbuildr_send_email_on_publish_post" ).is( ':checked' );
						if (ckb) {
							if ( $( this ).attr( 'name' ) !== 'publish' || $( this ).attr( 'value' ) === 'Schedule' ) {
								return;
							}
							if ( ! confirm( linkbuildr_popup_message.msg ) ) {
								event.preventDefault();
							}
						}
					}
				);
			}
		);
	}
);
