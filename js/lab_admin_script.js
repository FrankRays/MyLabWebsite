//javascript document

//boolean variable to store whether labAdminUserPic popup div 
//has retrieved the pictures in will store
gotPics = false;

jQuery(document).ready(function( $ ) {
	//select element that will display only if JS is disabled
	$('#labAdminPicSelect').hide();
	
	//current value of select list is used to decide which image to show
	var src = $('#labAdminPicSelect').val();
	updateUserPic(src);
	$('#labAdminUserPic').show();
	
	//toggle appearance of text that reads "Change pic for this user"
	$('#labAdminUserPic').mouseenter(function() {
		$('#labAdminChangePic').show();
		$('#labAdminUserPic').css('cursor', 'pointer');
	})
	$('#labAdminUserPic').mouseleave(function() {
		$('#labAdminChangePic').hide();
		$('#labAdminUserPic').css('cursor', 'default');
	})
	$('#labAdminChangePic').mouseenter(function() {
		$('#labAdminChangePic').show().css('cursor', 'pointer');
		$('#labAdminUserPic').css('cursor', 'pointer');
	})
	
	//on click, display popup of pics stored in the media library.
	//If we haven't added the pics to the div, add them now
	$('#labAdminUserPic').click(function() {
		popUp($('#labAdminPics'))
		if (!gotPics) {
			getPics();
		}
	})
	
	
	/*~~~~~~~~~~~~~~~~~~~~~~~~~~
	jQ function, no return value, no args
	Called by: main document.ready, listener on #labAdminUserPic 
	Calls: updateUserPic, popDown
	Finds all the items in the select list #labAdminPicSelect, extracts
	their values, and uses those values as srcs to add to the popup div
	#labAdminPics. Assigns a click listener to each img which will hide 
	the popup div and call updateUserPic.
	While the funciton is running, the cursor changes to a wait symbol.
	~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	function getPics() {
		$('body').css('cursor', 'wait');
		var pics = $('#labAdminPicSelect').children().each(function (){
			var pic = $(this).val();
			var html = '<img src = "' + pic + '" class = "labAdminChoosePic" />';
			$('#labAdminPics').append(html);
		})
		$('.labAdminChoosePic').each(function() {
			$(this).click(function(event) {
				var src = event.currentTarget.src
				updateUserPic(src);
				popDown();
			})
		})
		$('body').css('cursor', 'default');		
		gotPics = true;
	}
	
	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	updateUserPics(src)
	Called by: getPics (listener on img .labAdminChoosePic)
	src = string, src of the img #labAdminUserPic, showing
	the currently selected user pic.
	Also updates the currently selected option in the select 
	#labAdminPicSelect
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	function updateUserPic(src) {
		$('#labAdminUserPic').attr('src', src);
		$('#labAdminPicSelect option').each(function() {
			if ( $(this).attr('value') == src ) {
				$(this).attr('selected', 'selected');
			} else {
				$(this).removeAttr('selected');
			}
		})
	}
	
	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	popUp(popup)
	popup = jQ object, the popup div which should be shown
	Calls: popDown
	Called by: main, listener on #labAdminUserPic
	Shows a popup div
	Also shows the background, attaches a click listener
	which will hide the popup when clicked
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	function popUp(popup) {
		$('#popupBckgd').height($(document).height())
		$('#popupBckgd').fadeIn();
		$('#popupBckgd').click(popDown);
		popup.fadeIn();
	}
	
	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	popDown, no return, no args
	Hides a popup div and background div covering the main body
	of the page.
	Called by: popUp (#popupBckgd click listener)
	getPics (#labAdminChoosePic event listener)
	
	
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	function popDown() {
		$('.popup').fadeOut();
		$('#popupBckgd').fadeOut();
		$('#popupBckgd').unbind();
	}
})

