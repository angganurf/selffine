var URL = window.URL || window.webkitURL;

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else {
        root.capture_video_frame = factory();
    }
	}(this, function () {
    return function capture_video_frame(video, format) {
        if (typeof video === 'string') {
            video = document.getElementById(video);
        }

        format = format || 'jpeg';

        if (!video || (format !== 'png' && format !== 'jpeg')) {
            return false;
        }

        var canvas = document.createElement("canvas");

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        canvas.getContext('2d').drawImage(video, 0, 0);


        var dataUri = canvas.toDataURL('image/' + format);
        var data = dataUri.split(',')[1];
        var mimeType = dataUri.split(';')[0].slice(5)

        var bytes = window.atob(data);
        var buf = new ArrayBuffer(bytes.length);
        var arr = new Uint8Array(buf);

        for (var i = 0; i < bytes.length; i++) {
            arr[i] = bytes.charCodeAt(i);
        }

        var blob = new Blob([ arr ], { type: mimeType });
        return { blob: blob, dataUri: dataUri, format: format };
    };
}));


function capture_canvas_image(id, format) {
    if (typeof id === 'string') {
        var canvas = document.getElementById(id);
    }

    format = format || 'jpeg';

    if ((format !== 'png' && format !== 'jpeg')) {
        return false;
    }

    canvas.getContext('2d');


    var dataUri = canvas.toDataURL('image/' + format);
    var data = dataUri.split(',')[1];
    var mimeType = dataUri.split(';')[0].slice(5)

    var bytes = window.atob(data);
    var buf = new ArrayBuffer(bytes.length);
    var arr = new Uint8Array(buf);

    for (var i = 0; i < bytes.length; i++) {
        arr[i] = bytes.charCodeAt(i);
    }

    var blob = new Blob([ arr ], { type: mimeType });
    return { blob: blob, dataUri: dataUri, format: format };
}

function Wo_progressIconLoader(e) {e.each(function(){return progress_icon_elem=$(this).find("i.progress-icon"),default_icon=progress_icon_elem.attr("data-icon"),hide_back=!1,1==progress_icon_elem.hasClass("hidde")&&(hide_back=!0),1==$(this).find("i.fa-spinner").length?(progress_icon_elem.removeClass("fa-spinner").removeClass("fa-spin").addClass("fa-"+default_icon),1==hide_back&&progress_icon_elem.hide()):progress_icon_elem.removeClass("fa-"+default_icon).addClass("fa-spinner fa-spin").show(),!0})}function Wo_StartBar(){$(".loader").css("display","block")}function Wo_FinishBar(){$(".loader").css("display","none")}$(document).ready(function(){$(".nav-footer-toggle").on("click",function(e){e.preventDefault(),$(this).parent().toggleClass("Wide-Footer"),$(".nav-footer-toggle i").toggleClass("fa-arrow-circle-up fa-arrow-circle-down")})});
	function Wo_CheckForCallAnswer(id,type = 'video') {
		url = link('twilio/check_for_answer');
		if (type == 'audio') {
			url = link('twilio/check_for_audio_answer');
		}
		$.get(url + '?id='+id , function (data1) {
			if (data1.status == 200) {
				clearTimeout(window.checkcalls);
				$('#calling-modal').find('.modal-title').html('<i class="fa fa fa-video-camera"></i> ' + data1.text_answered);
				$('#calling-modal').find('.modal-body p').text(data1.text_please_wait);
				if (type == 'audio') {
					Wo_PlayAudioCall('stop');
			        setTimeout(function () {
			            $( '#calling-modal' ).remove();
			            $( '.modal-backdrop' ).remove();
			            $( 'body' ).removeClass( "modal-open" );
			            $('body').append(data1.calls_html);
			            $('#re-talking-modal').modal({
			                show: true
			            });
			        }, 3000);
					return false;
				}
				else{
					setTimeout(function () {
						window.location.href = data1.url;
					}, 1000);
					return false;
				}
			} else if (data1.status == 400) {
				clearTimeout(window.checkcalls);
				Wo_PlayAudioCall('stop');
				$('#calling-modal').find('.modal-title').html('<i class="fa fa fa-times"></i> ' + data1.text_call_declined);
				$('#calling-modal').find('.modal-body p').text(data1.text_call_declined_desc);
			}
			window.checkcalls = setTimeout(function () {
				Wo_CheckForCallAnswer(id,type);
			}, 2000);
		});
	}
	function Wo_CheckForAudioCallAnswer(id) {
		$.get(window.ajax + 'chat/check_for_audio_answer?id='+id, function (data1) {
			if (data1.status == 200) {
				clearTimeout(window.checkcalls);
				$('#calling-modal').find('.modal-title').html('<i class="fa fa fa-phone"></i> ' + data1.text_answered);
				$('#calling-modal').find('.modal-body p').text(data1.text_please_wait);
				Wo_PlayAudioCall('stop');
				setTimeout(function () {
					$( '#calling-modal' ).remove();
					$( '.modal-overlay' ).remove();
					$( 'body' ).removeClass( "modal-open" );
					$('body').append(data1.calls_html);
					$('#re-talking-modal').modal({
						show: true
					});
				}, 3000);
				return false;
			} else if (data1.status == 400) {
				clearTimeout(window.checkcalls);
				Wo_PlayAudioCall('stop');
				$('#calling-modal').find('.modal-title').html('<i class="fa fa fa-times"></i> ' + data1.text_call_declined);
				$('#calling-modal').find('.modal-body p').text(data1.text_call_declined_desc);
			} else {
				window.checkcalls = setTimeout(function () {
					Wo_CheckForAudioCallAnswer(id);
				}, 2000);
			}
		});
	}
	function Wo_AnswerCall(id, url, type) {
		type1 = 'video';
		if (type == 'video') {
			type1 = 'video';
		} else if (type == 'audio') {
			type1 = 'audio';
		}
		Wo_progressIconLoader($('#re-calling-modal').find('.answer-call'));
		let _url = link('twilio/answer_call');
		$.get(_url + '?id='+id+'&type='+type1, function (data) {
			Wo_PlayVideoCall('stop');
			if (type1 == 'video') {
				if (data.status == 200) {
					window.location.href = url;
				}
			} else {
				$('#re-calling-modal').remove();
				$('.modal-overlay').remove();
				$('body').removeClass( "modal-open" );
				$('body').append(data.calls_html);
				$('#re-talking-modal').modal({
					show: true
				});
			}
			Wo_progressIconLoader($('#re-calling-modal').find('.answer-call'));
		});
	}
	function Wo_DeclineCall(id, url, type) {
		type1 = 'video';
		if (type == 'video') {
			type1 = 'video';
		} else if (type == 'audio') {
			type1 = 'audio';
		}
		Wo_progressIconLoader($('#re-calling-modal').find('.decline-call'));
		let _url = link('twilio/decline_call');
		$.get(_url + '?id='+id+'&type='+type1, function (data) {
			if (data.status == 200) {
				Wo_PlayVideoCall('stop');
				$( '#re-calling-modal' ).remove();
				$( '.modal-overlay' ).remove();
				$('.modal-backdrop').remove();
				$( 'body' ).removeClass( "modal-open" );
				document.title = document_title;
			}
		});
	}
	function Wo_CloseCall(id) {
		Wo_progressIconLoader($('#re-talking-modal').find('.decline-call'));
		$.get(window.ajax + 'chat/close_call?id='+id, function (data) {
			if (data.status == 200) {
				$( '#re-talking-modal' ).remove();
				$( '.modal-overlay' ).remove();
				$( 'body' ).removeClass( "modal-open");
			}
		});
	}
	function Wo_CancelCall() {
		Wo_progressIconLoader($('#calling-modal').find('.cancel-call'));
		let url = link('twilio/cancel_call');
		$.get(url, function (data) {
			if (data.status == 200) {
				Wo_PlayAudioCall('stop');
				$( '#calling-modal' ).remove();
				$( '.modal-overlay' ).remove();
				$('.modal-backdrop').remove();
				$( 'body' ).removeClass( "modal-open" );
			}
		});
	}
	function Wo_GenerateVideoCall(user_id1) {
		let user_id2 = $('#vxd').val();
		let url = link('twilio/create_new_video_call');
		$.get( url + '?new=true&user_id1='+user_id1+'&user_id2='+user_id2, function(data, status){
			if( data.status == 200 ){
				$('body').append(data.html);
				$('#calling-modal').modal({dismissible: false});
				$('#calling-modal').modal('show');
				
				window.checkcalls = setTimeout(function () {
					Wo_CheckForCallAnswer(data.id);
				}, 2000);
				setTimeout(function() {
					$('#calling-modal').find('.modal-title').html('<i class="fa fa fa-video-camera"></i> ' + data.text_no_answer);
					$('#calling-modal').find('.modal-body p').text(data.text_please_try_again_later);
					clearTimeout(window.checkcalls);
					Wo_PlayAudioCall('stop');
				}, 43000);
				Wo_PlayAudioCall('play');
			}
		});

	}
	function Wo_GenerateAudioCall(user_id1) {
		let user_id2 = $('#axd').val();
		let url = link('twilio/create_new_audio_call');
		$.get( url + '?new=true&user_id1='+user_id1+'&user_id2='+user_id2, function(data, status){
			if( data.status == 200 ){
				$('body').append(data.html);
				$('#calling-modal').modal({dismissible: false});
				$('#calling-modal').modal('show');
				
				window.checkcalls = setTimeout(function () {
					Wo_CheckForCallAnswer(data.id,'audio');
				}, 2000);
				setTimeout(function() {
					$('#calling-modal').find('.modal-title').html('<i class="fa fa fa-video-camera"></i> ' + data.text_no_answer);
					$('#calling-modal').find('.modal-body p').text(data.text_please_try_again_later);
					clearTimeout(window.checkcalls);
					Wo_PlayAudioCall('stop');
				}, 43000);
				Wo_PlayAudioCall('play');
			}
		});
	}
	function Wo_PlayVideoCall(type) {
		var content = document.getElementById('video-calling-sound');
		if (!content){
			return;
		}
		if (type == 'play') {
			var promise = document.getElementById('video-calling-sound').play();
			if (promise !== undefined) {
				promise.then(function() {
					
				}).catch(function() {
				
				});
			}
			window.playmusic = setTimeout(function() {
				Wo_PlayVideoCall('play');
			}, 100);
		} else {
			clearTimeout(window.playmusic);
			document.getElementById('video-calling-sound').pause();
		}
	}
	function Wo_PlayAudioCall(type) {
		var content = document.getElementById('calling-sound');
		if (!content){
			return;
		}

		if (type == 'play') {
			var promise = document.getElementById('calling-sound').play();
			if (promise !== undefined) {
				promise.then(function(){
					
				}).catch(function() {
					
				});
			}
			window.playmusic_ = setTimeout(function() {
				Wo_PlayAudioCall('play');
			}, 100);
		} else {
			clearTimeout(window.playmusic_);
			document.getElementById('calling-sound').pause();
		}
	}


