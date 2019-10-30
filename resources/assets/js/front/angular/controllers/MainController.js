angular.module("frontCallburnApp").controller("MainController", [
  "$scope",
  "$rootScope",
  "$window",
  "$document",
  "Restangular",
  "$timeout",
  "$interval",
  "LanguageControl",
  "$http",
  "$q",
  "$sce",
  function(
    $scope,
    $rootScope,
    $window,
    $document,
    Restangular,
    $timeout,
    $interval,
    LanguageControl,
    $http,
    $q,
    $sce
  ) {
    $rootScope.animations = [
      "rubberBand",
      "shake",
      "pulse",
      "tada",
      "jello",
      "bounceIn"
    ];

    $rootScope.socket = io();
    $rootScope.socket.on("connect", function() {
      console.log($rootScope.socket.connected);
    });

    // Restangular.one('/auth/crisp-token').get().then(function(data){
    //     $scope.crispToken = data.resource.crispToken;
    //     if (data.resource.crispToken) {
    //         $scope.haveCrispToken = true;
    //     } else {
    //         $scope.haveCrispToken = false;
    //     }
    // });
    // $scope.crispLoaded = false;
    // $scope.loadCrisp = function () {
    //     var winPath = window.location.pathname.slice(4, 23);
    //     if (!$scope.crispLoaded) {
    //         $scope.crispLoaded = true;
    //         if (winPath === 'finish-registration') {
    //             if ($scope.haveCrispToken) {
    //                 CRISP_TOKEN_ID = $scope.crispToken;CRISP_WEBSITE_ID = 'e4ef3e4c-3291-431d-bdfc-eef78a98190f';(function(){d=document;s=d.createElement('script');s.src='//client.crisp.chat/l.js';s.async=1;d.getElementsByTagName('head')[0].appendChild(s);})();
    //             } else {

    //             }
    //         } else if (winPath !== 'finish-registration' && $scope.haveCrispToken) {
    //             CRISP_TOKEN_ID = $scope.crispToken;CRISP_WEBSITE_ID = 'e4ef3e4c-3291-431d-bdfc-eef78a98190f';(function(){d=document;s=d.createElement('script');s.src='//client.crisp.chat/l.js';s.async=1;d.getElementsByTagName('head')[0].appendChild(s);})();
    //         } else {
    //             window.$crisp=[];window.CRISP_WEBSITE_ID="e4ef3e4c-3291-431d-bdfc-eef78a98190f";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();
    //         }
    //     }
    // }

    $scope.fromForgot = function() {
      sessionStorage.setItem("fromForgot", "true");
      sessionStorage.removeItem("fromLink");
    };
    $scope.fromLink = function() {
      sessionStorage.setItem("fromLink", "true");
      sessionStorage.removeItem("fromForgot");
    };

    $scope.arrowPosition = function() {
      $scope.firstBar = false;
      $scope.secondBar = false;
      $scope.thirdBar = false;
      $scope.labelBar = false;
      var elem = document.getElementsByClassName("sms_column")[0];
      var pos = -240;
      var id = setInterval(frame, 200);
      function frame() {
        pos += 10;
        elem.style.bottom = pos + "px";
        if (pos >= -180 && pos < -120 && pos !== 0) {
          $scope.firstBar = true;
        } else if (pos >= -120 && pos < -65 && pos !== 0) {
          $scope.secondBar = true;
        } else if (pos >= -65 && pos !== 0) {
          $scope.thirdBar = true;
          $scope.labelBar = true;
        } else if (pos === 0) {
          clearInterval(id);
        }
      }
    };

    var numberInput = angular.element("#example-number-input")[0];
    if (numberInput) {
      numberInput.addEventListener("mouseup", function() {
        if (numberInput.value < 20) {
          numberInput.value = 20;
        }
      });
    }

    $scope.openChat = function() {
      console.log("pushed");
      $crisp.push(["do", "chat:toggle"]);
    };

    $scope.toggleBar = false;
    $scope.toggleFunc = function() {
      $scope.toggleBar = $scope.toggleBar ? false : true;
    };
    $scope.showInput = false;

    var segments = window.location.href.split("/");
    var videoPage = segments[segments.length - 1].trim();

    var browserLanguage = (navigator.language || navigator.userLanguage).split(
      "-"
    )[0];
    browserLanguage = browserLanguage ? browserLanguage : "en";
    browserLanguage =
      ["en", "es", "it"].indexOf(browserLanguage) === -1
        ? "en"
        : browserLanguage;
    window.browserLanguage = browserLanguage;
    // console.log(browserLanguage)
    window.locale = window.browserLanguage;
    document.currentLanguage = window.locale;

    $scope.currentVideo = null;
    $scope.titles = null;

    $scope.arrayKeys = [
      "click_to_call_video_info_title",
      "click_to_call_video_tutorial_title"
    ];

    var translate = {};
    var langRequestInProcess = false;

    window.trans = $rootScope.trans = function(part, keys) {
      var data = {
        part: part,
        keys: JSON.stringify(keys)
      };
      Restangular.one("/front-data/trans")
        .get(data)
        .then(function(data) {
          $scope.translationData = data.resource.translations;
        });
    };

    window.transQueue = $rootScope.transQueue = function(part, keys) {
      var queue = $q.defer();

      if ($rootScope.trans(str)) {
        queue.resolve();
      }
      return queue.promise;
    };

    trans("crud", $scope.arrayKeys);

    $scope.showInfo = false;
    $scope.showTutorial = true;

    $scope.menuClass = false;

    $scope.showCurrentVideo = function(key) {
      if (key === "info") {
        $scope.showInfo = true;
        $scope.showTutorial = false;
      } else if (key === "tutorial") {
        $scope.showInfo = false;
        $scope.showTutorial = true;
      }
    };

    $scope.currentLanguage = window.locale ? window.locale : "en";

    Restangular.one("/front-data/playlists")
      .get()
      .then(function(data) {
        $scope.CTCVideosObj = {
          promo: data.resource.playlists.ctc.promotionals,
          tutor: data.resource.playlists.ctc.tutorials
        };
        $scope.VMVideosObj = {
          promo: data.resource.playlists.vm.promotionals,
          tutor: data.resource.playlists.vm.tutorials
        };
        $scope.CTCVideos = data.resource.playlists.ctc.promotionals.concat(
          data.resource.playlists.ctc.tutorials
        );
        $scope.VMVideos = data.resource.playlists.vm.promotionals.concat(
          data.resource.playlists.vm.tutorials
        );
      });

    $scope.trustURL = function(url) {
      return $sce.trustAsResourceUrl(url);
    };

    $scope.getThumbnail = function(key) {
      return $scope.trustURL(
        "https://i.ytimg.com/vi/" + key + "/sddefault.jpg"
      );
    };

    $scope.playVideo = function(key) {
      $scope.url = $scope.trustURL(
        "https://www.youtube.com/embed/" + key + "?rel=0"
      );
    };

    $scope.getVideoFromPlaylist = function(videoItem) {
      if (videoItem !== undefined && $scope.url === undefined) {
        return $scope.trustURL(
          "https://www.youtube.com/embed/" + videoItem.videoId + "?rel=0"
        );
      }
    };

    window.getAnimation = $rootScope.getAnimation = function() {
      return (
        "animated" +
        " " +
        $rootScope.animations[
          Math.floor(Math.random() * $rootScope.animations.length)
        ]
      );
    };

    LanguageControl.GetLanguagesList().then(function(data) {
      $rootScope.flags = [];
      $rootScope.languages = data.resource.languages;
      $rootScope.languages.forEach(function(language) {
        $rootScope.flags.push(
          "/assets/callburn/images/lang-flags/" + language.code + ".svg"
        );
      });
    });

    window.redirect = $rootScope.redirect = function(href) {
      if (href === undefined) {
        href = "";
      }

      window.location.href = "/" + href;
    };

    $rootScope.checkAuth = function(link) {
      var baseUrl = window.location.origin;
      if (localStorage.getItem("jwtToken")) {
        window.location.assign(
          baseUrl + "/" + "myaccount#/dashboard/dashboard"
        );
      } else {
        window.location.assign(baseUrl + "/" + link);
      }
    };

    $document.on("click", function(e) {
      if (e.target.id === "notificationsDropdownButton") {
        $scope.notificationsDropdown = !$scope.notificationsDropdown;
      } else if (e.target.id === "showMoreNotifications") {
        $scope.notificationsDropdown = true;
      } else if (e.target.id !== "notificationsDropdown") {
        $scope.notificationsDropdown = false;
      }
    });

    $scope.navigateTo = function(section) {
      switch (section) {
        case "#introduction":
          window.location = section;
        default:
          window.location.href = section;
          break;
      }
      if (
        section === "#introduction" ||
        section === "#authentication" ||
        section === "#links" ||
        section === "#meta"
      ) {
        $scope.introSection = true;
        $scope.vmSection = false;
        $scope.ctcSection = false;
        document.title = "Introduction - Callburn Api";
      } else if (
        section === "#voice-messages" ||
        section === "#tts-languages" ||
        section === "#audio-templates" ||
        section === "#status-of-voice-message" ||
        section === "#create-voice-message" ||
        section === "#get-voice-messages-list" ||
        section === "#get-single-voice-message" ||
        section === "#delete-single-voice-message"
      ) {
        $scope.vmSection = true;
        $scope.introSection = false;
        $scope.ctcSection = false;
        document.title = "Callmessages - Callburn Api";
      } else if (
        section === "#click-to-call" ||
        section === "#get-snippets-list" ||
        section === "#make-a-call-through-snippet"
      ) {
        $scope.ctcSection = true;
        $scope.vmSection = false;
        $scope.introSection = false;
        document.title = "ClickToCall - Callburn Api";
      } else if (section === "#errors") {
        document.title = "Errors - Callburn Api";
      }
    };

    $rootScope.slickVideosConfig = {
      asNavFor: ".slick-nav",
      dots: false,
      autoplay: false,
      infinite: true,
      mobileFirst: true,
      event: {
        // afterChange: function (event, slick, currentSlide, nextSlide) {
        //     $scope.currentIndex = currentSlide; // save current index each time
        // }
        // init: function (event, slick) {
        //     $scope.iframeSrc = angular.element('#vm-video-0')[0].attributes[2].value + "&autoplay=1";
        //     angular.element('#vm-video-0').attr("src", $scope.iframeSrc);
        // },
      },
      responsive: [
        // {
        // breakpoint: 767,
        // settings: {
        //     prevArrow: null,
        //     nextArrow: null,
        //     arrows: false,
        // }
        // },
        {
          breakpoint: 319,
          settings: {
            arrows: true
          }
        }
      ]
    };
    $rootScope.slickVideosNav = {
      asNavFor: ".slick-for",
      dots: false,
      infinite: false,
      mobileFirst: true,
      draggable: true,
      prevArrow: null,
      nextArrow: null,
      focusOnSelect: true,
      responsive: [
        {
          breakpoint: 767,
          settings: {
            vertical: true
          }
        },
        {
          breakpoint: 319,
          settings: {
            vertical: false
          }
        }
      ]
    };

    angular.element(document).ready(function() {
      $interval(function() {
        angular.element("#closedTypeDiv").css({ left: "24px", bottom: "20px" });
        angular
          .element(".snippet-main-content")
          .css({ bottom: "70px", left: "24px" });
      }, 100);
    });

    $scope.openCTCSnippet = function() {
      // if (JSON.parse(currentUrl)[1] === 'click-to-call') {
      //     angular.element('html, body').animate({
      //         scrollTop: angular.element("#callburn-snippet").offset().top - 120
      //     }, 650);
      // } else {
      angular.element("#closedTypeDiv").trigger("click");
      // }
    };
    $scope.openCTCOpened = function() {
      angular.element("#closedTypeDiv").trigger("click");
    };

    window.addEventListener("scroll", function() {
      if (window.pageYOffset > 600) {
        $scope.showTop = true;
      } else {
        $scope.showTop = false;
      }
    });
    $rootScope.scrollToTop = function() {
      angular.element("html, body").animate(
        {
          scrollTop: angular.element("body").offset().top
        },
        650
      );
    };

    $rootScope.scrollToDiv = function(id) {
      angular.element("html, body").animate(
        {
          scrollTop: angular.element("#" + id).offset().top
        },
        650
      );
    };

    // slick youtube Videos Config
    $scope.youtubeVideosConfig = {
      asNavFor: ".slider-nav",
      dots: false,
      draggable: false,
      mobileFirst: false,
      arrows: false,
      prevArrow: null,
      nextArrow: null,
      draggable: false
    };
    $scope.youtubeVideosNavConfig = {
      asNavFor: ".slider-for",
      slidesToShow: 2,
      slidesToScroll: 1,
      dots: false,
      infinite: false,
      mobileFirst: true,
      draggable: true,
      prevArrow: null,
      nextArrow: null,
      focusOnSelect: true,
      responsive: [
        {
          breakpoint: 767,
          settings: {
            vertical: true
          }
        },
        {
          breakpoint: 319,
          settings: {
            vertical: false
          }
        }
      ]
    };
    $scope.youtubeVideosConfigCTC = {
      asNavFor: ".slider-navCTC",
      dots: false,
      draggable: false,
      mobileFirst: false,
      arrows: false,
      prevArrow: null,
      nextArrow: null,
      draggable: false
    };
    $scope.youtubeVideosNavConfigCTC = {
      asNavFor: ".slider-forCTC",
      slidesToShow: 2,
      slidesToScroll: 1,
      dots: false,
      infinite: false,
      mobileFirst: true,
      draggable: true,
      prevArrow: null,
      nextArrow: null,
      focusOnSelect: true,
      responsive: [
        {
          breakpoint: 767,
          settings: {
            vertical: true
          }
        },
        {
          breakpoint: 319,
          settings: {
            vertical: false
          }
        }
      ]
    };
    $scope.servicesSettings = {
      slidesToShow: 1,
      dots: true
    };

    $scope.isOpened = false;
    $document.on("click", function(e) {
      if (e.target.id === "notificationsDropdownButton") {
        $scope.isOpened = !$scope.isOpened;
      } else {
        $scope.isOpened = false;
      }
    });

    var label = angular.element(".price_label")[0];
    var nav = angular.element(".navbar-index");
    if (label) {
      setInterval(function() {
        var height = nav.outerHeight();
        label.style.top = height + "px";
      }, 100);
    }

    $scope.crispCountrySend = function(country) {
      var txt =
        "custom quotation for country " +
        "+" +
        country.phonenumber_prefix +
        " - " +
        country.name;
      $crisp.push(["do", "message:send", ["text", txt]]);
    };

    $scope.youtubeUrl = "";
    if (window.location.pathname == "/en") {
      $scope.youtubeUrl = $scope.trustURL("https://www.youtube.com/embed/vgNml-W57zg");
    } else if (window.location.pathname == "/es") {
      $scope.youtubeUrl = $scope.trustURL("https://www.youtube.com/embed/XyiLJyFvks0");
    } else if (window.location.pathname == "/it") {
      $scope.youtubeUrl = $scope.trustURL("https://www.youtube.com/embed/1UPs2j1mXsA");
    }
  }
]);
