// JavaScript Document

$('document').ready(function () {

	$('.go_comments').on('click', function(event) {
			event.preventDefault();
			$("html, body").animate({ scrollTop: $('#comments').offset().top }, 1000);
		 
	});
	$('.go_comment_form').on('click', function(event) {
		event.preventDefault();
		
		$("html, body").animate({ scrollTop: $('#reply-form').offset().top }, 1000);
	 
	});
	
	$('.go_login_form').on('click', function(event) {
		event.preventDefault();
		
		$("html, body").animate({ scrollTop: $('#login_form_anchor').offset().top }, 1000);
	 
	});
	
	$('.go_inline_login').on('click', function(event) {
			event.preventDefault();
			var error = 0;
			var msg = '';
			
			if(!$('#inlineloginFrm_loginname').val()) {
				error = 1;
				msg = msg + 'You must enter a username.\n';
			}
			if(!$('#inlineloginFrm_password').val()) {
				error = 1;
				msg = msg + 'You must enter a password.\n';
			}
			if (error == 1) {
				$('.inline-login-error').text(msg);
			}else{
				processInlineLogin();
			}
	});	
	
	$('.go_login').on('click', function(event) {
			event.preventDefault();
			var error = 0;
			var msg = '';	
			if(!$('#loginFrm_loginname').val()) {
				error = 1;
				msg = msg + 'You must enter a username.\n';
			}
			if(!$('#loginFrm_password').val()) {
				error = 1;
				msg = msg + 'You must enter a password.\n';
			}
			if (error == 1) {
				$('.login-error').text(msg);
			}else{
				processLogin();
			}
	});	

	$('#blogCommentFrm_cancel').on('click', function(event) {
		resetErrors();
		$('#blogCommentFrm_comment_detail').val('');
	
	});	
	$('#blogCommentFrm_submit').on('click', function(event) {
		
		event.preventDefault();
		
		var error = name_error = email_error = url_error = comment_error = 0;
		var sName = $('#blogCommentFrm_username').val();	
		if($.trim(sName).length == 0) {
			$('.nbr').remove();
			$('#error-username').show().append('<br class="nbr" />');	
			name_error = 1;
		}else{
			$('.nbr').remove();
			$('#error-username').hide();
			name_error = 0;
		}
		error = error + name_error;
		var sEmail = $('#blogCommentFrm_email').val();
		if($.trim(sEmail).length == 0 || !validateEmail(sEmail)) {
			$('.ebr').remove();
			$('#error-email').show().append('<br class="ebr" />');	
			email_error = 1;
		}else{
			$('.ebr').remove();
			$('#error-email').hide();
			email_error = 0;
		}
		error = error + email_error;
		var sURL = $('#blogCommentFrm_site_url').val();
		if(sURL && $.trim(sURL).length > 0) {
			if(!validateURL(sURL)) {
				$('.ubr').remove();
				$('#error-site_url').show().append('<br class="ubr" />');	
				url_error = 1;
			}else{
				$('.ubr').remove();
				$('#error-site_url').hide();
				url_error = 0;
			}
		}else{
			$('.ubr').remove();
			$('#error-site_url').hide();
			url_error = 0;
		}
		error = error + url_error;
		var sComment = $('#blogCommentFrm_comment_detail').val();	
		if($.trim(sComment).length < 3) {
			$('.cbr').remove();
			$('#error-comment_detail').show().append('<br class="cbr" />');	
			comment_error = 1;
		}else{
			$('.cbr').remove();
			$('#error-comment_detail').hide();
			comment_error = 0;
		}
		error = error + comment_error;
	
		if(error > 0) {
			return false;
		}else{
			$("#blogCommentFrm").submit();
		}
	
		
	});
});

function validateEmail(sEmail) {
	var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
	if (filter.test(sEmail)) {
		return true;
	}else {
	return false;
	}
}

function validateURL(sURL) {
	if(/^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test(sURL)){
   		return true;
	}else {
		return false;
	}
}
	
function resetErrors() {
	//clear any errors on comment form
	$('.nbr').remove();
	$('#error-author').hide();
	$('.ebr').remove();
	$('#error-email').hide();
	$('.ubr').remove();
	$('#error-site_url').hide();
	$('.cbr').remove();
	$('#error-comment_detail').hide();	
}
function processLogin() {
	$.ajax({
		type: 'POST',
		url: '/index.php?rt=blog/proc_login',
		dataType: 'json',
		data: $("#loginFrm").serialize(),
		success: function (data) {
			if(data['error']) {
				$('.login-error').text(data['error']);
			}else if (data['message']) {
				window.location.reload();
			}
			
		}
	});
}

function processInlineLogin() {
	$.ajax({
		type: 'POST',
		url: '/index.php?rt=blog/proc_login',
		dataType: 'json',
		data: $("#inlineloginFrm").serialize(),
		success: function (data) {
			if(data['error']) {
				$('.inline-login-error').text(data['error']);
			}else if (data['message']) {
				$('.inline-login-error').hide();
				$('#inlineloginFrm').hide();
				$('#login-message').hide();
				$('#blogCommentFrm').show();
				$('#blogCommentFrm_username').val(data['blog_user_name']);
				$('#blogCommentFrm_email').val(data['email']);
				$('#blogCommentFrm_site_url').val(data['site_url']);
			}
			
		}
	});
}

var addComment = {
	moveForm : function(parentId, respondId, postId, primaryId, t_Text) {

		var t = this, div, comm = t.I('div-comment-'+parentId), respond = t.I(respondId), cancel = t.I('blogCommentFrm_cancel'); 
		var parent = t.I('blogCommentFrm_parent_id'), post = t.I('blogCommentFrm_blog_entry_id'), primary = t.I('blogCommentFrm_primary_comment_id', title = t.I('form-title'));
		var notify = t.I('notify_reply');

		if ( ! comm || ! respond)
			return;

		t.respondId = respondId;
		postId = postId || false;

		if ( ! t.I('temp-form-div') ) {
			div = document.createElement('div');
			div.id = 'temp-form-div';
			div.style.display = 'none';
			t.I('blogCommentFrm_comment_detail').value = '';
			resetErrors();
			if(notify) {
				notify.style.display = 'none';
			}
			respond.parentNode.insertBefore(div, respond);
		}

		comm.parentNode.insertBefore(respond, comm.nextSibling);
		if ( post && postId )
			post.value = postId;
		parent.value = parentId;
		primary.value = primaryId;
		var e_Text = title.innerHTML;
		title.innerHTML = t_Text;

		cancel.onclick = function() {
			var t = addComment, temp = t.I('temp-form-div'), respond = t.I(t.respondId);
			t.I('blogCommentFrm_comment_detail').value = '';
			if ( ! temp || ! respond )
				return;
				
			t.I('blogCommentFrm_parent_id').value = '0';
			temp.parentNode.insertBefore(respond, temp);
			temp.parentNode.removeChild(temp);
			parent.value = 0;
			primary.value = '';
			this.onclick = null;
			title.innerHTML = e_Text;
			t.I('blogCommentFrm_comment_detail').value = '';
			if(notify) {
				notify.style.display = '';
			}
			return false;
		};

		try { t.I('comment').focus(); }
		catch(e) {}

		return false;
	},

	I : function(e) {
		return document.getElementById(e);
	}
};