jQuery(document).ready(function($) {

	var hash = $('.csrf-token').val();

	$.ajaxSetup({ 
	    data: {
	        hash: ((hash != undefined) ? hash : 0)
	    },
	    cache: false,
	    timeout:(1000 * 360)
	});

	$(document).ajaxSend(function(e, xhr, opt) {
		if (opt.url.indexOf('main/follow') > 0 && not(is_logged())) {
			redirect('welcome');
			xhr.abort();
		}
	});

	$(document).on("click","[data-modal--menu-dismiss]",function(event) {
		$(this).closest('.modal--menu').removeClass('open');
	});

	$(document).on("click","[data-confirm--modal-dismiss]",function(event) {
		$(this).closest('.confirm--modal').fadeOut(200, function() {
			$(this).data('id', '');
		});
	});

	$(document).on('click',"[data-modal-menu]",function(event) {
		$(".modal--menu").each(function(index, el) {
			$(el).removeClass('open');
		});

		var modal = "#"+$(this).data('modal-menu');
		$(modal).addClass('open');
	});

	$(".caption").filter(function(){
    	if($.trim($(this).text()).length < 1){
        	$(this).text('')
        }
    });

    $(document).on('click', '.add-post-bf--controls', function(event) {
    	$(this).toggleClass('active');
    });

	

	delay(function(){
		if (is_logged()) {
			update_data();
		}
	},100);
	$('[data-toggle="tooltip"]').tooltip();

	$(document).on('hover', '[data-toggle="tooltip"]', function(event) {
		event.preventDefault();
		$('[data-toggle="tooltip"]').tooltip();
	});


	$(document).on('show.bs.dropdown', '.dropdown.slide', function(event) {
		$(this).find('.dropdown-menu').first().stop(true, true).slideDown(100);
	});

	$(document).on('hide.bs.dropdown', '.dropdown.slide', function(event) {
		$(this).find('.dropdown-menu').first().stop(true, true).slideUp(100);
	});

	$(document).on('click', '.lightbox-ol', function(event) {
		event.preventDefault();
		$('.light__box').remove();
		$('body').removeClass('scroll_stop');
	});

	$.fn.scroll2 = function (speed) {
        if (typeof(speed) === 'undefined')
            speed = 500;

        $('html, body').animate({
            scrollTop: ($(this).offset().top - 100)
        }, speed);

        return $(this);
    };

    $(window).scroll(function(event) {
    	if ($(this).scrollTop() > $(this).height()) {
    		$(".scroll__up").css('right', '25px');
    	}
    	else{
    		$(".scroll__up").css('right', '-100px');
    	}
    });

    $(".scroll__up").click(function(event) {
    	$("html,body").stop(/*stop animation*/).animate({scrollTop:0}, 800);
    });

    $("#search-chats").keyup(function(event) {
		var chatls = $(".chat-list").find('ul');
		var uname  = $(this).val();
		var found  = new Array();

		if (uname.length > 1) {
			chatls.find('span.username').each(function(index, el) {
				var username = $(el).text();
				if (username.indexOf(uname) == -1) {
					$(el).closest('li').addClass('hidden');
				}
			});
		}
		else{
			chatls.find('li').removeClass('hidden');
		}
	});

	$(document).ready(function(){
		$('.tl-follow-suggestions').flickity({
			freeScroll: true,
			pageDots: false,
			contain: true,
			resize: false,
			cellAlign: 'left',
		});
	});

	$(document).on('submit','#edit-post-caption', function(event) {
		event.preventDefault();

		var text    = $(this).find('#caption').val();
		var post_id = $(this).find('#post_id').val();


		if (int(post_id) == 0) {
			return false;
		}


		var post = $("div[data-post-id='"+post_id+"']");

		$("#create-newpost").hide();
		$(this).empty();
		$('body').removeClass('active');
		post.find('[data-caption]').html(linkify_htags(text));

		$.ajax({
			url: link('posts/update'),
			type: 'POST',
			dataType: 'json',
			data: {text:text,id:post_id},
		})
		.done(function(data) {
			if (data.message) {
				$.toast(data.message,{
	            	duration: 5000, 
	            	type: '',
	            	align: 'bottom',
	            	singleton: true
	            });
			}
		});
	});
	
	$(document).on('click','.delete--article',function(event) {
		var zis = $(this);
		var id  = zis.parents('.confirm--modal').data('id');
		var url = zis.parents('.confirm--modal').data('url');
		var _thumbnail = zis.parents('.confirm--modal').data('thumbnail');

		if (id) {
			zis.parents('.confirm--modal').data('id',"").fadeOut(200);
			$("#modal-progress").removeClass('hidden');
			$.ajax({
				url: link('blogs/delete-article'),
				type: 'POST',
				dataType: 'json',
				data: {post_id:id,thumbnail: _thumbnail},
			})
			.done(function(data) {
				if (data.status == 200) {
					$("div[data-article-id='"+id+"']").slideUp('fast',function(){
						$(this).remove();	
					});
				}
				else{
					$.toast(data.message,{
	                  duration: 5000, 
	                  type: '',
	                  align: 'bottom',
	                  singleton: true
	                });
				}

				if (url) {
					delay(function(){
						window.location.href = url;
					},1000);
				}
				$("#modal-progress").addClass('hidden');
			});
		}
	});

	$(document).on('click','.delete--post',function(event) {
		var zis = $(this);
		var id  = zis.parents('.confirm--modal').data('id');
		var url = zis.parents('.confirm--modal').data('url');
		

		if (id) {
			zis.parents('.confirm--modal').data('id',"").fadeOut(200);
			$("#modal-progress").removeClass('hidden');
			$.ajax({
				url: link('posts/delete-post'),
				type: 'POST',
				dataType: 'json',
				data: {post_id:id},
			})
			.done(function(data) {
				if (data.status == 200) {
					$("div[data-post-id='"+id+"']").slideUp('fast',function(){
						$(this).remove();	
					});
				}
				else{
					$.toast(data.message,{
	                  duration: 5000, 
	                  type: '',
	                  align: 'bottom',
	                  singleton: true
	                });
				}

				if (url) {
					delay(function(){
						window.location.href = url;
					},1000);
				}
				$("#modal-progress").addClass('hidden');
				$('body').removeClass('scroll_stop');
			});
		}
	});

	$(document).on('click','.delete--fund',function(event) {
		var zis = $(this);
		var id  = zis.parents('.confirm--modal').data('id');
		

		if (id) {
			zis.parents('.confirm--modal').data('id',"").fadeOut(200);
			$("#modal-progress").removeClass('hidden');
			$.ajax({
				url: link('profile/delete-fund'),
				type: 'POST',
				dataType: 'json',
				data: {fund_id:id},
			})
			.done(function(data) {
				if (data.status == 200) {
					$("div[data-fund='"+id+"']").slideUp('fast',function(){
						$(this).remove();	
					});
				}
				else{
					$.toast(data.message,{
	                  duration: 5000, 
	                  type: '',
	                  align: 'bottom',
	                  singleton: true
	                });
				}

				$("#modal-progress").addClass('hidden');
			});
		}
	});

	$(document).on('click','.delete--comment',function(event) {
		var zis = $(this);
		var id  = zis.parents('.confirm--modal').data('id');

		if (id) {
			zis.parents('.confirm--modal').data('id',"").fadeOut(200, function() {
				$("[data-post-comment='"+id+"']").slideUp('fast',function(){
					$(this).remove();
				});

				$.post(link('posts/delete-comment'), {id:id});
				$('.confirm--modal').css('display', 'none');
			});;
		}
	});
	
	$(document).on('click','.delete--blog-comment',function(event) {
		var zis = $(this);
		var id  = zis.parents('.confirm--modal').data('id');

		if (id) {
			zis.parents('.confirm--modal').data('id',"").fadeOut(200, function() {
				$("[data-post-comment='"+id+"']").slideUp('fast',function(){
					$(this).remove();
				});

				$.post(link('blogs/delete-comment'), {id:id});
				$('.confirm--modal').css('display', 'none');
			});;
		}
	});

	$(document).on('click','.delete--funding-request',function(event) {
		var zis = $(this);
		var id  = zis.parents('.confirm--modal').data('id');

		if (id) {
			zis.parents('.confirm--modal').data('id',"").fadeOut(200, function() {
				$("[data-fund='"+id+"']").slideUp('fast',function(){
					$(this).remove();
				});

				$.post(link('main/delete-funding'), {id:id});
				$('.confirm--modal').css('display', 'none');
			});;
		}
	});
});

function get_cookie(name) {
    var matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));

    return (matches ? decodeURIComponent(matches[1]) : undefined);
}

function is_logged(){
	if (get_cookie('user_id') != undefined) {
		return true;
	}

	return false;
}

function scroll2top() {
	verticalOffset = typeof (verticalOffset) != 'undefined' ? verticalOffset : 0;
	element = $('html');
	offset = element.offset();
	offsetTop = offset.top;
	$('html, body').animate({
	    scrollTop: offsetTop
	}, 300, 'linear');
}

function base64_2_blob(dataURI) {
    var byteString;
    if (dataURI.split(',')[0].indexOf('base64') >= 0)
        byteString = atob(dataURI.split(',')[1]);
    else
        byteString = unescape(dataURI.split(',')[1]);

    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
    var ia = new Uint8Array(byteString.length);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }

    return new Blob([ia], { type:mimeString });
}

function video_base64_iamge(video) {
	if (!video) {
		return false;
	}

	var canvas    = document.createElement("canvas");
	var scale     = 0.25;
	var b64       = "";
	canvas.width  = video.videoWidth * scale;
	canvas.height = video.videoHeight * scale;
	canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
	b64 = canvas.toDataURL();
	return b64;
}
function get_yt_id(url){
	if (!url) { return false;}
	var regex  = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
	var groups = regex.exec(url);
    console.log(groups);
	return (Array.isArray(groups)) ? groups[7] : false;
}

function get_vimeo_id(url){
	if (!url) { return false;}
	var regex  = /^https?:\/\/vimeo\.com\/([0-9]+)\/{0,1}$/;
	var groups = regex.exec(url);
	return (Array.isArray(groups)) ? groups[1] : false;
}
function is_playtube_url(url,orginal){
	if (!url && !orginal) { return false;}
	if (typeof orginal == 'string' && orginal.length == 0) { return false;}
	//var regex = new RegExp(orginal);
	var regex = orginal;
	
	var groups = regex.exec(url);
	
	return (Array.isArray(groups) && groups[2]) ? groups[2] : false;
}
function get_dailymotion_id(url){
	if (!url) { return false;}
	var regex  = /^(?:https?:\/\/)?www\.dailymotion\.com\/video\/([a-zA-Z0-9_]+)\/?$/;
	var groups = regex.exec(url);
	return (Array.isArray(groups)) ? groups[1] : false;
}
function is_tiktok_url(url){
	if (!url) { return false;}
	var regex = /(http|https):\/\/www.tiktok\.com\/(.*)\/video\/(.*)+/;
	var groups = regex.exec(url);
	var regex2 = /(http|https):\/\/vt.tiktok\.com\/(.*)+/;
	var groups2 = regex2.exec(url);
	return (Array.isArray(groups) || Array.isArray(groups2)) ? true : false;
}

function is_mp4_url(url){
	if (!url) { return false;}
	var regex = /^(http:\/\/|https:\/\/|www\.).*(\.mp4)$/;
	var groups = regex.exec(url);
	return (Array.isArray(groups) && groups[2] == '.mp4') ? true : false;
}

function delete_post(id,redir){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	if (!id) { return false; }
	$('div.delpost--modal').data('id',id).fadeIn(250);
	if (redir === true) {
		$('div.delpost--modal').data('url',site_url());
	}
}

function delete_article(id,thumbnail,redir){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	if (!id) { return false; }
	$('div.delarticle--modal').data('id',id).data('thumbnail',thumbnail).fadeIn(250);
	if (redir === true) {
		$('div.delarticle--modal').data('url',site_url());
	}
}

function embed_post(id,redir){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	if (!id) { return false; }
	$('#embed_post').val('<iframe src="'+site_url( 'embed/'+id )+'" style="width: 100%"></iframe>');
	$('div.embedpost--modal').data('id',id).fadeIn(250);
	if (redir === true) {
		$('div.embedpost--modal').data('url',site_url());
	}
}

function comment_post(id,event,type = ''){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	if (((event.keyCode == 13 && event.shiftKey == 0) || type == 'send') && id) {
		event.preventDefault();
		var post = $("div[data-post-id='"+id+"']");
		post.find('.commenting-overlay').removeClass('hidden');
		//var text = post.find('.comment').val();
		var text = $("#emojionearea"+id)[0].emojioneArea.getText();
		var list = post.find('.post-comments-list');
		post.find('.comment').val('');
		$.post(xhr_url() + 'posts/add-comment', {post_id:id,text:text}, function(data, textStatus, xhr) {
			if (data.status == 200) {
				$(data.html).insertAfter(list.find('li.pp_post_comms'));
				if (list.css('display') == 'none') {
					list.slideDown(50);
				}

			}
			$('.emojionearea .emojionearea-editor').html('');
			post.find('.commenting-overlay').addClass('hidden');
			
		});
	}
	else{
		return false;
	}
}

function delete_commnet(id){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	if (id) {
		$('.delcomment--modal').data('id', id).fadeIn(250);
	}
	else{
		return false;
	}
}

function delete_blog_commnet(id){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	if (id) {
		$('.delcomment--modal').data('id', id).fadeIn(250);
	}
	else{
		return false;
	}
}
function delete_funding(id){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	if (id) {
		$('.delfunding--modal').data('id', id).fadeIn(250);
	}
	else{
		return false;
	}
}

var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();


function lightbox(post_id,page){
	if (!post_id || !page) { return false; }

	$("#modal-progress").removeClass('hidden');
	$.get((xhr_url() + 'posts/lightbox'), {post_id:post_id,page:page},function(data) {
		if (data.status == 200) {
			$(".lightbox__container").html(data.html);
			$('body').addClass('scroll_stop');
		}
		else{
			$(".lightbox__container").empty();
		}
		$("#modal-progress").addClass('hidden');
	},dataType= 'json');
	window.history.pushState({state:'new'},'', site_url( 'post/'+post_id ));
}

function storelightbox(post_id,page){
    if (!post_id || !page) { return false; }

    $("#modal-progress").removeClass('hidden');
    $.get((xhr_url() + 'store/lightbox'), {post_id:post_id,page:page},function(data) {
        if (data.status === 200) {
            $(".lightbox__container").html(data.html);
			$('body').addClass('scroll_stop');
        }
        else{
            $(".lightbox__container").empty();
        }
        $("#modal-progress").addClass('hidden');
    },dataType= 'json');
    window.history.pushState({state:'new'},'', site_url( 'store/'+post_id ));
}


function Pxpx_GetPayPalLink(type,amount,_title,_id) {
    $('.btn-paypal').attr('disabled','true');
    if (amount > 0) {
        $.post(link('store/get_paypal_link'), {type: type,amount:amount,title:_title,id:_id}, function(data, textStatus, xhr) {
            if (data.status == 200) {
                window.location.href = data.url;
            }
            $('.btn-paypal').removeAttr('disabled');
        });
    }
    else{
        scroll2top();
        $.toast("{{LANG please_check_details}}",{
            duration: 5000,
            type: 'success',
            align: 'bottom',
            singleton: true
        });
    }
}

function lb_comment(id,event){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}

	if (event.keyCode == 13 && event.shiftKey == 0 && id) {

		event.preventDefault();
		var lb = $("div.light__box");
		lb.find('.commenting-overlay').removeClass('hidden');
		var text = lb.find('.comment_light').val();
		var list = lb.find('.post-comments-list');
		if (!text) { return false; }

		$.post(xhr_url() + 'posts/add-comment', {post_id:id,text:text}, function(data, textStatus, xhr) {
			if (data.status == 200) {
				if (list.find('.pp_light_comm_count').length > 0) {
					$(data.html).insertAfter(list.find('.pp_light_comm_count'));
				}
				else{
					list.prepend(data.html);
				}

			}

			$('.emojionearea .emojionearea-editor').text('');
				$('.emojionearea .emojionearea-editor').html('');
			lb.find('.comment_light').val('');
			lb.find('.commenting-overlay').addClass('hidden');
		});
	}
	else{
		return false;
	}
}

function scroll_el(object,speed){
	if (!speed) {
		speed = 1000;
	}
	object.animate({
		scrollTop: (object.get(0).scrollHeight)
	}, speed);
}

function not(val){
	return !val;
}


function randint(min, max) {
  return Math.floor(Math.random() * (max - min)) + min;
}


function random_color(){
	var cols = [
		'#5bbe89',
		'#d55f3a',
		'#a97cc6',
		'#ef863c',
		'#e27100',
		'#15493b',
		'#b582af',
	    '#a84849',
	    '#fc9cde',
	    '#f9c270',
	    '#70a0e0',
	    '#56c4c5',
	    '#51bcbc',
	    '#f33d4c',
	    '#a1ce79',
	    '#a085e2',
	    '#ed9e6a',
	    '#2b87ce',
	    '#f2812b',
	    '#0ba05d',
	    '#f9a722',
	    '#8ec96c',
	    '#01a5a5',
	    '#5462a5',
	    '#609b41',
	    '#ff72d2',
	    '#008484',
	    '#c9605e',
	    '#aa2294',
	    '#056bba',
	    '#0e71ea'
	];
	return cols[randint(0,(cols.length - 1))];
}


function log(val){
	console.log(val);
}

function int(val){
	if ($.isNumeric(val) === true) {
		val = Number(val);
	}
	else{
		val = 0;
	}

	return val;
}

var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();


function get_notifications(){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	var notfi_set = $("ul#notifications__list");
	var newnotif  = $("#new__notif");
	$.ajax({
		url: xhr_url() + 'main/get_notif',
		type: 'GET',
		dataType: 'json'
	})
	.done(function(data) {
		if (data.status == 200) {
			newnotif.text('');
			notfi_set.html(data.html);
		}
		else if(data.status == 304){
			notfi_set.html('<div class="empty_state"><span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M18.586 20H4a.5.5 0 0 1-.4-.8l.4-.533V10c0-1.33.324-2.584.899-3.687L1.393 2.808l1.415-1.415 19.799 19.8-1.415 1.414L18.586 20zM20 15.786L7.559 3.345A8 8 0 0 1 20 10v5.786zM9.5 21h5a2.5 2.5 0 1 1-5 0z"/></svg></span><p>' + data.message +'</p></div>');
		}
	});
}

