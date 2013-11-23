//javascript document

$(document).ready(function (){
	$('#navbarWrapper').sticky({topSpacing: 0, wrapperClassName: 'stickyNavbarWrapper'});
	$('#mobileCommentForm').hide();
	
	
	$('.comment-reply-link').click(function() {
		var form = $('#mobileCommentForm').remove();
		form.slideDown();	
		//$(this).after('<div class = "clearfix></div>');
		$(this).parent().after(form);
		$('#mobileCommentForm').after('<div class = "clearfix"></div>');
		form.slideDown();
		return false;
	})
	
	$('#AddYours').click(function() {
		var form = $('#mobileCommentForm').remove();
		form.slideDown();	
		$(this).after(form);
		$('#mobileCommentForm').after('<div class = "clearfix"></div>');
		form.slideDown();
	})
	
	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	FORM VALIDATION
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	
	//validate name
	$('.YourName').blur(function() {
		validateName( $(this).val() )
	})
	//validate email
	$('.YourEmail').blur(function() {
		validateEmail( $(this).val() )
	})
})

function validateName(text){
	var warningBox = $('#warningYourName');
	if (text == "") {
		toggle_warning('You must enter your name', warningBox);
		return false;
	} else {
		toggle_warning('', warningBox);
		return true;
	}
}

function validateEmail(text){
	var warningBox = $('#warningYourEmail');
	var message = '';
	if (text == '') {
		toggle_warning('You must enter an email address', warningBox);
		return false;
	}
	else if (!((text.indexOf('.') > 0) && (text.indexOf('@') > 0)) || /[^a-zA-Z0-9.@_-]/.test(text)) {
		toggle_warning('The email address you entered is invalid', warningBox);
		return false;
	} else {
		toggle_warning('', warningBox);
		return true;
	}
}

function toggle_warning(message, element) {
	if (message == ''){
		element.hide();
		$('#submit').removeClass('btn-danger').addClass('btn-success');
	} else {
		element.show();
		$('#submit').removeClass('btn-success').addClass('btn-danger');
		element.addClass('alert');
		element.addClass('alert-danger');
		element.html(message);
	}
}