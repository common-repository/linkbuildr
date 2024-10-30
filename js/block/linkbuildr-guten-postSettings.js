( function( wp ) {
	var registerPlugin             		   = wp.plugins.registerPlugin;
	var PluginPostStatusInfo       		   = wp.editPost.PluginPostStatusInfo;
	var PluginPrePublishPanel      		   = wp.editPost.PluginPrePublishPanel;
	var el                         		   = wp.element.createElement;
	var Checkbox                   		   = wp.components.CheckboxControl;
	var PanelBody                  		   = wp.components.PanelBody;
	var withSelect                 		   = wp.data.withSelect;
	var withDispatch               		   = wp.data.withDispatch;
	var compose                    		   = wp.compose.compose;
	const linkbuildr_send_email_on_publish = 'linkbuildr_send_email_on_publish';

	const reviseData = (oldData, newData) => Object
		.keys( newData )
		.reduce(
			(prev, key) => {
            if (oldData[key] === newData[key]) {
					return prev;
				}
            return {
					...prev,
					[key]: newData[key],
				};
			},
			{}
		);

	const MetaCheckbox = compose(
		withSelect(
			function( select, props ) {
					const postMeta    = select( 'core/editor' ).getEditedPostAttribute( 'meta' );
					const oldPostMeta = select( 'core/editor' ).getCurrentPostAttribute( 'meta' );

					return {
						meta: { ...oldPostMeta, ...postMeta },
						oldMeta: oldPostMeta,
				}
			}
		),
		withDispatch(
			function( dispatch, props ) {
					return {
						onUpdateLBSendOnPublish: function( value, newMeta, oldMeta ) {
							const meta = {
								...reviseData( oldMeta, newMeta ),
								[linkbuildr_send_email_on_publish]: value,
							};
							dispatch( "core/editor" ).editPost( { meta } );
						}
				}
			}
		),
	)(
		function( props ) {
				return el(
					Checkbox,
					{
						label: 'Send Linkbuildr Emails on Publish',
						checked: ! ! props.meta[linkbuildr_send_email_on_publish],
						onChange: function() {
							 props.onUpdateLBSendOnPublish( ! props.meta[linkbuildr_send_email_on_publish], props.meta, props.oldMeta )
						},
					}
				);
		}
	);

	var LinkbuildrNotice = compose(
		withSelect(
			function( select, props ) {
					return {
						currentPostId: select( 'core/editor' ).getCurrentPostId(),
				}
			}
		),
		withSelect(
			function( select, props ) {
					return {
						postNotificationData: select( 'linkbuildr' ).receivePostNoficationData( null, props.currentPostId ),
				}
			}
		),
	)(
		function( props ) {
				LinkbuildrHandleNotice( props.postNotificationData );
				return null;
		}
	);

	registerPlugin(
		'linkbuildr-post-settings',
		{
			render: function() {
				return el(
					PluginPostStatusInfo,
					{
						className: 'lb-guten-post-settings'
					},
					el( MetaCheckbox ),
					el( LinkbuildrNotice )
				);
			}
		}
	);

	var LinkbuildrPluginPrePublishPanel = compose(
		withSelect(
			function( select, props ) {
					return {
						lbPostEditMeta: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ linkbuildr_send_email_on_publish ],
				}
			}
		),
	)(
		function( props ) {
				var LinkbuildrPanelBodyTitle = ['Linkbuildr Emails:', el(
					'span',
					{
						className: "editor-post-publish-panel__link",
						key: "label"
					},
					props.lbPostEditMeta ? 'Yes' : 'No'
				)];

				return el(
					PanelBody,
					{
						title: LinkbuildrPanelBodyTitle,
						initialOpen: false
					},
					el( MetaCheckbox ),
				)
		}
	);

	registerPlugin(
		'linkbuildr-pre-publish-panel',
		{
			render: function() {
				return el(
					PluginPrePublishPanel,
					{ className: 'lb-pre-publish-panel' },
					el( LinkbuildrPluginPrePublishPanel )
				);
			}
		}
	);
} )( window.wp );