function get_requests(){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	var notfi_set = $("ul#requests__list");
	var newnotif  = $("#new__notif_follow");
	$.ajax({
		url: xhr_url() + 'main/get_requests',
		type: 'GET',
		dataType: 'json'
	})
	.done(function(data) {
		if (data.status == 200) {
			newnotif.text('');
			notfi_set.html(data.html);
		}
		else if(data.status == 304){
			notfi_set.html('<div class="empty_state"><span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M14 14.252V22H4a8 8 0 0 1 10-7.748zM12 13c-3.315 0-6-2.685-6-6s2.685-6 6-6 6 2.685 6 6-2.685 6-6 6zm7 3.586l2.121-2.122 1.415 1.415L20.414 18l2.122 2.121-1.415 1.415L19 19.414l-2.121 2.122-1.415-1.415L17.586 18l-2.122-2.121 1.415-1.415L19 16.586z"/></svg></span><p>' + data.message +'</p></div>');
		}
	});
}

function Pxp_AcceptFollowRequest(self,user_id) {
	$('.accept_request_btn').attr('disabled', 'true');
	$.post(xhr_url() + 'main/accept_requests', {user_id: user_id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			$('#request_menu_'+user_id).remove();
			$.toast(data.message,{
            	duration: 5000, 
            	type: '',
            	align: 'bottom',
            	singleton: true
            });
		}
		else{
			$.toast(data.message,{
            	duration: 5000, 
            	type: '',
            	align: 'bottom',
            	singleton: true
            });
		}
	});
}

function Pxp_DeleteFollowRequest(self,user_id) {
	$('.accept_request_btn').attr('disabled', 'true');
	$.post(xhr_url() + 'main/delete_requests', {user_id: user_id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			$('#request_menu_'+user_id).remove();
			// $.toast(data.message,{
   //          	duration: 5000, 
   //          	type: '',
   //          	align: 'bottom',
   //          	singleton: true
   //          });
		}
		else{
			$.toast(data.message,{
            	duration: 5000, 
            	type: '',
            	align: 'bottom',
            	singleton: true
            });
		}
	});
}
function like_post(post_id,zis){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}

	if (!post_id || !zis) {
		return false;
	}

	var zis = $(zis);
	var lks = null;

	if ($("[data-post-likes='"+post_id+"']").length == 1) {
		lks = $("[data-post-likes='"+post_id+"']");
	}

	if (zis.hasClass('active')) {
		zis.removeClass('active');
		if (lks) {
			let likes = int(lks.text());
			if (likes >= 1) {
				lks.text(likes - 1);
			}
		}
	}
	else{
		zis.addClass('active');
		if (lks) {
			let likes = int(lks.text()) + 1;
			lks.text(likes);
		}
	}

	$.ajax({
		url: xhr_url() + 'posts/like',
		type: 'POST',
		dataType: 'json',
		data: {id:post_id},
	})
	.done(function(data) {
		px_add_view(post_id);
	});
}

function save_post(post_id,zis){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}

	if (!post_id || !zis) {
		return false;
	}

	var zis = $(zis);

	if (zis.hasClass('active')) {
		zis.removeClass('active');
	}
	else{
		zis.addClass('active');
	}

	$.ajax({
		url: link('posts/save'),
		type: 'POST',
		dataType: 'json',
		data: {id:post_id},
	})
	.done(function(data) {
		if (data.status == 200) {
			$.toast(data.message,{
            	duration: 5000, 
            	type: '',
            	align: 'bottom',
            	singleton: true
            });
		}
	});
}


function update_data(){
	var app_page = $("body").data('app');
	var features = {
		'notifications':1,
		'new_messages':1,
		'stories':0,
		'chats':0
	};

	if (app_page == 'messages') {
		features['chats'] = 1;
	}

	$.ajax({
		url: link('main/update-data'),
		type: 'GET',
		dataType: 'json',
		data: features,
	})
	.done(function(data) {
		if (data.notif && $.isNumeric(data.notif)) {
			var newnotif = $("#new__notif");
			newnotif.text(data.notif);
		}

		if (data.requests && $.isNumeric(data.requests)) {
			var requests = $("#new__notif_follow");
			requests.text(data.requests);
		}

		if (data.new_messages && $.isNumeric(data.new_messages)) {
			var new_messages = $("#new__messages");
			var new_messages_sec = $("#new__messages_nav");
			new_messages.text(data.new_messages);
			new_messages_sec.text(data.new_messages);
		}

		if(data.is_call == 0 ){
			Wo_PlayVideoCall('stop');
			$( '#re-calling-modal' ).remove();
			$( '.modal-overlay' ).remove();
			$( 'body' ).removeClass( "modal-open" );
		}

		var videochatnotification = data.calls;
		if ( videochatnotification > 0 && $('#re-calling-modal').length == 0 && $('#re-talking-modal').length == 0) {
			if ($('#calling-modal').length == 0) {
				$('body').append(data.calls_html);
				if (!$('#re-calling-modal').hasClass('calling')) {
					$('#re-calling-modal').modal({dismissible: false});
					$('#re-calling-modal').modal('show');
					Wo_PlayVideoCall('play');
				}
				document.title = 'New video call..';
				setTimeout(function () {
					$('.modal').modal('close');
					$('#re-calling-modal').addClass('calling');
					Wo_PlayVideoCall('stop');
					//document.title = document_title;
					setTimeout(function () {
						$( '#re-calling-modal' ).remove();
						$( '.modal-overlay' ).remove();
						$( 'body' ).removeClass( "modal-open" );
					}, 3000);
					$( '#re-calling-modal' ).remove();
					$('.modal-overlay.in').hide();
				}, 40000);
			}
		}else{
			//alert('hangout');
		}
				
	});

	setTimeout(function(){
		update_data();
	},(1000 * 10))
}


function link(path){
	var url = xhr_url() + path;
	return url;
}

function redirect(path){
	window.location.href = site_url(path);
}

function header_loadbar(a){
	if (a == 'f') {
		var w = $('.loadding-pgbar .bar').css('left');
		$('.loadding-pgbar .bar').animate({left: 100+'%'},{
			complete:function(){
				$(this).fadeOut(500, function() {
					$(this).css('left', '0px').fadeIn(10);
				});
			}
		});
	}
	else{
		var w = randint(35,75);
		$('.loadding-pgbar .bar').animate({left:w +'%'},1000);
	}
}

function view_post_likes(post_id){
	if (post_id && $.isNumeric(post_id)) {
		if ($('[data-post-likes='+post_id+']').text() > 0) {
			header_loadbar();

			$.ajax({
				url: link('posts/view-likes'),
				type: 'GET',
				dataType: 'json',
				data: {post_id:post_id},
			})
			.done(function(data) {
				if (data.status == 200) {
					$(data.html).insertAfter('main.container');
				}
				else{
					if (data.message) {
						$.toast(data.message,{
		                	duration: 5000, 
		                	type: '',
		                	align: 'bottom',
		                	singleton: true
		                });
					}
				}
				delay(function(){
					header_loadbar('f');
				},500);
			});
		}
		
	}
}

function toggle_post_comm(post_id) {
	if (post_id && $.isNumeric(post_id)) {
		$('.timeline-posts[data-post-id="'+post_id+'"]').find('.post-comments-list').slideToggle(200);
	}
}

function load_tlp_comments(post_id,zis) {
	if (post_id && $.isNumeric(post_id) && zis) {
		var post = $('.timeline-posts[data-post-id="'+post_id+'"]');
        var first = post.find('.post-comments-list').find('[data-post-comment]').first();
		var last = post.find('.post-comments-list').find('[data-post-comment]').last();
		var cmid = last.data('post-comment');
		var zis  = $(zis);

		if ($.isNumeric(cmid)) {
			zis.attr('disabled', 'true');
			$.ajax({
				url: link('posts/load-tlp-comments'),
				type: 'POST',
				dataType: 'json',
				data: {post_id: post_id,offset:cmid},
			})
			.done(function(data) {
				if (data.status == 200) {
					$(data.html).insertAfter(last);
                    $(data.html).insertAfter(first);
				}
				else{
					zis.text(data.message);
					delay(function(){
						zis.fadeOut(300, function() {
							$(this).parent('li').remove();
						});
					},3000);
				}
				zis.removeAttr('disabled');
			});
		}
	}
}

function edit_post(post_id){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}

	if (post_id && $.isNumeric(post_id)) {
		var form = $("#post-editing-template").html();
		var post = $("div[data-post-id='"+post_id+"']");
		var text = post.find('[data-caption]').text();
		
		if (form.length) {
			form = $(form);
			form.find('#caption').val($.trim(text));
			form.find('#post_id').val(post_id);
			console.log(form)
			$('body').addClass('active');
			$("#create-newpost").html(form).show();
		}
	}
}

function linkify_htags(text){
	var htags = text.match(/#([^`~!@$%^&*\#()\-+=\\|\/\.,<>?\'\":;{}\[\]* ]{4,120})/ig);
	var htag  = '';

	if (Array.isArray(htags) && htags.length > 0) {
		htags.forEach(function(el){
			htag = el.substr(1);
			htag = '<a href="'+site_url(('explore/tags/'+htag))+'">'+el+'</a>';
			text = text.replace(el, htag);
		});
	}
	return text;
}

function px_add_view(post_id) {
	$.ajax({
		url: link('posts/add_view'),
		type: 'POST',
		dataType: 'json',
		data: {post_id:post_id},
	})
	.done(function(data) {
		if (data.status == 200) {
			$('.video_views_').find('span').html(data.count)
		}
		else{
		}
	});
}
function add_emoji(id) {
	var el = $("#emojionearea"+id).emojioneArea({
		pickerPosition: "bottom",
		filtersPosition: "bottom",
	    tonesStyle: "bullet",
	    events: {
	     	keyup: function (editor, event) {
		      $("div[data-post-id='"+id+"']").find('.comment').val(el[0].emojioneArea.getText());
              comment_post(id,event);
		    }
	    },
	    filters: {
	        flags : false,
	        objects : false,
	        travel_places : false,
	        activity : false
	    }
	});
}
function add_comment_emoji(id) {
	var el = $("#emojionearea_comment"+id).emojioneArea({
		pickerPosition: "bottom",
	    tonesStyle: "radio",
	    events: {
	     	keyup: function (editor, event) {
		      $("[data-post-comment='"+id+"']").find('.comment').val(el[0].emojioneArea.getText());
              pxp_reply_comment(id,event);
		    }
	    },
	    filters: {
	        flags : false,
	        objects : false,
	        travel_places : false,
	        activity : false
	    }
	});
}
function add_comment_emoji_lightbox(id) {
	var el = $("#emojionearea_comment_lightbox_"+id).emojioneArea({
		pickerPosition: "bottom",
	    tonesStyle: "radio",
	    events: {
	     	keyup: function (editor, event) {
		      $("[data-post-comment='"+id+"']").find('.comment').val(el[0].emojioneArea.getText());
              pxp_reply_comment(id,event,'lightbox');
		    }
	    },
	    filters: {
	        flags : false,
	        objects : false,
	        travel_places : false,
	        activity : false
	    }
	});
}

function change_mode() {
	if ($('#night_mode_css').length > 0) {
		$('#night_mode_css').remove();
		$('.logo img').attr('src', window.logo);
	}
	else{
		$('head').append($('<link>',{
			rel: 'stylesheet',
			href:  get_theme()+"/main/static/css/styles.master_night.css",
			id : 'night_mode_css'
		}))
        $('.logo img').attr('src', window.light_logo);
	}
	$.post(link('main/change_mode'), function(data, textStatus, xhr) {
	});
}

function get_more_activities() {
	var id = $('.activity_').last().attr('id');
	$('#load_more_activities_').hide();
	$("#load_more_activities_load_").removeClass('hidden');
	$.post(link('main/get_more_activities'),{id:id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			$('#activities_container').append(data.html);
			$('#load_more_activities_').show();
		}
		else{
			if ($('#load_more_activities_text').length == 0) {
				$('#activities_container').append('<div id="load_more_activities_text" class="item activity_"><div class="caption caption_ text-center">'+data.text+'</div></div>');
                $('#load_more_activities_').hide();
			}
        }
        scroll_el($('#activities_container'),100);
		$("#load_more_activities_load_").addClass('hidden');
	});
}
function show_m_reprted(post_id) {
	$('.show_m_reprted-'+post_id).remove(); 
	$('.text_m_reprted-'+post_id).remove(); 
}

// like comment 
function pxp_like_dis_comment(comment_id,self) {
	$.post(link('comments/like_dislike'),{comment_id:comment_id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			if (data.code == 1) {
				$('.comment_like_'+comment_id).find('svg').addClass('liked_color');
				$('.comment_like_span_'+comment_id).find('span').html(data.likes);
			}
			else{
				$('.comment_like_'+comment_id).find('svg').removeClass('liked_color');
				$('.comment_like_span_'+comment_id).find('span').html(data.likes);
			}
		}
	});
}
// like comment 
// reply comment 
function pxp_reply_comment(id,event,type = ''){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	if (event.keyCode == 13 && event.shiftKey == 0 && id) {
		event.preventDefault();
		var comment = $("[data-post-comment='"+id+"']");
		comment.find('.reply_commenting_overlay').removeClass('hidden');
		var text = comment.find('.comment').val();
		var list = comment.find('.reply_list');
		comment.find('.comment').val('');
		$.post(xhr_url() + 'comments/add_comment_reply', {comment_id:id,text:text}, function(data, textStatus, xhr) {
			if (data.status == 200) {
				if (type == 'lightbox') {
					$('.reply_list_lightbox_'+id).append(data.html);
				}
				else{
					list.append(data.html);
				}
				// $(data.html).insertAfter(list.find('li.pp_post_comms'));
				// if (list.css('display') == 'none') {
				// 	list.slideDown(50);
				// }

			}
			$('.emojionearea .emojionearea-editor').html('');
			comment.find('.reply_commenting_overlay').addClass('hidden');
			$('.commenting-overlay').addClass('hidden');
			
		});
	}
	else{
		return false;
	}
}

