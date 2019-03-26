jQuery(document).on('click', '#test_drive_welcome_notice .notice-dismiss', function () {

  var data = {
    action: 'sandbox_notice_dismiss',
  };
  jQuery.ajax({
    type: 'POST',
    url: ajaxurl,
    data: data,
    success: function (response) {
    },
    dataType: "json"
  });

});

jQuery(function ($) {

  $(document).ready(function () {

    var $isChrome = /chrom(e|ium)/.test(navigator.userAgent.toLowerCase());

    // Change this id with the one you have in Chrome.
    var chromeExtensionId = "nhjkfekhgaccgjpoaebhgjhhbcbjhnco";

    affiliate_ad_sliding = function(){
      var $addAreaWrapper = $('#wpcontent').length ? $('#wpcontent') : $('body');
      // pick random Ad
      var $adIndex = Math.floor(Math.random()*$(".poopy_affiliate").length);
      var $affiliateAd = $(".poopy_affiliate").eq($adIndex).clone();
      // get Ad area width
      var $affiliateAreaWidth = $addAreaWrapper.width();
      // get Ad area height
      var $affiliateAreaHeight = window.innerHeight
        || document.documentElement.clientHeight
        || document.body.clientHeight;

      var $doc = document.documentElement;
      var $windowTop = (window.pageYOffset || $doc.scrollTop) - ($doc.clientTop || 0);

      $addAreaWrapper.append($affiliateAd);

      // set default add position
      var $adTop = Math.floor(Math.random() * $affiliateAreaHeight);
      if ($affiliateAreaHeight - $adTop < $affiliateAd.height()){
        $adTop = $affiliateAreaHeight - $affiliateAd.height() - 10;
      }
      $affiliateAd.css({'top': $adTop > 0 ? $adTop + $windowTop : 0, 'right': 0});
      // animate Ad
      $affiliateAd.animate({'right':$affiliateAreaWidth - $affiliateAd.width()}, 20000, function(){
        $(this).fadeOut('slow', function(){
          $(this).remove();
        });
      });
    };

    if ($isChrome) {
      if (typeof chrome.runtime === 'undefined') {
        // Show install button if extension not installed.
        $("#install-chrome").show();
        $('.install-chrome-extension').css("cssText", "display: inline-block !important;");
        if (is_poopy && $(window).width() > 782) {
          // Show Extension Ad every 5 minutes.
          setInterval(affiliate_ad_sliding, 300 * 1000);
        }
      }
      else {
        // Check is extension installed.
        chrome.runtime.sendMessage(chromeExtensionId, {msg: "isInstalled"},
          function (response) {
            if (typeof response === 'undefined' || !response) {
              // Show install button if extension not installed.
              $("#install-chrome").show();
              $('.install-chrome-extension').css("cssText", "display: inline-block !important;");
              if ($(window).width() > 782) {
                // Show Extension Ad every 5 minutes.
                setInterval(affiliate_ad_sliding, 300 * 1000);
              }
            }
            else {
              // Show 'Add Template to Extension' if extension installed.
              $('.add-template-to-extension').css("cssText", "display: block !important;");
            }
          });
      }

      if (!is_poopy) {
        // Add Template to Extension works only for licensed installs.
        $('.add-template-to-extension').click(function (e) {
          // Check weather the template is already installed or not.
          chrome.runtime.sendMessage(chromeExtensionId, {msg: "getInstalledTemplates"},
            function (response) {
              if (response.response.success) {
                var isAlreadyAdded = false;
                var templateKeys = Object.keys(response.response.data);
                var templateUrl = $('#template_url').attr('href');
                var templateTitle = $('.add-template-to-extension').attr('rel');
                for (var i = 0; i < templateKeys.length; i++) {
                  if (response.response.data[templateKeys[i]].url == templateUrl) {
                    isAlreadyAdded = true;
                    break;
                  }
                }
                if (isAlreadyAdded) {
                  alert('This template has already been added to the browser extension.');
                }
                else if (templateKeys.length > 3) {
                  alert('Only 4 templates may be stored in the browser extension. Remove one to add another.');
                }
                else {
                  // Add Template to Browser Extension
                  chrome.runtime.sendMessage(chromeExtensionId, {
                      msg: "saveNewTemplate",
                      title: templateTitle,
                      url: templateUrl
                    },
                    function (response) {
                      if (response.response.success) {
                        alert(response.response.message);
                      }
                    });
                }
              }
            });
        });
      }
    }

    update_sandbox_timer = function () {
      var data = {
        action: 'sandbox_expiration_time'
      };
      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: data,
        success: function (response) {
          $('.sandbox_remaining').find('.ab-item').find('span:first').html(response.timer);
          $('.sandbox-time-to-expire').find('strong').html(response.timer.replace('Expires in ', ''));
        },
        dataType: "json"
      });
    };
    if (timer_interval > 0) {
      update_sandbox_timer();
      setInterval(update_sandbox_timer, timer_interval);
    }

    $("#sandbox-datepicker").datepicker({
      beforeShow: function () {
        if (!$('.sandbox_datepicker_wrapper').length) {
          $('#ui-datepicker-div').wrap('<span class="sandbox_datepicker_wrapper"></span>');
        }
      }
    });

    // swither show/hide logic
    $('input.switcher').live('change', function (e) {

      if ($(this).is(':radio:checked')) {
        $(this).parents('form').find('input.switcher:radio[name="' + $(this).attr('name') + '"]').not(this).change();
      }
      var $switcherID = $(this).attr('id');

      var $targets = $('.switcher-target-' + $switcherID);

      var is_show = $(this).is(':checked');
      if ($(this).is('.switcher-reversed')) is_show = !is_show;
      if (is_show) {
        $targets.fadeIn('fast');
      } else {
        $targets.hide().find('.clear-on-switch').add($targets.filter('.clear-on-switch')).val('');
      }
    }).change();

    // actions for poopy sandbox
    if (is_poopy) {
      $("#toolbar-header").prependTo("#wpadminbar").show();
      $('html').css("cssText", "padding-top:0px; margin-top:0 !important;");
      $('body').css('padding-top', '135px');
      $('#wpadminbar').show();

      // saving sandbox expiration settings
      $('form.sandbox-settings-form').find('input.save-expiration-settings').click(function (e) {
        e.preventDefault();
        //$( this ).effect( "shake" );
        paidFeature();
        $('input[name=sandbox_expiration_date]').val('');
        $('.sandbox-time-to-expire').css({'color': 'red'});
        setTimeout(function () {
          $('.sandbox-time-to-expire').animate({
            color: 'black'
          }, 1000);
        }, 700);
      });
      // saving sandbox template settings
      $('form.sandbox-settings-form').find('input.save-template-settings').click(function (e) {
        e.preventDefault();
        paidFeature();
      });
      // saving sandbox template settings
      $('form.sandbox-settings-form').find('input.save-template-permissions').click(function (e) {
        e.preventDefault();
        paidFeature();
      });
      // saving sandbox welcome notice settings
      $('form.sandbox-settings-form').find('input.save-template-welcome-notice').click(function (e) {
        e.preventDefault();
        paidFeature();
      });
      // saving sandbox advanced settings
      $('form.sandbox-settings-form').find('input.save-advanced-settings').click(function (e) {
        e.preventDefault();
        paidFeature();
        $('select[name=php_version]').prop('selectedIndex', 0);
        $('.sandbox-php-version').css({'color': 'red'});
        setTimeout(function () {
          $('.sandbox-php-version').animate({
            color: 'black'
          }, 1000);
        }, 700);
      });
      $('.poopy_menu').hover(function () {
        $('.poopy_menu_content').show();
      }, function () {
        $('.poopy_menu_content').hide();
      });
      // Show 'Add Template to Extension' if extension installed.
      $('.add-template-to-extension').css("cssText", "display: block !important;");
      $('.add-template-to-extension').click(function(){
        paidFeature();
      });
    }
    else {
      // Upgrade notice for Child Perms and Welcome Notices.
      if ($('.sandbox-permissions-permission').length) {
        // saving sandbox template settings
        $('form.sandbox-settings-form').find('input.save-template-permissions').click(function (e) {
          e.preventDefault();
          paidFeature();
        });
        // saving sandbox welcome notice settings
        $('form.sandbox-settings-form').find('input.save-template-welcome-notice').click(function (e) {
          e.preventDefault();
          paidFeature();
        });
      }
      // saving sandbox advanced settings
      $('form.sandbox-settings-form').find('input.save-advanced-settings').click(function (e) {
        e.preventDefault();
        $(this).attr('disabled', 'disabled');
        var $php = $('select[name=php_version]').val();
        var data = {
          action: 'sandbox_change_php_version',
          version: $php
        };
        jQuery.ajax({
          type: 'POST',
          url: ajaxurl,
          data: data,
          success: function (response) {
            window.location.reload(true);
          },
          error: function (response) {
            window.location.reload(true);
          },
          dataType: "json"
        });
      });
    }

    // paid feature effect
    paidFeature = function () {
      $('html, body').animate({
        scrollTop: $(".try_now:first").offset().top - (is_poopy ? 200 : 0)
      }, 200, function () {
        $(".sandbox-ad").effect("shake", {
          direction: "left",
          times: 3,
          distance: 5
        });
      });
    };

    $('.additional-notice-settings-section').find('h3:first').click(function(){
      $(this).prev('.sandbox-circle-plus').toggleClass('sandbox-opened');
      $(this).next('.additional-notice-settings-content').slideToggle();
    });

    $('.sandbox-circle-plus').on('click', function(){
      $(this).toggleClass('sandbox-opened');
      $(this).parent().find('.additional-notice-settings-content').slideToggle();
    });

    $('<span id="tdr_drive_confirmation_template" style="display:none;"></span>').insertAfter('#test_drive_confirmation_notice');
    $('#tdr_drive_confirmation_template').html($('#test_drive_confirmation_notice').html());

    $('#tdr_email_submit').click(function () {

      $('#test_drive_confirmation_notice').hide();
      var email = $('#tdr_email_input');
      var subscribe = $('#tdr_subscribe').is(':checked');

      $('#test_drive_confirmation_notice').html($('#tdr_drive_confirmation_template').html().replace('[tdr_submitted_email]', email.val()));

      var data = {
        action: 'sandbox_send_confirmation_email',
        email: email.val(),
        subscribe: subscribe
      };
      $('#tdr_email_input').attr('disabled', 'disabled');
      $('#tdr_email_submit').attr('disabled', 'disabled');
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: data,
        success: function (response) {

          if (response.result) {
            // $('#sandbox_welcome_notice').hide();
          }
          else {
            console.log(response.msg);
          }
          $('#test_drive_confirmation_notice').show();
          $('#tdr_email_input').removeAttr('disabled');
          $('#tdr_email_submit').removeAttr('disabled');
        },
        dataType: "json"
      });

    });
    $('.test_drive_notice').find('.notice-dismiss').live('click', function () {
      $(this).parents('.test_drive_notice:first').slideUp();
    });
    $('textarea[name=sandbox_redirect_url]').keyup(function () {
      var $url = $('#template_url').data('url') + '&url=' + $(this).val();
      if (!is_poopy) {
        $('#template_url').attr('href', $url);
      }
      $('#template_url').html($url);
    });
  });

  function is_valid_email(email, strict) {
    if (!strict)
      email = email.replace(/^\s+|\s+$/g, '');
    return (/^([a-z0-9_\-]+\.)*[a-z0-9_\-]+@([a-z0-9][a-z0-9\-]*[a-z0-9]\.)+[a-z]{2,4}$/i).test(email);
  }

  // help icons
  $('.sandbox-help').toolbartipsy({
    gravity: function () {
      var ver = 'n';
      if ($(document).scrollTop() < $(this).offset().top - $('.tipsy').height() - 2) {
        ver = 's';
      }
      var hor = '';
      if ($(this).offset().left + $('.tipsy').width() < $(window).width() + $(document).scrollLeft()) {
        hor = 'w';
      } else if ($(this).offset().left - $('.tipsy').width() > $(document).scrollLeft()) {
        hor = 'e';
      }
      return ver + hor;
    },
    live: true,
    html: true,
    opacity: 1
  }).live('click', function () {
    return false;
  }).each(function () { // fix tipsy title for IE
    $(this).attr('original-title', $(this).attr('title'));
    $(this).removeAttr('title');
  });

  $('.sandbox-toolbox-help').toolbartipsy({
    gravity: function () {
      var ver = 'n';
      if ($(document).scrollTop() < $(this).offset().top - $('.tipsy').height() - 2) {
        ver = 'n';
      }
      var hor = '';
      if ($(this).offset().left + $('.tipsy').width() < $(window).width() + $(document).scrollLeft()) {
        hor = 'w';
      } else if ($(this).offset().left - $('.tipsy').width() > $(document).scrollLeft()) {
        hor = 'e';
      }
      return ver + hor;
    },
    live: true,
    html: true,
    opacity: 1,
    class: 'toolbar-tipsy'
  }).live('click', function () {
    return false;
  }).each(function () { // fix tipsy title for IE
    $(this).attr('original-title', $(this).attr('title'));
    $(this).removeAttr('title');
  });

});
