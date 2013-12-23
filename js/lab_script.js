//javascript document

$(document).ready(function (){
	$('#navbarWrapper').sticky({topSpacing: 0, wrapperClassName: 'stickyNavbarWrapper'});
	$('#mobileCommentForm').hide();
	
	$('.comment-reply-link').click(function(e) {
		e.preventDefault();
		//find the comment parent and change #comment_parent
		//value to reflect this
		var href = $(this).prop('href');
		var regex = /\?replytocom\=(\d+)/;
		var results = regex.exec(href);
		var replyTo = results[1];
		//move the form to the just after the "Reply" button
		moveTheForm( $(this), replyTo);
	})
	
	$('#AddYours').click(function(e) {
		moveTheForm( $(this), "0" );
	})
	
	resetHandlers();
	
	$('.carousel').carousel({
		interval: 4000,
		
	});
	
	$('.labMemberSummary').click(function(e) {
		var callingId = e.currentTarget.id;
		var regex = /memSum(\d+)/;
		var results = regex.exec(callingId);
		var memberNum = results[1];
		var targetId = '#labMemFull' + memberNum;
		var leftMargin = $(document).width() / 2 - $(targetId).width() / 2;
		$(targetId).css('left', leftMargin + 'px');
		$('#popupBckgd').fadeIn();
		$('#popupBckgd').height($(document).height())
		$(targetId).fadeIn();
	})
	$('#popupBckgd').click(function() {
		$('#popupBckgd').fadeOut();
		$('.popup').fadeOut();
	})
})

function moveTheForm(callingElement, replyTo) {
	//reset the comment text
	$('#comment').html("");
	//set the hidden #comment_parent field with the number of the
	//comment being replied to
	$('#comment_parent').val( replyTo );
	//show the form itself
	$('#commentForm').show();
	//hide the div containing the form
	var form = $('#mobileCommentForm').hide();
	//move it to the new location
	form.remove();	
	if (replyTo != "0") callingElement.parent().after(form);
	else callingElement.after(form);
	$('#mobileCommentForm').after('<div class = "clearfix"></div>');
	$('#submitOutcome').hide();
	form.slideDown();
	//unbind and rebind event handlers
	resetHandlers();
}


function resetHandlers() {
	$('#contactForm').submit(contactSubmit);
	
	//form submission
	$('#commentForm').unbind();
	$('#commentForm').submit(ajaxSubmit);
	
	//validate name
	$('#YourName').unbind();
	$('#YourName').blur(function() {
		validateName( $(this).val() )
	})
	
	//validate email
	$('#YourEmail').unbind();
	$('#YourEmail').blur(function() {
		validateEmail( $(this).val() )
	})
}

function contactSubmit(){
	var name = $('#YourName').val();
	var email = $('#YourEmail').val();
	if ( validateName(name) && validateEmail(email) ){
		return true;
	} else {
		return false;
	}
}

function ajaxSubmit(e) {
	e.preventDefault();
	var name = $('#YourName').val();
	var email = $('#YourEmail').val();
	if ( validateName(name) && validateEmail(email) ){
		var formData = $(this).serialize();
		var actionUrl = $(this).attr('action');
		$.ajax({ type: 'POST',
			data: formData,
			url: actionUrl,
			beforeSend: function(){
				$('body').css('cursor', 'wait');
			},
			error: function(){
				$('body').css('cursor', 'default');
				$('#submitOutcome').removeClass('alert-success');
				$('#submitOutcome').addClass('alert-danger');
				$('#submitOutcome').html('Lost conneciton with server.  Please try again in a moment');
				$('#submitOutcome').slideDown();
				alert('Lost conneciton with server.  Please try again in a moment');
			},
			success: function(){
				$('body').css('cursor', 'default');
				$('#submitOutcome').removeClass('alert-danger');
				$('#submitOutcome').addClass('alert-success');
				$('#commentForm').slideUp();
				$('#submitOutcome').html('Your comment has been logged and is awaiting moderation');
				$('#submitOutcome').slideDown();
			}
		})	
	}
}

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