function pxp_get_comment_reply(comment_id,self,type = ''){
	var comment = $("[data-post-comment='"+comment_id+"']");
	var list = comment.find('.reply_list');
	if (list.text() == '' && type != 'lightbox') {
		$.post(link('comments/get_comment_reply'),{comment_id:comment_id}, function(data, textStatus, xhr) {
			if (data.status == 200) {
				if (type == 'lightbox') {
					$('.reply_list_lightbox_'+comment_id).html(data.html);
					$('#lightbox_reply_form_'+comment_id).show();
				}
				else{
					list.html(data.html);
					$('#add_reply_form_'+comment_id).show();
				}
				
				// if (data.code == 1) {
				// 	$(self).find('svg').addClass('liked_color');
				// 	$(self).find('span').html(data.likes);
				// }
				// else{
				// 	$(self).find('svg').removeClass('liked_color');
				// 	$(self).find('span').html(data.likes);
				// }
			}
		});
	}
	else if($('.reply_list_lightbox_'+comment_id).text() == '' && type == 'lightbox'){
		$.post(link('comments/get_comment_reply'),{comment_id:comment_id}, function(data, textStatus, xhr) {
			if (data.status == 200) {
				if (type == 'lightbox') {
					$('.reply_list_lightbox_'+comment_id).html(data.html);
					$('#lightbox_reply_form_'+comment_id).show();
				}
				else{
					list.html(data.html);
					$('#add_reply_form_'+comment_id).show();
				}
				
				// if (data.code == 1) {
				// 	$(self).find('svg').addClass('liked_color');
				// 	$(self).find('span').html(data.likes);
				// }
				// else{
				// 	$(self).find('svg').removeClass('liked_color');
				// 	$(self).find('span').html(data.likes);
				// }
			}
		});

	}
	else{
		console.log($('.reply_list_lightbox_'+comment_id).text() == '' );
		if (type == 'lightbox') {
			$('.reply_list_lightbox_'+comment_id).html('');
			$('#lightbox_reply_form_'+comment_id).hide();
		}
		else{
			list.html('');
			$('#add_reply_form_'+comment_id).hide();
		}
	}
}

function pxp_like_dis_comment_reply(reply_id,self) {
	$.post(link('comments/reply_like_dislike'),{reply_id:reply_id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			if (data.code == 1) {
				$(self).find('svg').addClass('liked_color');
				$('.comment_like_span_reply_'+reply_id).find('span').html(data.likes);
			}
			else{
				$(self).find('svg').removeClass('liked_color');
				$('.comment_like_span_reply_'+reply_id).find('span').html(data.likes);
			}
		}
	});
}

function delete_commnet_reply(id){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	if (id) {
		$('.delreply--modal').data('id', id).fadeIn(250);
	}
	else{
		return false;
	}
}
function pxp_boost_post(post_id){
	if (not(is_logged())) {
		redirect('welcome');
		return false;
	}
	$.post(link('posts/boost'), {post_id: post_id}, function(data, textStatus, xhr) {
		if (data.status == 200) {
			if (data.code == 1) {
				$('#boost_'+post_id).html(data.message);
			}
			else{
				$('#boost_'+post_id).html(data.message);
			}
		}
	});
}

$(document).on('click','.delete--comment--reply',function(event) {
	var zis = $(this);
	var id  = zis.parents('.confirm--modal').data('id');
	if (id) {
		zis.parents('.confirm--modal').data('id',"").fadeOut(200, function() {
			$("[data-post-comment-reply='"+id+"']").slideUp('fast',function(){
				$(this).remove();
			});

			$.post(link('comments/delete_reply'), {reply_id:id});
			$('.confirm--modal').css('display', 'none');
		});;
	}
});
// reply comment 
function showResponseAlert(item,alert_class,message,time = 0) {
    $(item).html('<div class="alert alert-'+alert_class+'">'+message+'</div>');
    if (time > 0) {
        setTimeout(() => {
            $(item).html('');
        },time);
    }
}

/*Stickey Sidebar*/
!function(i){i.fn.theiaStickySidebar=function(t){function e(t,e){var a=o(t,e);a||(console.log("TSS: Body width smaller than options.minWidth. Init is delayed."),i(document).on("scroll."+t.namespace,function(t,e){return function(a){var n=o(t,e);n&&i(this).unbind(a)}}(t,e)),i(window).on("resize."+t.namespace,function(t,e){return function(a){var n=o(t,e);n&&i(this).unbind(a)}}(t,e)))}function o(t,e){return t.initialized===!0||!(i("body").width()<t.minWidth)&&(a(t,e),!0)}function a(t,e){t.initialized=!0;var o=i("#theia-sticky-sidebar-stylesheet-"+t.namespace);0===o.length&&i("head").append(i('<style id="theia-sticky-sidebar-stylesheet-'+t.namespace+'">.theiaStickySidebar:after {content: ""; display: table; clear: both;}</style>')),e.each(function(){function e(){a.fixedScrollTop=0,a.sidebar.css({"min-height":"1px"}),a.stickySidebar.css({position:"static",width:"",transform:"none"})}function o(t){var e=t.height();return t.children().each(function(){e=Math.max(e,i(this).height())}),e}var a={};if(a.sidebar=i(this),a.options=t||{},a.container=i(a.options.containerSelector),0==a.container.length&&(a.container=a.sidebar.parent()),a.sidebar.parents().css("-webkit-transform","none"),a.sidebar.css({position:a.options.defaultPosition,overflow:"visible","-webkit-box-sizing":"border-box","-moz-box-sizing":"border-box","box-sizing":"border-box"}),a.stickySidebar=a.sidebar.find(".theiaStickySidebar"),0==a.stickySidebar.length){var s=/(?:text|application)\/(?:x-)?(?:javascript|ecmascript)/i;a.sidebar.find("script").filter(function(i,t){return 0===t.type.length||t.type.match(s)}).remove(),a.stickySidebar=i("<div>").addClass("theiaStickySidebar").append(a.sidebar.children()),a.sidebar.append(a.stickySidebar)}a.marginBottom=parseInt(a.sidebar.css("margin-bottom")),a.paddingTop=parseInt(a.sidebar.css("padding-top")),a.paddingBottom=parseInt(a.sidebar.css("padding-bottom"));var r=a.stickySidebar.offset().top,d=a.stickySidebar.outerHeight();a.stickySidebar.css("padding-top",1),a.stickySidebar.css("padding-bottom",1),r-=a.stickySidebar.offset().top,d=a.stickySidebar.outerHeight()-d-r,0==r?(a.stickySidebar.css("padding-top",0),a.stickySidebarPaddingTop=0):a.stickySidebarPaddingTop=1,0==d?(a.stickySidebar.css("padding-bottom",0),a.stickySidebarPaddingBottom=0):a.stickySidebarPaddingBottom=1,a.previousScrollTop=null,a.fixedScrollTop=0,e(),a.onScroll=function(a){if(a.stickySidebar.is(":visible")){if(i("body").width()<a.options.minWidth)return void e();if(a.options.disableOnResponsiveLayouts){var s=a.sidebar.outerWidth("none"==a.sidebar.css("float"));if(s+50>a.container.width())return void e()}var r=i(document).scrollTop(),d="static";if(r>=a.sidebar.offset().top+(a.paddingTop-a.options.additionalMarginTop)){var c,p=a.paddingTop+t.additionalMarginTop,b=a.paddingBottom+a.marginBottom+t.additionalMarginBottom,l=a.sidebar.offset().top,f=a.sidebar.offset().top+o(a.container),h=0+t.additionalMarginTop,g=a.stickySidebar.outerHeight()+p+b<i(window).height();c=g?h+a.stickySidebar.outerHeight():i(window).height()-a.marginBottom-a.paddingBottom-t.additionalMarginBottom;var u=l-r+a.paddingTop,S=f-r-a.paddingBottom-a.marginBottom,y=a.stickySidebar.offset().top-r,m=a.previousScrollTop-r;"fixed"==a.stickySidebar.css("position")&&"modern"==a.options.sidebarBehavior&&(y+=m),"stick-to-top"==a.options.sidebarBehavior&&(y=t.additionalMarginTop),"stick-to-bottom"==a.options.sidebarBehavior&&(y=c-a.stickySidebar.outerHeight()),y=m>0?Math.min(y,h):Math.max(y,c-a.stickySidebar.outerHeight()),y=Math.max(y,u),y=Math.min(y,S-a.stickySidebar.outerHeight());var k=a.container.height()==a.stickySidebar.outerHeight();d=(k||y!=h)&&(k||y!=c-a.stickySidebar.outerHeight())?r+y-a.sidebar.offset().top-a.paddingTop<=t.additionalMarginTop?"static":"absolute":"fixed"}if("fixed"==d){var v=i(document).scrollLeft();a.stickySidebar.css({position:"fixed",width:n(a.stickySidebar)+"px",transform:"translateY("+y+"px)",left:a.sidebar.offset().left+parseInt(a.sidebar.css("padding-left"))-v+"px",top:"0px"})}else if("absolute"==d){var x={};"absolute"!=a.stickySidebar.css("position")&&(x.position="absolute",x.transform="translateY("+(r+y-a.sidebar.offset().top-a.stickySidebarPaddingTop-a.stickySidebarPaddingBottom)+"px)",x.top="0px"),x.width=n(a.stickySidebar)+"px",x.left="",a.stickySidebar.css(x)}else"static"==d&&e();"static"!=d&&1==a.options.updateSidebarHeight&&a.sidebar.css({"min-height":a.stickySidebar.outerHeight()+a.stickySidebar.offset().top-a.sidebar.offset().top+a.paddingBottom}),a.previousScrollTop=r}},a.onScroll(a),i(document).on("scroll."+a.options.namespace,function(i){return function(){i.onScroll(i)}}(a)),i(window).on("resize."+a.options.namespace,function(i){return function(){i.stickySidebar.css({position:"static"}),i.onScroll(i)}}(a)),"undefined"!=typeof ResizeSensor&&new ResizeSensor(a.stickySidebar[0],function(i){return function(){i.onScroll(i)}}(a))})}function n(i){var t;try{t=i[0].getBoundingClientRect().width}catch(i){}return"undefined"==typeof t&&(t=i.width()),t}var s={containerSelector:"",additionalMarginTop:0,additionalMarginBottom:0,updateSidebarHeight:!0,minWidth:0,disableOnResponsiveLayouts:!0,sidebarBehavior:"modern",defaultPosition:"relative",namespace:"TSS"};return t=i.extend(s,t),t.additionalMarginTop=parseInt(t.additionalMarginTop)||0,t.additionalMarginBottom=parseInt(t.additionalMarginBottom)||0,e(t,this),this}}(jQuery);



/*!
 * Flickity PACKAGED v2.2.1
 * Touch, responsive, flickable carousels
 *
 * Licensed GPLv3 for open source use
 * or Flickity Commercial License for commercial use
 *
 * https://flickity.metafizzy.co
 * Copyright 2015-2019 Metafizzy
 */

