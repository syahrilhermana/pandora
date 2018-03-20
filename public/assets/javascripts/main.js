(function() {
  $(document).ready(function() {
    /*
     * =============================================================================
     *   Navbar scroll animation
     * =============================================================================
     */
    $(".page-header-fixed .navbar.scroll-hide").mouseover(function() {
      $(".page-header-fixed .navbar.scroll-hide").removeClass("closed");
      return setTimeout((function() {
        return $(".page-header-fixed .navbar.scroll-hide").css({
          overflow: "visible"
        });
      }), 150);
    });
    $(function() {
      var delta, lastScrollTop;
      lastScrollTop = 0;
      delta = 50;
      return $(window).scroll(function(event) {
        var st;
        st = $(this).scrollTop();
        if (Math.abs(lastScrollTop - st) <= delta) {
          return;
        }
        if (st > lastScrollTop) {
          $('.page-header-fixed .navbar.scroll-hide').addClass("closed");
        } else {
          $('.page-header-fixed .navbar.scroll-hide').removeClass("closed");
        }
        return lastScrollTop = st;
      });
    });

    /*
     * =============================================================================
     *   Mobile Nav
     * =============================================================================
     */
    $('.navbar-toggle').click(function() {
      return $('body, html').toggleClass("nav-open");
    });

    /*
     * =============================================================================
     *   Style Selector
     * =============================================================================
     */
    $(".style-selector select").each(function() {
      return $(this).find("option:first").attr("selected", "selected");
    });
    $(".style-toggle").bind("click", function() {
      if ($(this).hasClass("open")) {
        $(this).removeClass("open").addClass("closed");
        return $(".style-selector").animate({
          "right": "-240px"
        }, 250);
      } else {
        $(this).removeClass("closed").addClass("open");
        return $(".style-selector").show().animate({
          "right": 0
        }, 250);
      }
    });
    $(".style-selector select[name='layout']").change(function() {
      if ($(".style-selector select[name='layout'] option:selected").val() === "boxed") {
        $("body").addClass("layout-boxed");
        return $(window).resize();
      } else {
        $("body").removeClass("layout-boxed");
        return $(window).resize();
      }
    });
    $(".style-selector select[name='nav']").change(function() {
      if ($(".style-selector select[name='nav'] option:selected").val() === "top") {
        $("body").removeClass("sidebar-nav");
        return $(window).resize();
      } else {
        $("body").addClass("sidebar-nav");
        return $(window).resize();
      }
    });
    $(".color-options a").bind("click", function() {
      $(".color-options a").removeClass("active");
      return $(this).addClass("active");
    });
    $(".pattern-options a").bind("click", function() {
      var classes;
      classes = $("body").attr("class").split(" ").filter(function(item) {
        if (item.indexOf("bg-") === -1) {
          return item;
        } else {
          return "";
        }
      });
      $("body").attr("class", classes.join(" "));
      $(".pattern-options a").removeClass("active");
      $(this).addClass("active");
      return $("body").addClass($(this).attr("id"));
    });

    /*
     * =============================================================================
     *   Bootstrap Tabs
     * =============================================================================
     */
    $("#myTab a:last").tab("show");

    /*
     * =============================================================================
     *   Bootstrap Popover
     * =============================================================================
     */
    $(".popover-trigger").popover();

    /*
     * =============================================================================
     *   Bootstrap Tooltip
     * =============================================================================
     */
    $(".tooltip-trigger").tooltip();

    /*
     * =============================================================================
     *   Popover JS
     * =============================================================================
     */
    $('#popover').popover();

    /*
     * =============================================================================
     *   Fancybox Modal
     * =============================================================================
     */
    $(".fancybox").fancybox({
      maxWidth: 700,
      height: 'auto',
      fitToView: false,
      autoSize: true,
      padding: 15,
      nextEffect: 'fade',
      prevEffect: 'fade',
      helpers: {
        title: {
          type: "outside"
        }
      }
    });


    /*
     * =============================================================================
     *   Skycons
     * =============================================================================
     */
    $('.skycons-element').each(function() {
      var canvasId, skycons, weatherSetting;
      skycons = new Skycons({
        color: "white"
      });
      canvasId = $(this).attr('id');
      weatherSetting = $(this).data('skycons');
      skycons.add(canvasId, Skycons[weatherSetting]);
      return skycons.play();
    });

    /*
     * =============================================================================
     *   Input placeholder fix
     * =============================================================================
     */
    if (!Modernizr.input.placeholder) {
      $("[placeholder]").focus(function() {
        var input;
        input = $(this);
        if (input.val() === input.attr("placeholder")) {
          input.val("");
          return input.removeClass("placeholder");
        }
      }).blur(function() {
        var input;
        input = $(this);
        if (input.val() === "" || input.val() === input.attr("placeholder")) {
          input.addClass("placeholder");
          return input.val(input.attr("placeholder"));
        }
      }).blur();
      $("[placeholder]").parents("form").submit(function() {
        return $(this).find("[placeholder]").each(function() {
          var input;
          input = $(this);
          if (input.val() === input.attr("placeholder")) {
            return input.val("");
          }
        });
      });
    }

    /*
     * =============================================================================
     *   Ladda loading buttons
     * =============================================================================
     */
    Ladda.bind(".ladda-button:not(.progress-demo)", {
      timeout: 2000
    });
    Ladda.bind(".ladda-button.progress-demo", {
      callback: function(instance) {
        var interval, progress;
        progress = 0;
        return interval = setInterval(function() {
          progress = Math.min(progress + Math.random() * 0.1, 1);
          instance.setProgress(progress);
          if (progress === 1) {
            instance.stop();
            return clearInterval(interval);
          }
        }, 200);
      }
    });

    /*
     * =============================================================================
     *   Desktop Notifications Permission
     * =============================================================================
     */
    if(Notification.permission !== "granted") {
      Notification.requestPermission();
    }
  });

}).call(this);

