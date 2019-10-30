$(document).ready(function () {
	AOS.init();

	var base_url = window.location.origin;
	var segments = window.location.href.split('/');
	var langFromState = segments[segments.length - 1].trim();
	var browserLanguage = window.navigator.language.split('-')[0];
	browserLanguage = browserLanguage ? browserLanguage : 'en';
	browserLanguage = ['en', 'es', 'it'].indexOf(browserLanguage) === -1 ? 'en' : browserLanguage;
	
	localStorage.removeItem('lang', browserLanguage);
	// if (!localStorage.getItem('lang') && langFromState === browserLanguage) {
	// 	window.location.href = base_url + '/' + browserLanguage;
	// 	localStorage.setItem('lang', browserLanguage);		
	// }

	$('.registration-tabs').removeClass('hidden');
	$('.angular-hidden').removeClass('angular-hidden');


	$(".choose_lang").on('change',function (e) {


		var data = {
			locale : $(".choose_lang").val()
		};

		Cookies.set('locale', data.locale , { expires: 365 });

		$.ajax({
			url : "/front-data/language",
			type: "POST",
			data : data
		}).done(function(data) {

			if(data.resource.error.no === 0) {


				window.location.assign(base_url + "/" + data.resource.error.to)
			}

		});


	});



	function template1 (tmp1) {
		if (!tmp1.id) { return tmp1.text; }
		var $tmp1 = $(
			'<span><img src="/laravel_assets/callburn/images/lang-flags/' + tmp1.element.value.toLowerCase() + '.svg" /> ' + tmp1.text + '</span>'
			);
		return $tmp1;
	};

	function formatState (state) {
		if (!state.id) { return state.text; }
		var $state = $(
			'<span><img src="/laravel_assets/callburn/images/lang-flags/' + state.element.value.toLowerCase() + '.svg" /> ' + state.text + '</span>'
			);
		return $state;
	};

	$("[name='country']").select2({
		templateResult: formatState,
		templateSelection: template1,
		width: '100%',
		minimumResultsForSearch: -1
	});

	$("#chat-launch-button-contact-page").click(function(event) {
		event.preventDefault(); 
		$crisp.push(["do", "chat:toggle"])
	});

	$("#chat-launch-button").click(function(event) {
		event.preventDefault(); 
		$crisp.push(["do", "chat:toggle"])
	});

	//nudgespot-messages-header-close

	$(".snippet-main-content").hide();
	
	$("#video_modal").on("hide.bs.modal", function (e) {
		$('iframe').attr('src', $('iframe').attr('src'));
	});

	var marker = $('#marker');
	var current = $('.active');

	marker.css({
		bottom: -(marker.height() / 2),
		left: current.position().left,
		width: current.outerWidth()
	});

	if (Modernizr.csstransitions) {
		$('.item').mouseover(function () {
			var self = $(this),
			offsetLeft = self.position().left,
		        // Use the element under the pointer OR the current page item
		        width = self.outerWidth() || current.outerWidth(),
		        // Ternary operator, because if using OR when offsetLeft is 0, it is considered a falsy value, thus causing a bug for the first element.
		        left = offsetLeft == 0 ? 0 : offsetLeft || current.position().left;
		  // Play with the active class
		  $('.active').removeClass('active');
		  self.addClass('active');
		  marker.css({
		  	left: left,
		  	width: width,
		  });
		});
		// When the mouse leaves the menu
		$('.feature-area').mouseleave(function () {
		  // remove all active classes, add active class to the current page item
		  $('.active').removeClass('active');
		  current.addClass('active');
		  // reset the marker to the current page item position and width
		  marker.css({
		  	left: current.position().left,
		  	width: current.outerWidth()
		  });
		});

	} else {
		$('.item').mouseover(function () {
			var self = $(this),
			offsetLeft = self.position().left,
		        // Use the element under the pointer OR the current page item
		        width = self.outerWidth() || current.outerWidth(),
		        // Ternary operator, because if using OR when offsetLeft is 0, it is considered a falsy value, thus causing a bug for the first element.
		        left = offsetLeft == 0 ? 0 : offsetLeft || current.position().left;
		  // Play with the active class
		  $('.item').removeClass('active');
		  self.addClass('active');
		  marker.stop().animate({
		  	left: left,
		  	width: width,
		  }, 300);
		});

		// When the mouse leaves the menu
		$('.feature-area').mouseleave(function () {
		  // remove all active classes, add active class to the current page item
		  $('.item').removeClass('active');
		  current.addClass('active');
		  // reset the marker to the current page item position and width
		  marker.stop().animate({
		  	left: current.position().left,
		  	width: current.outerWidth()
		  }, 300);
		});
	};

		var crispSetData = function () {
			localStorage.setItem('crisp_chat_sent', true);
			$crisp.push(["set", "session:data", ["has_sent_a_chat", true]]);
			dataLayer.push({
                'event': 'crisp_chat_sent'
            });
            console.log('crisp chat sent');
		}
		if (!localStorage.getItem('crisp_chat_sent')) {
			$crisp.push(["on", "message:sent", crispSetData]);
		}


		var crispLoaded = function () {
			
			var winPath = window.location.pathname;
			if (winPath.slice(4, 23) === 'finish-registration') {
				$crisp.push(["set", "user:email", [winPath.split("/").pop()]])
				$crisp.push(["set", "session:event", ["user:verification"]])
				$crisp.push(["set", "session:data", ["funnel_status","on_verification"]])
			} else {
				// check if user has sent a chat and set value if not.
				var crispData = $crisp.get("session:data", "has_sent_a_chat");
				// check if user is already registered
				var crispCheckUserId = $crisp.get("session:data", "user_id");
				crispData === null ? $crisp.push(["set", "session:data", ["has_sent_a_chat", false]]) : null;
				// init funnel0 if user isn't already registered 
				crispCheckUserId === null ? $crisp.push(["set", "session:data", ["funnel","step0"]]) : null;
			}
		}

		$crisp.push(["on", "session:loaded", crispLoaded]);
});