!function(e,i){"function"==typeof define&&define.amd?define("jquery-bridget/jquery-bridget",["jquery"],function(t){return i(e,t)}):"object"==typeof module&&module.exports?module.exports=i(e,require("jquery")):e.jQueryBridget=i(e,e.jQuery)}(window,function(t,e){"use strict";var i=Array.prototype.slice,n=t.console,d=void 0===n?function(){}:function(t){n.error(t)};function s(h,s,c){(c=c||e||t.jQuery)&&(s.prototype.option||(s.prototype.option=function(t){c.isPlainObject(t)&&(this.options=c.extend(!0,this.options,t))}),c.fn[h]=function(t){return"string"==typeof t?function(t,o,r){var a,l="$()."+h+'("'+o+'")';return t.each(function(t,e){var i=c.data(e,h);if(i){var n=i[o];if(n&&"_"!=o.charAt(0)){var s=n.apply(i,r);a=void 0===a?s:a}else d(l+" is not a valid method")}else d(h+" not initialized. Cannot call methods, i.e. "+l)}),void 0!==a?a:t}(this,t,i.call(arguments,1)):(function(t,n){t.each(function(t,e){var i=c.data(e,h);i?(i.option(n),i._init()):(i=new s(e,n),c.data(e,h,i))})}(this,t),this)},o(c))}function o(t){!t||t&&t.bridget||(t.bridget=s)}return o(e||t.jQuery),s}),function(t,e){"function"==typeof define&&define.amd?define("ev-emitter/ev-emitter",e):"object"==typeof module&&module.exports?module.exports=e():t.EvEmitter=e()}("undefined"!=typeof window?window:this,function(){function t(){}var e=t.prototype;return e.on=function(t,e){if(t&&e){var i=this._events=this._events||{},n=i[t]=i[t]||[];return-1==n.indexOf(e)&&n.push(e),this}},e.once=function(t,e){if(t&&e){this.on(t,e);var i=this._onceEvents=this._onceEvents||{};return(i[t]=i[t]||{})[e]=!0,this}},e.off=function(t,e){var i=this._events&&this._events[t];if(i&&i.length){var n=i.indexOf(e);return-1!=n&&i.splice(n,1),this}},e.emitEvent=function(t,e){var i=this._events&&this._events[t];if(i&&i.length){i=i.slice(0),e=e||[];for(var n=this._onceEvents&&this._onceEvents[t],s=0;s<i.length;s++){var o=i[s];n&&n[o]&&(this.off(t,o),delete n[o]),o.apply(this,e)}return this}},e.allOff=function(){delete this._events,delete this._onceEvents},t}),function(t,e){"function"==typeof define&&define.amd?define("get-size/get-size",e):"object"==typeof module&&module.exports?module.exports=e():t.getSize=e()}(window,function(){"use strict";function m(t){var e=parseFloat(t);return-1==t.indexOf("%")&&!isNaN(e)&&e}var i="undefined"==typeof console?function(){}:function(t){console.error(t)},y=["paddingLeft","paddingRight","paddingTop","paddingBottom","marginLeft","marginRight","marginTop","marginBottom","borderLeftWidth","borderRightWidth","borderTopWidth","borderBottomWidth"],b=y.length;function E(t){var e=getComputedStyle(t);return e||i("Style returned "+e+". Are you running this code in a hidden iframe on Firefox? See https://bit.ly/getsizebug1"),e}var S,C=!1;function x(t){if(function(){if(!C){C=!0;var t=document.createElement("div");t.style.width="200px",t.style.padding="1px 2px 3px 4px",t.style.borderStyle="solid",t.style.borderWidth="1px 2px 3px 4px",t.style.boxSizing="border-box";var e=document.body||document.documentElement;e.appendChild(t);var i=E(t);S=200==Math.round(m(i.width)),x.isBoxSizeOuter=S,e.removeChild(t)}}(),"string"==typeof t&&(t=document.querySelector(t)),t&&"object"==typeof t&&t.nodeType){var e=E(t);if("none"==e.display)return function(){for(var t={width:0,height:0,innerWidth:0,innerHeight:0,outerWidth:0,outerHeight:0},e=0;e<b;e++){t[y[e]]=0}return t}();var i={};i.width=t.offsetWidth,i.height=t.offsetHeight;for(var n=i.isBorderBox="border-box"==e.boxSizing,s=0;s<b;s++){var o=y[s],r=e[o],a=parseFloat(r);i[o]=isNaN(a)?0:a}var l=i.paddingLeft+i.paddingRight,h=i.paddingTop+i.paddingBottom,c=i.marginLeft+i.marginRight,d=i.marginTop+i.marginBottom,u=i.borderLeftWidth+i.borderRightWidth,f=i.borderTopWidth+i.borderBottomWidth,p=n&&S,g=m(e.width);!1!==g&&(i.width=g+(p?0:l+u));var v=m(e.height);return!1!==v&&(i.height=v+(p?0:h+f)),i.innerWidth=i.width-(l+u),i.innerHeight=i.height-(h+f),i.outerWidth=i.width+c,i.outerHeight=i.height+d,i}}return x}),function(t,e){"use strict";"function"==typeof define&&define.amd?define("desandro-matches-selector/matches-selector",e):"object"==typeof module&&module.exports?module.exports=e():t.matchesSelector=e()}(window,function(){"use strict";var i=function(){var t=window.Element.prototype;if(t.matches)return"matches";if(t.matchesSelector)return"matchesSelector";for(var e=["webkit","moz","ms","o"],i=0;i<e.length;i++){var n=e[i]+"MatchesSelector";if(t[n])return n}}();return function(t,e){return t[i](e)}}),function(e,i){"function"==typeof define&&define.amd?define("fizzy-ui-utils/utils",["desandro-matches-selector/matches-selector"],function(t){return i(e,t)}):"object"==typeof module&&module.exports?module.exports=i(e,require("desandro-matches-selector")):e.fizzyUIUtils=i(e,e.matchesSelector)}(window,function(h,o){var c={extend:function(t,e){for(var i in e)t[i]=e[i];return t},modulo:function(t,e){return(t%e+e)%e}},e=Array.prototype.slice;c.makeArray=function(t){return Array.isArray(t)?t:null==t?[]:"object"==typeof t&&"number"==typeof t.length?e.call(t):[t]},c.removeFrom=function(t,e){var i=t.indexOf(e);-1!=i&&t.splice(i,1)},c.getParent=function(t,e){for(;t.parentNode&&t!=document.body;)if(t=t.parentNode,o(t,e))return t},c.getQueryElement=function(t){return"string"==typeof t?document.querySelector(t):t},c.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},c.filterFindElements=function(t,n){t=c.makeArray(t);var s=[];return t.forEach(function(t){if(t instanceof HTMLElement)if(n){o(t,n)&&s.push(t);for(var e=t.querySelectorAll(n),i=0;i<e.length;i++)s.push(e[i])}else s.push(t)}),s},c.debounceMethod=function(t,e,n){n=n||100;var s=t.prototype[e],o=e+"Timeout";t.prototype[e]=function(){var t=this[o];clearTimeout(t);var e=arguments,i=this;this[o]=setTimeout(function(){s.apply(i,e),delete i[o]},n)}},c.docReady=function(t){var e=document.readyState;"complete"==e||"interactive"==e?setTimeout(t):document.addEventListener("DOMContentLoaded",t)},c.toDashed=function(t){return t.replace(/(.)([A-Z])/g,function(t,e,i){return e+"-"+i}).toLowerCase()};var d=h.console;return c.htmlInit=function(a,l){c.docReady(function(){var t=c.toDashed(l),s="data-"+t,e=document.querySelectorAll("["+s+"]"),i=document.querySelectorAll(".js-"+t),n=c.makeArray(e).concat(c.makeArray(i)),o=s+"-options",r=h.jQuery;n.forEach(function(e){var t,i=e.getAttribute(s)||e.getAttribute(o);try{t=i&&JSON.parse(i)}catch(t){return void(d&&d.error("Error parsing "+s+" on "+e.className+": "+t))}var n=new a(e,t);r&&r.data(e,l,n)})})},c}),function(e,i){"function"==typeof define&&define.amd?define("flickity/js/cell",["get-size/get-size"],function(t){return i(e,t)}):"object"==typeof module&&module.exports?module.exports=i(e,require("get-size")):(e.Flickity=e.Flickity||{},e.Flickity.Cell=i(e,e.getSize))}(window,function(t,e){function i(t,e){this.element=t,this.parent=e,this.create()}var n=i.prototype;return n.create=function(){this.element.style.position="absolute",this.element.setAttribute("aria-hidden","true"),this.x=0,this.shift=0},n.destroy=function(){this.unselect(),this.element.style.position="";var t=this.parent.originSide;this.element.style[t]=""},n.getSize=function(){this.size=e(this.element)},n.setPosition=function(t){this.x=t,this.updateTarget(),this.renderPosition(t)},n.updateTarget=n.setDefaultTarget=function(){var t="left"==this.parent.originSide?"marginLeft":"marginRight";this.target=this.x+this.size[t]+this.size.width*this.parent.cellAlign},n.renderPosition=function(t){var e=this.parent.originSide;this.element.style[e]=this.parent.getPositionValue(t)},n.select=function(){this.element.classList.add("is-selected"),this.element.removeAttribute("aria-hidden")},n.unselect=function(){this.element.classList.remove("is-selected"),this.element.setAttribute("aria-hidden","true")},n.wrapShift=function(t){this.shift=t,this.renderPosition(this.x+this.parent.slideableWidth*t)},n.remove=function(){this.element.parentNode.removeChild(this.element)},i}),function(t,e){"function"==typeof define&&define.amd?define("flickity/js/slide",e):"object"==typeof module&&module.exports?module.exports=e():(t.Flickity=t.Flickity||{},t.Flickity.Slide=e())}(window,function(){"use strict";function t(t){this.parent=t,this.isOriginLeft="left"==t.originSide,this.cells=[],this.outerWidth=0,this.height=0}var e=t.prototype;return e.addCell=function(t){if(this.cells.push(t),this.outerWidth+=t.size.outerWidth,this.height=Math.max(t.size.outerHeight,this.height),1==this.cells.length){this.x=t.x;var e=this.isOriginLeft?"marginLeft":"marginRight";this.firstMargin=t.size[e]}},e.updateTarget=function(){var t=this.isOriginLeft?"marginRight":"marginLeft",e=this.getLastCell(),i=e?e.size[t]:0,n=this.outerWidth-(this.firstMargin+i);this.target=this.x+this.firstMargin+n*this.parent.cellAlign},e.getLastCell=function(){return this.cells[this.cells.length-1]},e.select=function(){this.cells.forEach(function(t){t.select()})},e.unselect=function(){this.cells.forEach(function(t){t.unselect()})},e.getCellElements=function(){return this.cells.map(function(t){return t.element})},t}),function(e,i){"function"==typeof define&&define.amd?define("flickity/js/animate",["fizzy-ui-utils/utils"],function(t){return i(e,t)}):"object"==typeof module&&module.exports?module.exports=i(e,require("fizzy-ui-utils")):(e.Flickity=e.Flickity||{},e.Flickity.animatePrototype=i(e,e.fizzyUIUtils))}(window,function(t,e){var i={startAnimation:function(){this.isAnimating||(this.isAnimating=!0,this.restingFrames=0,this.animate())},animate:function(){this.applyDragForce(),this.applySelectedAttraction();var t=this.x;if(this.integratePhysics(),this.positionSlider(),this.settle(t),this.isAnimating){var e=this;requestAnimationFrame(function(){e.animate()})}},positionSlider:function(){var t=this.x;this.options.wrapAround&&1<this.cells.length&&(t=e.modulo(t,this.slideableWidth),t-=this.slideableWidth,this.shiftWrapCells(t)),this.setTranslateX(t,this.isAnimating),this.dispatchScrollEvent()},setTranslateX:function(t,e){t+=this.cursorPosition,t=this.options.rightToLeft?-t:t;var i=this.getPositionValue(t);this.slider.style.transform=e?"translate3d("+i+",0,0)":"translateX("+i+")"},dispatchScrollEvent:function(){var t=this.slides[0];if(t){var e=-this.x-t.target,i=e/this.slidesWidth;this.dispatchEvent("scroll",null,[i,e])}},positionSliderAtSelected:function(){this.cells.length&&(this.x=-this.selectedSlide.target,this.velocity=0,this.positionSlider())},getPositionValue:function(t){return this.options.percentPosition?.01*Math.round(t/this.size.innerWidth*1e4)+"%":Math.round(t)+"px"},settle:function(t){this.isPointerDown||Math.round(100*this.x)!=Math.round(100*t)||this.restingFrames++,2<this.restingFrames&&(this.isAnimating=!1,delete this.isFreeScrolling,this.positionSlider(),this.dispatchEvent("settle",null,[this.selectedIndex]))},shiftWrapCells:function(t){var e=this.cursorPosition+t;this._shiftCells(this.beforeShiftCells,e,-1);var i=this.size.innerWidth-(t+this.slideableWidth+this.cursorPosition);this._shiftCells(this.afterShiftCells,i,1)},_shiftCells:function(t,e,i){for(var n=0;n<t.length;n++){var s=t[n],o=0<e?i:0;s.wrapShift(o),e-=s.size.outerWidth}},_unshiftCells:function(t){if(t&&t.length)for(var e=0;e<t.length;e++)t[e].wrapShift(0)},integratePhysics:function(){this.x+=this.velocity,this.velocity*=this.getFrictionFactor()},applyForce:function(t){this.velocity+=t},getFrictionFactor:function(){return 1-this.options[this.isFreeScrolling?"freeScrollFriction":"friction"]},getRestingPosition:function(){return this.x+this.velocity/(1-this.getFrictionFactor())},applyDragForce:function(){if(this.isDraggable&&this.isPointerDown){var t=this.dragX-this.x-this.velocity;this.applyForce(t)}},applySelectedAttraction:function(){if(!(this.isDraggable&&this.isPointerDown)&&!this.isFreeScrolling&&this.slides.length){var t=(-1*this.selectedSlide.target-this.x)*this.options.selectedAttraction;this.applyForce(t)}}};return i}),function(r,a){if("function"==typeof define&&define.amd)define("flickity/js/flickity",["ev-emitter/ev-emitter","get-size/get-size","fizzy-ui-utils/utils","./cell","./slide","./animate"],function(t,e,i,n,s,o){return a(r,t,e,i,n,s,o)});else if("object"==typeof module&&module.exports)module.exports=a(r,require("ev-emitter"),require("get-size"),require("fizzy-ui-utils"),require("./cell"),require("./slide"),require("./animate"));else{var t=r.Flickity;r.Flickity=a(r,r.EvEmitter,r.getSize,r.fizzyUIUtils,t.Cell,t.Slide,t.animatePrototype)}}(window,function(n,t,e,a,i,r,s){var l=n.jQuery,o=n.getComputedStyle,h=n.console;function c(t,e){for(t=a.makeArray(t);t.length;)e.appendChild(t.shift())}var d=0,u={};function f(t,e){var i=a.getQueryElement(t);if(i){if(this.element=i,this.element.flickityGUID){var n=u[this.element.flickityGUID];return n.option(e),n}l&&(this.$element=l(this.element)),this.options=a.extend({},this.constructor.defaults),this.option(e),this._create()}else h&&h.error("Bad element for Flickity: "+(i||t))}f.defaults={accessibility:!0,cellAlign:"center",freeScrollFriction:.075,friction:.28,namespaceJQueryEvents:!0,percentPosition:!0,resize:!0,selectedAttraction:.025,setGallerySize:!0},f.createMethods=[];var p=f.prototype;a.extend(p,t.prototype),p._create=function(){var t=this.guid=++d;for(var e in this.element.flickityGUID=t,(u[t]=this).selectedIndex=0,this.restingFrames=0,this.x=0,this.velocity=0,this.originSide=this.options.rightToLeft?"right":"left",this.viewport=document.createElement("div"),this.viewport.className="flickity-viewport",this._createSlider(),(this.options.resize||this.options.watchCSS)&&n.addEventListener("resize",this),this.options.on){var i=this.options.on[e];this.on(e,i)}f.createMethods.forEach(function(t){this[t]()},this),this.options.watchCSS?this.watchCSS():this.activate()},p.option=function(t){a.extend(this.options,t)},p.activate=function(){this.isActive||(this.isActive=!0,this.element.classList.add("flickity-enabled"),this.options.rightToLeft&&this.element.classList.add("flickity-rtl"),this.getSize(),c(this._filterFindCellElements(this.element.children),this.slider),this.viewport.appendChild(this.slider),this.element.appendChild(this.viewport),this.reloadCells(),this.options.accessibility&&(this.element.tabIndex=0,this.element.addEventListener("keydown",this)),this.emitEvent("activate"),this.selectInitialIndex(),this.isInitActivated=!0,this.dispatchEvent("ready"))},p._createSlider=function(){var t=document.createElement("div");t.className="flickity-slider",t.style[this.originSide]=0,this.slider=t},p._filterFindCellElements=function(t){return a.filterFindElements(t,this.options.cellSelector)},p.reloadCells=function(){this.cells=this._makeCells(this.slider.children),this.positionCells(),this._getWrapShiftCells(),this.setGallerySize()},p._makeCells=function(t){return this._filterFindCellElements(t).map(function(t){return new i(t,this)},this)},p.getLastCell=function(){return this.cells[this.cells.length-1]},p.getLastSlide=function(){return this.slides[this.slides.length-1]},p.positionCells=function(){this._sizeCells(this.cells),this._positionCells(0)},p._positionCells=function(t){t=t||0,this.maxCellHeight=t&&this.maxCellHeight||0;var e=0;if(0<t){var i=this.cells[t-1];e=i.x+i.size.outerWidth}for(var n=this.cells.length,s=t;s<n;s++){var o=this.cells[s];o.setPosition(e),e+=o.size.outerWidth,this.maxCellHeight=Math.max(o.size.outerHeight,this.maxCellHeight)}this.slideableWidth=e,this.updateSlides(),this._containSlides(),this.slidesWidth=n?this.getLastSlide().target-this.slides[0].target:0},p._sizeCells=function(t){t.forEach(function(t){t.getSize()})},p.updateSlides=function(){if(this.slides=[],this.cells.length){var n=new r(this);this.slides.push(n);var s="left"==this.originSide?"marginRight":"marginLeft",o=this._getCanCellFit();this.cells.forEach(function(t,e){if(n.cells.length){var i=n.outerWidth-n.firstMargin+(t.size.outerWidth-t.size[s]);o.call(this,e,i)||(n.updateTarget(),n=new r(this),this.slides.push(n)),n.addCell(t)}else n.addCell(t)},this),n.updateTarget(),this.updateSelectedSlide()}},p._getCanCellFit=function(){var t=this.options.groupCells;if(!t)return function(){return!1};if("number"==typeof t){var e=parseInt(t,10);return function(t){return t%e!=0}}var i="string"==typeof t&&t.match(/^(\d+)%$/),n=i?parseInt(i[1],10)/100:1;return function(t,e){return e<=(this.size.innerWidth+1)*n}},p._init=p.reposition=function(){this.positionCells(),this.positionSliderAtSelected()},p.getSize=function(){this.size=e(this.element),this.setCellAlign(),this.cursorPosition=this.size.innerWidth*this.cellAlign};var g={center:{left:.5,right:.5},left:{left:0,right:1},right:{right:0,left:1}};return p.setCellAlign=function(){var t=g[this.options.cellAlign];this.cellAlign=t?t[this.originSide]:this.options.cellAlign},p.setGallerySize=function(){if(this.options.setGallerySize){var t=this.options.adaptiveHeight&&this.selectedSlide?this.selectedSlide.height:this.maxCellHeight;this.viewport.style.height=t+"px"}},p._getWrapShiftCells=function(){if(this.options.wrapAround){this._unshiftCells(this.beforeShiftCells),this._unshiftCells(this.afterShiftCells);var t=this.cursorPosition,e=this.cells.length-1;this.beforeShiftCells=this._getGapCells(t,e,-1),t=this.size.innerWidth-this.cursorPosition,this.afterShiftCells=this._getGapCells(t,0,1)}},p._getGapCells=function(t,e,i){for(var n=[];0<t;){var s=this.cells[e];if(!s)break;n.push(s),e+=i,t-=s.size.outerWidth}return n},p._containSlides=function(){if(this.options.contain&&!this.options.wrapAround&&this.cells.length){var t=this.options.rightToLeft,e=t?"marginRight":"marginLeft",i=t?"marginLeft":"marginRight",n=this.slideableWidth-this.getLastCell().size[i],s=n<this.size.innerWidth,o=this.cursorPosition+this.cells[0].size[e],r=n-this.size.innerWidth*(1-this.cellAlign);this.slides.forEach(function(t){s?t.target=n*this.cellAlign:(t.target=Math.max(t.target,o),t.target=Math.min(t.target,r))},this)}},p.dispatchEvent=function(t,e,i){var n=e?[e].concat(i):i;if(this.emitEvent(t,n),l&&this.$element){var s=t+=this.options.namespaceJQueryEvents?".flickity":"";if(e){var o=l.Event(e);o.type=t,s=o}this.$element.trigger(s,i)}},p.select=function(t,e,i){if(this.isActive&&(t=parseInt(t,10),this._wrapSelect(t),(this.options.wrapAround||e)&&(t=a.modulo(t,this.slides.length)),this.slides[t])){var n=this.selectedIndex;this.selectedIndex=t,this.updateSelectedSlide(),i?this.positionSliderAtSelected():this.startAnimation(),this.options.adaptiveHeight&&this.setGallerySize(),this.dispatchEvent("select",null,[t]),t!=n&&this.dispatchEvent("change",null,[t]),this.dispatchEvent("cellSelect")}},p._wrapSelect=function(t){var e=this.slides.length;if(!(this.options.wrapAround&&1<e))return t;var i=a.modulo(t,e),n=Math.abs(i-this.selectedIndex),s=Math.abs(i+e-this.selectedIndex),o=Math.abs(i-e-this.selectedIndex);!this.isDragSelect&&s<n?t+=e:!this.isDragSelect&&o<n&&(t-=e),t<0?this.x-=this.slideableWidth:e<=t&&(this.x+=this.slideableWidth)},p.previous=function(t,e){this.select(this.selectedIndex-1,t,e)},p.next=function(t,e){this.select(this.selectedIndex+1,t,e)},p.updateSelectedSlide=function(){var t=this.slides[this.selectedIndex];t&&(this.unselectSelectedSlide(),(this.selectedSlide=t).select(),this.selectedCells=t.cells,this.selectedElements=t.getCellElements(),this.selectedCell=t.cells[0],this.selectedElement=this.selectedElements[0])},p.unselectSelectedSlide=function(){this.selectedSlide&&this.selectedSlide.unselect()},p.selectInitialIndex=function(){var t=this.options.initialIndex;if(this.isInitActivated)this.select(this.selectedIndex,!1,!0);else{if(t&&"string"==typeof t)if(this.queryCell(t))return void this.selectCell(t,!1,!0);var e=0;t&&this.slides[t]&&(e=t),this.select(e,!1,!0)}},p.selectCell=function(t,e,i){var n=this.queryCell(t);if(n){var s=this.getCellSlideIndex(n);this.select(s,e,i)}},p.getCellSlideIndex=function(t){for(var e=0;e<this.slides.length;e++){if(-1!=this.slides[e].cells.indexOf(t))return e}},p.getCell=function(t){for(var e=0;e<this.cells.length;e++){var i=this.cells[e];if(i.element==t)return i}},p.getCells=function(t){t=a.makeArray(t);var i=[];return t.forEach(function(t){var e=this.getCell(t);e&&i.push(e)},this),i},p.getCellElements=function(){return this.cells.map(function(t){return t.element})},p.getParentCell=function(t){var e=this.getCell(t);return e||(t=a.getParent(t,".flickity-slider > *"),this.getCell(t))},p.getAdjacentCellElements=function(t,e){if(!t)return this.selectedSlide.getCellElements();e=void 0===e?this.selectedIndex:e;var i=this.slides.length;if(i<=1+2*t)return this.getCellElements();for(var n=[],s=e-t;s<=e+t;s++){var o=this.options.wrapAround?a.modulo(s,i):s,r=this.slides[o];r&&(n=n.concat(r.getCellElements()))}return n},p.queryCell=function(t){if("number"==typeof t)return this.cells[t];if("string"==typeof t){if(t.match(/^[#\.]?[\d\/]/))return;t=this.element.querySelector(t)}return this.getCell(t)},p.uiChange=function(){this.emitEvent("uiChange")},p.childUIPointerDown=function(t){"touchstart"!=t.type&&t.preventDefault(),this.focus()},p.onresize=function(){this.watchCSS(),this.resize()},a.debounceMethod(f,"onresize",150),p.resize=function(){if(this.isActive){this.getSize(),this.options.wrapAround&&(this.x=a.modulo(this.x,this.slideableWidth)),this.positionCells(),this._getWrapShiftCells(),this.setGallerySize(),this.emitEvent("resize");var t=this.selectedElements&&this.selectedElements[0];this.selectCell(t,!1,!0)}},p.watchCSS=function(){this.options.watchCSS&&(-1!=o(this.element,":after").content.indexOf("flickity")?this.activate():this.deactivate())},p.onkeydown=function(t){var e=document.activeElement&&document.activeElement!=this.element;if(this.options.accessibility&&!e){var i=f.keyboardHandlers[t.keyCode];i&&i.call(this)}},f.keyboardHandlers={37:function(){var t=this.options.rightToLeft?"next":"previous";this.uiChange(),this[t]()},39:function(){var t=this.options.rightToLeft?"previous":"next";this.uiChange(),this[t]()}},p.focus=function(){var t=n.pageYOffset;this.element.focus({preventScroll:!0}),n.pageYOffset!=t&&n.scrollTo(n.pageXOffset,t)},p.deactivate=function(){this.isActive&&(this.element.classList.remove("flickity-enabled"),this.element.classList.remove("flickity-rtl"),this.unselectSelectedSlide(),this.cells.forEach(function(t){t.destroy()}),this.element.removeChild(this.viewport),c(this.slider.children,this.element),this.options.accessibility&&(this.element.removeAttribute("tabIndex"),this.element.removeEventListener("keydown",this)),this.isActive=!1,this.emitEvent("deactivate"))},p.destroy=function(){this.deactivate(),n.removeEventListener("resize",this),this.allOff(),this.emitEvent("destroy"),l&&this.$element&&l.removeData(this.element,"flickity"),delete this.element.flickityGUID,delete u[this.guid]},a.extend(p,s),f.data=function(t){var e=(t=a.getQueryElement(t))&&t.flickityGUID;return e&&u[e]},a.htmlInit(f,"flickity"),l&&l.bridget&&l.bridget("flickity",f),f.setJQuery=function(t){l=t},f.Cell=i,f.Slide=r,f}),function(e,i){"function"==typeof define&&define.amd?define("unipointer/unipointer",["ev-emitter/ev-emitter"],function(t){return i(e,t)}):"object"==typeof module&&module.exports?module.exports=i(e,require("ev-emitter")):e.Unipointer=i(e,e.EvEmitter)}(window,function(s,t){function e(){}var i=e.prototype=Object.create(t.prototype);i.bindStartEvent=function(t){this._bindStartEvent(t,!0)},i.unbindStartEvent=function(t){this._bindStartEvent(t,!1)},i._bindStartEvent=function(t,e){var i=(e=void 0===e||e)?"addEventListener":"removeEventListener",n="mousedown";s.PointerEvent?n="pointerdown":"ontouchstart"in s&&(n="touchstart"),t[i](n,this)},i.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},i.getTouch=function(t){for(var e=0;e<t.length;e++){var i=t[e];if(i.identifier==this.pointerIdentifier)return i}},i.onmousedown=function(t){var e=t.button;e&&0!==e&&1!==e||this._pointerDown(t,t)},i.ontouchstart=function(t){this._pointerDown(t,t.changedTouches[0])},i.onpointerdown=function(t){this._pointerDown(t,t)},i._pointerDown=function(t,e){t.button||this.isPointerDown||(this.isPointerDown=!0,this.pointerIdentifier=void 0!==e.pointerId?e.pointerId:e.identifier,this.pointerDown(t,e))},i.pointerDown=function(t,e){this._bindPostStartEvents(t),this.emitEvent("pointerDown",[t,e])};var n={mousedown:["mousemove","mouseup"],touchstart:["touchmove","touchend","touchcancel"],pointerdown:["pointermove","pointerup","pointercancel"]};return i._bindPostStartEvents=function(t){if(t){var e=n[t.type];e.forEach(function(t){s.addEventListener(t,this)},this),this._boundPointerEvents=e}},i._unbindPostStartEvents=function(){this._boundPointerEvents&&(this._boundPointerEvents.forEach(function(t){s.removeEventListener(t,this)},this),delete this._boundPointerEvents)},i.onmousemove=function(t){this._pointerMove(t,t)},i.onpointermove=function(t){t.pointerId==this.pointerIdentifier&&this._pointerMove(t,t)},i.ontouchmove=function(t){var e=this.getTouch(t.changedTouches);e&&this._pointerMove(t,e)},i._pointerMove=function(t,e){this.pointerMove(t,e)},i.pointerMove=function(t,e){this.emitEvent("pointerMove",[t,e])},i.onmouseup=function(t){this._pointerUp(t,t)},i.onpointerup=function(t){t.pointerId==this.pointerIdentifier&&this._pointerUp(t,t)},i.ontouchend=function(t){var e=this.getTouch(t.changedTouches);e&&this._pointerUp(t,e)},i._pointerUp=function(t,e){this._pointerDone(),this.pointerUp(t,e)},i.pointerUp=function(t,e){this.emitEvent("pointerUp",[t,e])},i._pointerDone=function(){this._pointerReset(),this._unbindPostStartEvents(),this.pointerDone()},i._pointerReset=function(){this.isPointerDown=!1,delete this.pointerIdentifier},i.pointerDone=function(){},i.onpointercancel=function(t){t.pointerId==this.pointerIdentifier&&this._pointerCancel(t,t)},i.ontouchcancel=function(t){var e=this.getTouch(t.changedTouches);e&&this._pointerCancel(t,e)},i._pointerCancel=function(t,e){this._pointerDone(),this.pointerCancel(t,e)},i.pointerCancel=function(t,e){this.emitEvent("pointerCancel",[t,e])},e.getPointerPoint=function(t){return{x:t.pageX,y:t.pageY}},e}),function(e,i){"function"==typeof define&&define.amd?define("unidragger/unidragger",["unipointer/unipointer"],function(t){return i(e,t)}):"object"==typeof module&&module.exports?module.exports=i(e,require("unipointer")):e.Unidragger=i(e,e.Unipointer)}(window,function(o,t){function e(){}var i=e.prototype=Object.create(t.prototype);i.bindHandles=function(){this._bindHandles(!0)},i.unbindHandles=function(){this._bindHandles(!1)},i._bindHandles=function(t){for(var e=(t=void 0===t||t)?"addEventListener":"removeEventListener",i=t?this._touchActionValue:"",n=0;n<this.handles.length;n++){var s=this.handles[n];this._bindStartEvent(s,t),s[e]("click",this),o.PointerEvent&&(s.style.touchAction=i)}},i._touchActionValue="none",i.pointerDown=function(t,e){this.okayPointerDown(t)&&(this.pointerDownPointer=e,t.preventDefault(),this.pointerDownBlur(),this._bindPostStartEvents(t),this.emitEvent("pointerDown",[t,e]))};var s={TEXTAREA:!0,INPUT:!0,SELECT:!0,OPTION:!0},r={radio:!0,checkbox:!0,button:!0,submit:!0,image:!0,file:!0};return i.okayPointerDown=function(t){var e=s[t.target.nodeName],i=r[t.target.type],n=!e||i;return n||this._pointerReset(),n},i.pointerDownBlur=function(){var t=document.activeElement;t&&t.blur&&t!=document.body&&t.blur()},i.pointerMove=function(t,e){var i=this._dragPointerMove(t,e);this.emitEvent("pointerMove",[t,e,i]),this._dragMove(t,e,i)},i._dragPointerMove=function(t,e){var i={x:e.pageX-this.pointerDownPointer.pageX,y:e.pageY-this.pointerDownPointer.pageY};return!this.isDragging&&this.hasDragStarted(i)&&this._dragStart(t,e),i},i.hasDragStarted=function(t){return 3<Math.abs(t.x)||3<Math.abs(t.y)},i.pointerUp=function(t,e){this.emitEvent("pointerUp",[t,e]),this._dragPointerUp(t,e)},i._dragPointerUp=function(t,e){this.isDragging?this._dragEnd(t,e):this._staticClick(t,e)},i._dragStart=function(t,e){this.isDragging=!0,this.isPreventingClicks=!0,this.dragStart(t,e)},i.dragStart=function(t,e){this.emitEvent("dragStart",[t,e])},i._dragMove=function(t,e,i){this.isDragging&&this.dragMove(t,e,i)},i.dragMove=function(t,e,i){t.preventDefault(),this.emitEvent("dragMove",[t,e,i])},i._dragEnd=function(t,e){this.isDragging=!1,setTimeout(function(){delete this.isPreventingClicks}.bind(this)),this.dragEnd(t,e)},i.dragEnd=function(t,e){this.emitEvent("dragEnd",[t,e])},i.onclick=function(t){this.isPreventingClicks&&t.preventDefault()},i._staticClick=function(t,e){this.isIgnoringMouseUp&&"mouseup"==t.type||(this.staticClick(t,e),"mouseup"!=t.type&&(this.isIgnoringMouseUp=!0,setTimeout(function(){delete this.isIgnoringMouseUp}.bind(this),400)))},i.staticClick=function(t,e){this.emitEvent("staticClick",[t,e])},e.getPointerPoint=t.getPointerPoint,e}),function(n,s){"function"==typeof define&&define.amd?define("flickity/js/drag",["./flickity","unidragger/unidragger","fizzy-ui-utils/utils"],function(t,e,i){return s(n,t,e,i)}):"object"==typeof module&&module.exports?module.exports=s(n,require("./flickity"),require("unidragger"),require("fizzy-ui-utils")):n.Flickity=s(n,n.Flickity,n.Unidragger,n.fizzyUIUtils)}(window,function(i,t,e,a){a.extend(t.defaults,{draggable:">1",dragThreshold:3}),t.createMethods.push("_createDrag");var n=t.prototype;a.extend(n,e.prototype),n._touchActionValue="pan-y";var s="createTouch"in document,o=!1;n._createDrag=function(){this.on("activate",this.onActivateDrag),this.on("uiChange",this._uiChangeDrag),this.on("deactivate",this.onDeactivateDrag),this.on("cellChange",this.updateDraggable),s&&!o&&(i.addEventListener("touchmove",function(){}),o=!0)},n.onActivateDrag=function(){this.handles=[this.viewport],this.bindHandles(),this.updateDraggable()},n.onDeactivateDrag=function(){this.unbindHandles(),this.element.classList.remove("is-draggable")},n.updateDraggable=function(){">1"==this.options.draggable?this.isDraggable=1<this.slides.length:this.isDraggable=this.options.draggable,this.isDraggable?this.element.classList.add("is-draggable"):this.element.classList.remove("is-draggable")},n.bindDrag=function(){this.options.draggable=!0,this.updateDraggable()},n.unbindDrag=function(){this.options.draggable=!1,this.updateDraggable()},n._uiChangeDrag=function(){delete this.isFreeScrolling},n.pointerDown=function(t,e){this.isDraggable?this.okayPointerDown(t)&&(this._pointerDownPreventDefault(t),this.pointerDownFocus(t),document.activeElement!=this.element&&this.pointerDownBlur(),this.dragX=this.x,this.viewport.classList.add("is-pointer-down"),this.pointerDownScroll=l(),i.addEventListener("scroll",this),this._pointerDownDefault(t,e)):this._pointerDownDefault(t,e)},n._pointerDownDefault=function(t,e){this.pointerDownPointer={pageX:e.pageX,pageY:e.pageY},this._bindPostStartEvents(t),this.dispatchEvent("pointerDown",t,[e])};var r={INPUT:!0,TEXTAREA:!0,SELECT:!0};function l(){return{x:i.pageXOffset,y:i.pageYOffset}}return n.pointerDownFocus=function(t){r[t.target.nodeName]||this.focus()},n._pointerDownPreventDefault=function(t){var e="touchstart"==t.type,i="touch"==t.pointerType,n=r[t.target.nodeName];e||i||n||t.preventDefault()},n.hasDragStarted=function(t){return Math.abs(t.x)>this.options.dragThreshold},n.pointerUp=function(t,e){delete this.isTouchScrolling,this.viewport.classList.remove("is-pointer-down"),this.dispatchEvent("pointerUp",t,[e]),this._dragPointerUp(t,e)},n.pointerDone=function(){i.removeEventListener("scroll",this),delete this.pointerDownScroll},n.dragStart=function(t,e){this.isDraggable&&(this.dragStartPosition=this.x,this.startAnimation(),i.removeEventListener("scroll",this),this.dispatchEvent("dragStart",t,[e]))},n.pointerMove=function(t,e){var i=this._dragPointerMove(t,e);this.dispatchEvent("pointerMove",t,[e,i]),this._dragMove(t,e,i)},n.dragMove=function(t,e,i){if(this.isDraggable){t.preventDefault(),this.previousDragX=this.dragX;var n=this.options.rightToLeft?-1:1;this.options.wrapAround&&(i.x=i.x%this.slideableWidth);var s=this.dragStartPosition+i.x*n;if(!this.options.wrapAround&&this.slides.length){var o=Math.max(-this.slides[0].target,this.dragStartPosition);s=o<s?.5*(s+o):s;var r=Math.min(-this.getLastSlide().target,this.dragStartPosition);s=s<r?.5*(s+r):s}this.dragX=s,this.dragMoveTime=new Date,this.dispatchEvent("dragMove",t,[e,i])}},n.dragEnd=function(t,e){if(this.isDraggable){this.options.freeScroll&&(this.isFreeScrolling=!0);var i=this.dragEndRestingSelect();if(this.options.freeScroll&&!this.options.wrapAround){var n=this.getRestingPosition();this.isFreeScrolling=-n>this.slides[0].target&&-n<this.getLastSlide().target}else this.options.freeScroll||i!=this.selectedIndex||(i+=this.dragEndBoostSelect());delete this.previousDragX,this.isDragSelect=this.options.wrapAround,this.select(i),delete this.isDragSelect,this.dispatchEvent("dragEnd",t,[e])}},n.dragEndRestingSelect=function(){var t=this.getRestingPosition(),e=Math.abs(this.getSlideDistance(-t,this.selectedIndex)),i=this._getClosestResting(t,e,1),n=this._getClosestResting(t,e,-1);return i.distance<n.distance?i.index:n.index},n._getClosestResting=function(t,e,i){for(var n=this.selectedIndex,s=1/0,o=this.options.contain&&!this.options.wrapAround?function(t,e){return t<=e}:function(t,e){return t<e};o(e,s)&&(n+=i,s=e,null!==(e=this.getSlideDistance(-t,n)));)e=Math.abs(e);return{distance:s,index:n-i}},n.getSlideDistance=function(t,e){var i=this.slides.length,n=this.options.wrapAround&&1<i,s=n?a.modulo(e,i):e,o=this.slides[s];if(!o)return null;var r=n?this.slideableWidth*Math.floor(e/i):0;return t-(o.target+r)},n.dragEndBoostSelect=function(){if(void 0===this.previousDragX||!this.dragMoveTime||100<new Date-this.dragMoveTime)return 0;var t=this.getSlideDistance(-this.dragX,this.selectedIndex),e=this.previousDragX-this.dragX;return 0<t&&0<e?1:t<0&&e<0?-1:0},n.staticClick=function(t,e){var i=this.getParentCell(t.target),n=i&&i.element,s=i&&this.cells.indexOf(i);this.dispatchEvent("staticClick",t,[e,n,s])},n.onscroll=function(){var t=l(),e=this.pointerDownScroll.x-t.x,i=this.pointerDownScroll.y-t.y;(3<Math.abs(e)||3<Math.abs(i))&&this._pointerDone()},t}),function(n,s){"function"==typeof define&&define.amd?define("flickity/js/prev-next-button",["./flickity","unipointer/unipointer","fizzy-ui-utils/utils"],function(t,e,i){return s(n,t,e,i)}):"object"==typeof module&&module.exports?module.exports=s(n,require("./flickity"),require("unipointer"),require("fizzy-ui-utils")):s(n,n.Flickity,n.Unipointer,n.fizzyUIUtils)}(window,function(t,e,i,n){"use strict";var s="http://www.w3.org/2000/svg";function o(t,e){this.direction=t,this.parent=e,this._create()}(o.prototype=Object.create(i.prototype))._create=function(){this.isEnabled=!0,this.isPrevious=-1==this.direction;var t=this.parent.options.rightToLeft?1:-1;this.isLeft=this.direction==t;var e=this.element=document.createElement("button");e.className="flickity-button flickity-prev-next-button",e.className+=this.isPrevious?" previous":" next",e.setAttribute("type","button"),this.disable(),e.setAttribute("aria-label",this.isPrevious?"Previous":"Next");var i=this.createSVG();e.appendChild(i),this.parent.on("select",this.update.bind(this)),this.on("pointerDown",this.parent.childUIPointerDown.bind(this.parent))},o.prototype.activate=function(){this.bindStartEvent(this.element),this.element.addEventListener("click",this),this.parent.element.appendChild(this.element)},o.prototype.deactivate=function(){this.parent.element.removeChild(this.element),this.unbindStartEvent(this.element),this.element.removeEventListener("click",this)},o.prototype.createSVG=function(){var t=document.createElementNS(s,"svg");t.setAttribute("class","flickity-button-icon"),t.setAttribute("viewBox","0 0 100 100");var e=document.createElementNS(s,"path"),i=function(t){return"string"!=typeof t?"M "+t.x0+",50 L "+t.x1+","+(t.y1+50)+" L "+t.x2+","+(t.y2+50)+" L "+t.x3+",50  L "+t.x2+","+(50-t.y2)+" L "+t.x1+","+(50-t.y1)+" Z":t}(this.parent.options.arrowShape);return e.setAttribute("d",i),e.setAttribute("class","arrow"),this.isLeft||e.setAttribute("transform","translate(100, 100) rotate(180) "),t.appendChild(e),t},o.prototype.handleEvent=n.handleEvent,o.prototype.onclick=function(){if(this.isEnabled){this.parent.uiChange();var t=this.isPrevious?"previous":"next";this.parent[t]()}},o.prototype.enable=function(){this.isEnabled||(this.element.disabled=!1,this.isEnabled=!0)},o.prototype.disable=function(){this.isEnabled&&(this.element.disabled=!0,this.isEnabled=!1)},o.prototype.update=function(){var t=this.parent.slides;if(this.parent.options.wrapAround&&1<t.length)this.enable();else{var e=t.length?t.length-1:0,i=this.isPrevious?0:e;this[this.parent.selectedIndex==i?"disable":"enable"]()}},o.prototype.destroy=function(){this.deactivate(),this.allOff()},n.extend(e.defaults,{prevNextButtons:!0,arrowShape:{x0:10,x1:60,y1:50,x2:70,y2:40,x3:30}}),e.createMethods.push("_createPrevNextButtons");var r=e.prototype;return r._createPrevNextButtons=function(){this.options.prevNextButtons&&(this.prevButton=new o(-1,this),this.nextButton=new o(1,this),this.on("activate",this.activatePrevNextButtons))},r.activatePrevNextButtons=function(){this.prevButton.activate(),this.nextButton.activate(),this.on("deactivate",this.deactivatePrevNextButtons)},r.deactivatePrevNextButtons=function(){this.prevButton.deactivate(),this.nextButton.deactivate(),this.off("deactivate",this.deactivatePrevNextButtons)},e.PrevNextButton=o,e}),function(n,s){"function"==typeof define&&define.amd?define("flickity/js/page-dots",["./flickity","unipointer/unipointer","fizzy-ui-utils/utils"],function(t,e,i){return s(n,t,e,i)}):"object"==typeof module&&module.exports?module.exports=s(n,require("./flickity"),require("unipointer"),require("fizzy-ui-utils")):s(n,n.Flickity,n.Unipointer,n.fizzyUIUtils)}(window,function(t,e,i,n){function s(t){this.parent=t,this._create()}(s.prototype=Object.create(i.prototype))._create=function(){this.holder=document.createElement("ol"),this.holder.className="flickity-page-dots",this.dots=[],this.handleClick=this.onClick.bind(this),this.on("pointerDown",this.parent.childUIPointerDown.bind(this.parent))},s.prototype.activate=function(){this.setDots(),this.holder.addEventListener("click",this.handleClick),this.bindStartEvent(this.holder),this.parent.element.appendChild(this.holder)},s.prototype.deactivate=function(){this.holder.removeEventListener("click",this.handleClick),this.unbindStartEvent(this.holder),this.parent.element.removeChild(this.holder)},s.prototype.setDots=function(){var t=this.parent.slides.length-this.dots.length;0<t?this.addDots(t):t<0&&this.removeDots(-t)},s.prototype.addDots=function(t){for(var e=document.createDocumentFragment(),i=[],n=this.dots.length,s=n+t,o=n;o<s;o++){var r=document.createElement("li");r.className="dot",r.setAttribute("aria-label","Page dot "+(o+1)),e.appendChild(r),i.push(r)}this.holder.appendChild(e),this.dots=this.dots.concat(i)},s.prototype.removeDots=function(t){this.dots.splice(this.dots.length-t,t).forEach(function(t){this.holder.removeChild(t)},this)},s.prototype.updateSelected=function(){this.selectedDot&&(this.selectedDot.className="dot",this.selectedDot.removeAttribute("aria-current")),this.dots.length&&(this.selectedDot=this.dots[this.parent.selectedIndex],this.selectedDot.className="dot is-selected",this.selectedDot.setAttribute("aria-current","step"))},s.prototype.onTap=s.prototype.onClick=function(t){var e=t.target;if("LI"==e.nodeName){this.parent.uiChange();var i=this.dots.indexOf(e);this.parent.select(i)}},s.prototype.destroy=function(){this.deactivate(),this.allOff()},e.PageDots=s,n.extend(e.defaults,{pageDots:!0}),e.createMethods.push("_createPageDots");var o=e.prototype;return o._createPageDots=function(){this.options.pageDots&&(this.pageDots=new s(this),this.on("activate",this.activatePageDots),this.on("select",this.updateSelectedPageDots),this.on("cellChange",this.updatePageDots),this.on("resize",this.updatePageDots),this.on("deactivate",this.deactivatePageDots))},o.activatePageDots=function(){this.pageDots.activate()},o.updateSelectedPageDots=function(){this.pageDots.updateSelected()},o.updatePageDots=function(){this.pageDots.setDots()},o.deactivatePageDots=function(){this.pageDots.deactivate()},e.PageDots=s,e}),function(t,n){"function"==typeof define&&define.amd?define("flickity/js/player",["ev-emitter/ev-emitter","fizzy-ui-utils/utils","./flickity"],function(t,e,i){return n(t,e,i)}):"object"==typeof module&&module.exports?module.exports=n(require("ev-emitter"),require("fizzy-ui-utils"),require("./flickity")):n(t.EvEmitter,t.fizzyUIUtils,t.Flickity)}(window,function(t,e,i){function n(t){this.parent=t,this.state="stopped",this.onVisibilityChange=this.visibilityChange.bind(this),this.onVisibilityPlay=this.visibilityPlay.bind(this)}(n.prototype=Object.create(t.prototype)).play=function(){"playing"!=this.state&&(document.hidden?document.addEventListener("visibilitychange",this.onVisibilityPlay):(this.state="playing",document.addEventListener("visibilitychange",this.onVisibilityChange),this.tick()))},n.prototype.tick=function(){if("playing"==this.state){var t=this.parent.options.autoPlay;t="number"==typeof t?t:3e3;var e=this;this.clear(),this.timeout=setTimeout(function(){e.parent.next(!0),e.tick()},t)}},n.prototype.stop=function(){this.state="stopped",this.clear(),document.removeEventListener("visibilitychange",this.onVisibilityChange)},n.prototype.clear=function(){clearTimeout(this.timeout)},n.prototype.pause=function(){"playing"==this.state&&(this.state="paused",this.clear())},n.prototype.unpause=function(){"paused"==this.state&&this.play()},n.prototype.visibilityChange=function(){this[document.hidden?"pause":"unpause"]()},n.prototype.visibilityPlay=function(){this.play(),document.removeEventListener("visibilitychange",this.onVisibilityPlay)},e.extend(i.defaults,{pauseAutoPlayOnHover:!0}),i.createMethods.push("_createPlayer");var s=i.prototype;return s._createPlayer=function(){this.player=new n(this),this.on("activate",this.activatePlayer),this.on("uiChange",this.stopPlayer),this.on("pointerDown",this.stopPlayer),this.on("deactivate",this.deactivatePlayer)},s.activatePlayer=function(){this.options.autoPlay&&(this.player.play(),this.element.addEventListener("mouseenter",this))},s.playPlayer=function(){this.player.play()},s.stopPlayer=function(){this.player.stop()},s.pausePlayer=function(){this.player.pause()},s.unpausePlayer=function(){this.player.unpause()},s.deactivatePlayer=function(){this.player.stop(),this.element.removeEventListener("mouseenter",this)},s.onmouseenter=function(){this.options.pauseAutoPlayOnHover&&(this.player.pause(),this.element.addEventListener("mouseleave",this))},s.onmouseleave=function(){this.player.unpause(),this.element.removeEventListener("mouseleave",this)},i.Player=n,i}),function(i,n){"function"==typeof define&&define.amd?define("flickity/js/add-remove-cell",["./flickity","fizzy-ui-utils/utils"],function(t,e){return n(i,t,e)}):"object"==typeof module&&module.exports?module.exports=n(i,require("./flickity"),require("fizzy-ui-utils")):n(i,i.Flickity,i.fizzyUIUtils)}(window,function(t,e,n){var i=e.prototype;return i.insert=function(t,e){var i=this._makeCells(t);if(i&&i.length){var n=this.cells.length;e=void 0===e?n:e;var s=function(t){var e=document.createDocumentFragment();return t.forEach(function(t){e.appendChild(t.element)}),e}(i),o=e==n;if(o)this.slider.appendChild(s);else{var r=this.cells[e].element;this.slider.insertBefore(s,r)}if(0===e)this.cells=i.concat(this.cells);else if(o)this.cells=this.cells.concat(i);else{var a=this.cells.splice(e,n-e);this.cells=this.cells.concat(i).concat(a)}this._sizeCells(i),this.cellChange(e,!0)}},i.append=function(t){this.insert(t,this.cells.length)},i.prepend=function(t){this.insert(t,0)},i.remove=function(t){var e=this.getCells(t);if(e&&e.length){var i=this.cells.length-1;e.forEach(function(t){t.remove();var e=this.cells.indexOf(t);i=Math.min(e,i),n.removeFrom(this.cells,t)},this),this.cellChange(i,!0)}},i.cellSizeChange=function(t){var e=this.getCell(t);if(e){e.getSize();var i=this.cells.indexOf(e);this.cellChange(i)}},i.cellChange=function(t,e){var i=this.selectedElement;this._positionCells(t),this._getWrapShiftCells(),this.setGallerySize();var n=this.getCell(i);n&&(this.selectedIndex=this.getCellSlideIndex(n)),this.selectedIndex=Math.min(this.slides.length-1,this.selectedIndex),this.emitEvent("cellChange",[t]),this.select(this.selectedIndex),e&&this.positionSliderAtSelected()},e}),function(i,n){"function"==typeof define&&define.amd?define("flickity/js/lazyload",["./flickity","fizzy-ui-utils/utils"],function(t,e){return n(i,t,e)}):"object"==typeof module&&module.exports?module.exports=n(i,require("./flickity"),require("fizzy-ui-utils")):n(i,i.Flickity,i.fizzyUIUtils)}(window,function(t,e,o){"use strict";e.createMethods.push("_createLazyload");var i=e.prototype;function s(t,e){this.img=t,this.flickity=e,this.load()}return i._createLazyload=function(){this.on("select",this.lazyLoad)},i.lazyLoad=function(){var t=this.options.lazyLoad;if(t){var e="number"==typeof t?t:0,i=this.getAdjacentCellElements(e),n=[];i.forEach(function(t){var e=function(t){if("IMG"==t.nodeName){var e=t.getAttribute("data-flickity-lazyload"),i=t.getAttribute("data-flickity-lazyload-src"),n=t.getAttribute("data-flickity-lazyload-srcset");if(e||i||n)return[t]}var s=t.querySelectorAll("img[data-flickity-lazyload], img[data-flickity-lazyload-src], img[data-flickity-lazyload-srcset]");return o.makeArray(s)}(t);n=n.concat(e)}),n.forEach(function(t){new s(t,this)},this)}},s.prototype.handleEvent=o.handleEvent,s.prototype.load=function(){this.img.addEventListener("load",this),this.img.addEventListener("error",this);var t=this.img.getAttribute("data-flickity-lazyload")||this.img.getAttribute("data-flickity-lazyload-src"),e=this.img.getAttribute("data-flickity-lazyload-srcset");this.img.src=t,e&&this.img.setAttribute("srcset",e),this.img.removeAttribute("data-flickity-lazyload"),this.img.removeAttribute("data-flickity-lazyload-src"),this.img.removeAttribute("data-flickity-lazyload-srcset")},s.prototype.onload=function(t){this.complete(t,"flickity-lazyloaded")},s.prototype.onerror=function(t){this.complete(t,"flickity-lazyerror")},s.prototype.complete=function(t,e){this.img.removeEventListener("load",this),this.img.removeEventListener("error",this);var i=this.flickity.getParentCell(this.img),n=i&&i.element;this.flickity.cellSizeChange(n),this.img.classList.add(e),this.flickity.dispatchEvent("lazyLoad",t,n)},e.LazyLoader=s,e}),function(t,e){"function"==typeof define&&define.amd?define("flickity/js/index",["./flickity","./drag","./prev-next-button","./page-dots","./player","./add-remove-cell","./lazyload"],e):"object"==typeof module&&module.exports&&(module.exports=e(require("./flickity"),require("./drag"),require("./prev-next-button"),require("./page-dots"),require("./player"),require("./add-remove-cell"),require("./lazyload")))}(window,function(t){return t}),function(t,e){"function"==typeof define&&define.amd?define("flickity-as-nav-for/as-nav-for",["flickity/js/index","fizzy-ui-utils/utils"],e):"object"==typeof module&&module.exports?module.exports=e(require("flickity"),require("fizzy-ui-utils")):t.Flickity=e(t.Flickity,t.fizzyUIUtils)}(window,function(n,s){n.createMethods.push("_createAsNavFor");var t=n.prototype;return t._createAsNavFor=function(){this.on("activate",this.activateAsNavFor),this.on("deactivate",this.deactivateAsNavFor),this.on("destroy",this.destroyAsNavFor);var t=this.options.asNavFor;if(t){var e=this;setTimeout(function(){e.setNavCompanion(t)})}},t.setNavCompanion=function(t){t=s.getQueryElement(t);var e=n.data(t);if(e&&e!=this){this.navCompanion=e;var i=this;this.onNavCompanionSelect=function(){i.navCompanionSelect()},e.on("select",this.onNavCompanionSelect),this.on("staticClick",this.onNavStaticClick),this.navCompanionSelect(!0)}},t.navCompanionSelect=function(t){var e=this.navCompanion&&this.navCompanion.selectedCells;if(e){var i=e[0],n=this.navCompanion.cells.indexOf(i),s=n+e.length-1,o=Math.floor(function(t,e,i){return(e-t)*i+t}(n,s,this.navCompanion.cellAlign));if(this.selectCell(o,!1,t),this.removeNavSelectedElements(),!(o>=this.cells.length)){var r=this.cells.slice(n,1+s);this.navSelectedElements=r.map(function(t){return t.element}),this.changeNavSelectedClass("add")}}},t.changeNavSelectedClass=function(e){this.navSelectedElements.forEach(function(t){t.classList[e]("is-nav-selected")})},t.activateAsNavFor=function(){this.navCompanionSelect(!0)},t.removeNavSelectedElements=function(){this.navSelectedElements&&(this.changeNavSelectedClass("remove"),delete this.navSelectedElements)},t.onNavStaticClick=function(t,e,i,n){"number"==typeof n&&this.navCompanion.selectCell(n)},t.deactivateAsNavFor=function(){this.removeNavSelectedElements()},t.destroyAsNavFor=function(){this.navCompanion&&(this.navCompanion.off("select",this.onNavCompanionSelect),this.off("staticClick",this.onNavStaticClick),delete this.navCompanion)},n}),function(e,i){"use strict";"function"==typeof define&&define.amd?define("imagesloaded/imagesloaded",["ev-emitter/ev-emitter"],function(t){return i(e,t)}):"object"==typeof module&&module.exports?module.exports=i(e,require("ev-emitter")):e.imagesLoaded=i(e,e.EvEmitter)}("undefined"!=typeof window?window:this,function(e,t){var s=e.jQuery,o=e.console;function r(t,e){for(var i in e)t[i]=e[i];return t}var a=Array.prototype.slice;function l(t,e,i){if(!(this instanceof l))return new l(t,e,i);var n=t;"string"==typeof t&&(n=document.querySelectorAll(t)),n?(this.elements=function(t){return Array.isArray(t)?t:"object"==typeof t&&"number"==typeof t.length?a.call(t):[t]}(n),this.options=r({},this.options),"function"==typeof e?i=e:r(this.options,e),i&&this.on("always",i),this.getImages(),s&&(this.jqDeferred=new s.Deferred),setTimeout(this.check.bind(this))):o.error("Bad element for imagesLoaded "+(n||t))}(l.prototype=Object.create(t.prototype)).options={},l.prototype.getImages=function(){this.images=[],this.elements.forEach(this.addElementImages,this)},l.prototype.addElementImages=function(t){"IMG"==t.nodeName&&this.addImage(t),!0===this.options.background&&this.addElementBackgroundImages(t);var e=t.nodeType;if(e&&h[e]){for(var i=t.querySelectorAll("img"),n=0;n<i.length;n++){var s=i[n];this.addImage(s)}if("string"==typeof this.options.background){var o=t.querySelectorAll(this.options.background);for(n=0;n<o.length;n++){var r=o[n];this.addElementBackgroundImages(r)}}}};var h={1:!0,9:!0,11:!0};function i(t){this.img=t}function n(t,e){this.url=t,this.element=e,this.img=new Image}return l.prototype.addElementBackgroundImages=function(t){var e=getComputedStyle(t);if(e)for(var i=/url\((['"])?(.*?)\1\)/gi,n=i.exec(e.backgroundImage);null!==n;){var s=n&&n[2];s&&this.addBackground(s,t),n=i.exec(e.backgroundImage)}},l.prototype.addImage=function(t){var e=new i(t);this.images.push(e)},l.prototype.addBackground=function(t,e){var i=new n(t,e);this.images.push(i)},l.prototype.check=function(){var n=this;function e(t,e,i){setTimeout(function(){n.progress(t,e,i)})}this.progressedCount=0,this.hasAnyBroken=!1,this.images.length?this.images.forEach(function(t){t.once("progress",e),t.check()}):this.complete()},l.prototype.progress=function(t,e,i){this.progressedCount++,this.hasAnyBroken=this.hasAnyBroken||!t.isLoaded,this.emitEvent("progress",[this,t,e]),this.jqDeferred&&this.jqDeferred.notify&&this.jqDeferred.notify(this,t),this.progressedCount==this.images.length&&this.complete(),this.options.debug&&o&&o.log("progress: "+i,t,e)},l.prototype.complete=function(){var t=this.hasAnyBroken?"fail":"done";if(this.isComplete=!0,this.emitEvent(t,[this]),this.emitEvent("always",[this]),this.jqDeferred){var e=this.hasAnyBroken?"reject":"resolve";this.jqDeferred[e](this)}},(i.prototype=Object.create(t.prototype)).check=function(){this.getIsImageComplete()?this.confirm(0!==this.img.naturalWidth,"naturalWidth"):(this.proxyImage=new Image,this.proxyImage.addEventListener("load",this),this.proxyImage.addEventListener("error",this),this.img.addEventListener("load",this),this.img.addEventListener("error",this),this.proxyImage.src=this.img.src)},i.prototype.getIsImageComplete=function(){return this.img.complete&&this.img.naturalWidth},i.prototype.confirm=function(t,e){this.isLoaded=t,this.emitEvent("progress",[this,this.img,e])},i.prototype.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},i.prototype.onload=function(){this.confirm(!0,"onload"),this.unbindEvents()},i.prototype.onerror=function(){this.confirm(!1,"onerror"),this.unbindEvents()},i.prototype.unbindEvents=function(){this.proxyImage.removeEventListener("load",this),this.proxyImage.removeEventListener("error",this),this.img.removeEventListener("load",this),this.img.removeEventListener("error",this)},(n.prototype=Object.create(i.prototype)).check=function(){this.img.addEventListener("load",this),this.img.addEventListener("error",this),this.img.src=this.url,this.getIsImageComplete()&&(this.confirm(0!==this.img.naturalWidth,"naturalWidth"),this.unbindEvents())},n.prototype.unbindEvents=function(){this.img.removeEventListener("load",this),this.img.removeEventListener("error",this)},n.prototype.confirm=function(t,e){this.isLoaded=t,this.emitEvent("progress",[this,this.element,e])},l.makeJQueryPlugin=function(t){(t=t||e.jQuery)&&((s=t).fn.imagesLoaded=function(t,e){return new l(this,t,e).jqDeferred.promise(s(this))})},l.makeJQueryPlugin(),l}),function(i,n){"function"==typeof define&&define.amd?define(["flickity/js/index","imagesloaded/imagesloaded"],function(t,e){return n(i,t,e)}):"object"==typeof module&&module.exports?module.exports=n(i,require("flickity"),require("imagesloaded")):i.Flickity=n(i,i.Flickity,i.imagesLoaded)}(window,function(t,e,i){"use strict";e.createMethods.push("_createImagesLoaded");var n=e.prototype;return n._createImagesLoaded=function(){this.on("activate",this.imagesLoaded)},n.imagesLoaded=function(){if(this.options.imagesLoaded){var n=this;i(this.slider).on("progress",function(t,e){var i=n.getParentCell(e.img);n.cellSizeChange(i&&i.element),n.options.freeScroll||n.positionSliderAtSelected()})}},e});



/*Toast*/
!function(t){var n=null;t.extend({toast:function(a,o){switch((o=o||{}).width=o.width||0,o.duration=o.duration||2e3,o.type=o.type||"",o.align=o.align||"bottom",o.hasOwnProperty("singleton")||(o.singleton=!0),n||(n=t("<ul></ul>").addClass("toast").appendTo(document.body).hide()),o.align){case"top":n.css({top:"0",bottom:"",margin:"40px 0 0 0"});break;case"bottom":n.css({top:"",bottom:"0",margin:"0 0 40px 0"});break;case"top-right":n.css({top:"0",right:"0",margin:"0"})}o.singleton&&n.html("");var e="<span>"+a+"</span>",i=t("<li></li>").hide();o.width>0&&(e="<span style='width: "+o.width+"px'>"+a+"</span>"),"top"==o.align?i.html(e).prependTo(n):i.html(e).appendTo(n),""!==o.type&&i.addClass(o.type);var s=null;s=setTimeout(function(){clearTimeout(s),i.animate({height:0,opacity:0},"fast",function(){i.remove(),n.children().length||n.hide()})},o.duration),n.show(),i.fadeIn("normal")}})}(jQuery);