function drawTable(url, target) {
    var table = (typeof target !== 'undefined') ? target : 'datatables';

    $('#'+table).DataTable({
        "pagingType": "full_numbers",
        "lengthMenu": [
            [10, 50, 100, 500, -1],
            [10, 50, 100, 500, "All"]
        ],
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": url+uriArgs(),
            "type": "GET"
        }
    });
}

function redrawTable(target) {
    var object = (typeof target !== 'undefined') ? target : 'datatables';
    var table = $('#'+object).DataTable();
    table.draw(false);
}

function uriArgs() {
    var uri = window.location.href;
    var args= uri.split("?");

    if(args.length > 1)
        return "?"+args[1];
    else
        return "";
}

function removeItem(uri, token, callback, redraw) {
    var result = confirm("Anda yakin ingin menghapus data ini?");
    var action = (typeof redraw !== 'undefined') ? true : false;
    if(result) {
        var jqXHR = $.post(uri, {token: token}, function (response) {
            alert(response.message);
        });

        jqXHR.always(function () {
            if(action) {
                redrawTable(callback);
            } else {
                $("#result").load(callback);
            }
        });
    }
}

function ajaxLoad(uri, title) {
    $("#default-title").text(title);
    $("#default").modal('show').find('.modal-body').html("").load(uri);
}

function ajaxSelect(code, uri, target, string, callback) {
    if(code.value != '') {
        $.ajax({
            url: uri,
            method: "GET",
            data: {
                token: code.value
            },
            success: function (response) {
                $('#'+target).empty();
                $('#'+target).append("<option value=''>"+ string +"</option>");
                
                $.each(response, function (i, response) {
                    $('#'+target).append("<option id='"+ target + '-' + response.target +"' value='"+ response.id +"'>"+ response.name +"</option>");
                });

                // change
                console.log(callback);
                console.log(target);
                console.log('-------------');
                if(callback != '') {
                    document.getElementById(target + "-" + callback).selected=true;
                    $('#' + target).trigger("change");
                }
            }
        });
    } else {
        $('#'+target).empty();
        $('#'+target).append("<option value=''>"+ string +"</option>");
    }
}

function ajaxSubmit(target, uri) {
    var form = document.getElementById(target);
    var formData = new FormData(form);

    $.ajax({
        url: uri,
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $("#default").modal('hide');
        },
        error: function(response) {
            alert(response.message);
            $("#default").modal('hide');
        }
    });
}
