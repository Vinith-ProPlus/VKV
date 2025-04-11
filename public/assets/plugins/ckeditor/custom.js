/**
 * @license Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
	CKEDITOR.tools.enableHtml5Elements( document );

// The trick to keep the editor in the sample quite small
// unless user specified own height.
CKEDITOR.config.height = 400;
CKEDITOR.config.width = 'auto';

var initCKEditor = ( function() {
	var wysiwygareaAvailable = isWysiwygareaAvailable(),
		isBBCodeBuiltIn = !!CKEDITOR.plugins.get( 'bbcode' );

	return function() {
		var editorElement = CKEDITOR.document.getById( 'editor' );

		// :(((
		if ( isBBCodeBuiltIn ) {
			editorElement.setHtml(
				'Hello world!\n\n' +
				'I\'m an instance of [url=https://ckeditor.com]CKEditor[/url].'
			);
		}
		let RootUrl=$('#txtRootUrl').val();
		// Depending on the wysiwygarea plugin availability initialize classic or inline editor.
		if ( wysiwygareaAvailable ) {
			CKEDITOR.replace( 'editor',{
				extraPlugins: 'image2,uploadimage',
				//removePlugins: 'image',
				image2_disableResizer : true,
				filebrowserUploadUrl: RootUrl+'admin/ckeditor/upload-image?command=QuickUpload&type=images',
      			filebrowserImageUploadUrl:RootUrl+'admin/ckeditor/upload-image?command=QuickUpload&type=images',
				 // Upload dropped or pasted images to the CKFinder connector (note that the response type is set to JSON).
				 uploadUrl: RootUrl+'admin/ckeditor/upload-image?command=QuickUpload&type=images&responseType=json',

				 // Reduce the list of block elements listed in the Format drop-down to the most commonly used.
				 format_tags: 'p;h1;h2;h3;pre',
				 // Simplify the Image and Link dialog windows. The "Advanced" tab is not needed in most cases.
				 removeDialogTabs: 'image:advanced;link:advanced',
		   
				 height: 450,
				 removeButtons: 'PasteFromWord'

			} );
			CKEDITOR.on('dialogDefinition', function(ev) {
				var dialogName = ev.data.name;
				var dialogDefinition = ev.data.definition;
				if (dialogName == 'image2') {
			
					var infoTab = dialogDefinition.getContents( 'info' );
			
					infoTab.get('width').validate = function() {
						return true; //more advanced validation rule should be used here
					}
			
					infoTab.get('height').validate = function() {
						return true; //more advanced validation rule should be used here
					}
				}
			});
		} else {
			editorElement.setAttribute( 'contenteditable', 'true' );
			CKEDITOR.inline( 'editor' );

			// TODO we can consider displaying some info box that
			// without wysiwygarea the classic editor may not work.
		}
		//CKEDITOR.plugins.imageresize.resizeAll(CKEDITOR.editor)
	};

	function isWysiwygareaAvailable() {
		// If in development mode, then the wysiwygarea must be available.
		// Split REV into two strings so builder does not replace it :D.
		if ( CKEDITOR.revision == ( '%RE' + 'V%' ) ) {
			return true;
		}

		return !!CKEDITOR.plugins.get( 'wysiwygarea' );
	}
} )();

// %LEAVE_UNMINIFIED% %REMOVE_LINE%
