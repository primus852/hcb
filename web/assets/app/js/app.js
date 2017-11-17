String.prototype.escapeHTML = function () {
    return (
        this.replace(/>/g, '&gt;').replace(/</g, '&lt;').replace(/"/g, '&quot;')
    );
};

/* Global Vars */
var cols = {},
    messageIsOpen = false;

$(function () {

    startTime(1);
    $('input').attr('autocomplete', 'off');

    /* ------- Init Perfect Scrollbar ------- */
    initPerfectScrollbar('#perfectScroll');

    /* ------- Init Modal Dialogs ------- */
    initModal();

    /* ------- Init tooltips ------- */
    initTooltips('.tt');

    // Trigger the event (useful on page load).
    $(window).hashchange();

    /* ------- Add active menu ------- */
    initMenu('#'+GetNav);

    /* ------- Add slider overlays ------- */
    initOverlays();

    /* ------- Init Hashwatcher ------- */
    detectHash();


});

function initMenu(selector){
    var $curMenu = $(selector);
    if (!$curMenu.is(':visible')) {
        $curMenu.parent().prev('a').trigger('click');
    }
    $curMenu.addClass('active');
}

function initOverlays() {
    cols.showOverlay = function () {
        $('body').addClass('show-main-overlay');
    };
    cols.hideOverlay = function () {
        $('body').removeClass('show-main-overlay');
    };


    cols.showMessage = function () {
        $('body').addClass('show-message');
        messageIsOpen = true;
    };
    cols.hideMessage = function () {
        $('body').removeClass('show-message');
        messageIsOpen = false;
    };


    cols.showSidebar = function () {
        $('body').addClass('show-sidebar');
    };
    cols.hideSidebar = function () {
        $('body').removeClass('show-sidebar');
    };


    // Show sidebar when trigger is clicked

    $('.trigger-toggle-sidebar').on('click', function () {
        cols.showSidebar();
        cols.showOverlay();
    });


    $('.trigger-message-close').on('click', function () {

        var $mb = $(this).closest('.messageFly');

        if ($('.messageFly').length <= 1) {
            cols.hideMessage();
            $mb.remove();
            history.pushState("", document.title, window.location.pathname);
        } else {
            $mb.animate({left: '9999px'}, function () {
                $mb.remove();
            });
        }

    });

    // When you click the overlay, close everything
    $('#main > .overlay').on('click', function () {
        cols.hideOverlay();
        cols.hideMessage();
        cols.hideSidebar();
    });
}

function detectHash() {

    var hash = window.location.hash;
    if (!hash.length) {
        return false;
    }

    $("[data-hash=" + hash.replace('#', '') + "]").trigger('click');

}

/* Toggle Treview */
$(document).on('click', '.toggleTree', function (e) {
    e.preventDefault();

    var $items = $(this).next('.treeMenu');
    var $icon = $(this).children('i');
    if ($items.is(':visible')) {
        $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
    $items.toggle(200);

});

/* Click on any Listitem */
$(document).on('click', '.clickable', function (e) {

    e.preventDefault();
    window.location.hash = $(this).attr('data-hash');


    var $uId = Math.floor((Math.random() * 10000) + 1);

    $('body').append('<div class="messageFly" id="' + $uId + '"></div>');

    var $message = $('#' + $uId);
    $message.show();

    loadDetails($(this), $uId);

    if (messageIsOpen) {
        if (!$(this).hasClass('innerMessage')) {
            cols.hideMessage();
        }
        setTimeout(function () {
            cols.showMessage();
        }, 300);
    } else {
        cols.showMessage();
    }
    cols.showOverlay();


});


/* Click on any Listitem */
$(document).on('click', '.initUploader', function (e) {
    e.preventDefault();

    initUploader($(this).attr('data-uploader'));
    $(this).remove();

});

function loadDetails(trigger, id) {

    /* DIV */
    var $message = $('#' + id);
    $message.html('<i class="fa fa-spin fa-spinner fa-4x"></i>');

    /* Ajax Call */
    $.get(trigger.attr('data-url')).done(function (data) {
        $message.html(data);
        var sHash = window.location.hash.split('load-');
        if (sHash.length) {
            $('[data-device="' + sHash[1] + '"]').trigger('click');
        }

        $('#menuToggle > input').trigger('click');
        initOverlays();
        initModal();
        initTooltips('.tt');
        initPerfectScrollbar('.subScroll');

        /* If it is a menu Link, init active menu bar */
        if(trigger.hasClass('mLink')){
            initMenu(trigger.parent());
        }

    });
}


/* Close Modal */
$(document).on('click', '.md-close', function (e) {
    e.preventDefault();
    var $modal = $('#modal');
    if ($(this).hasClass('disabled')) {
        return false;
    }
    $modal.removeClass('md-show');
});


$(document).on('click', '.md-trigger', function (e) {

    e.preventDefault();
    var $btn = $(this);
    var $title = $btn.attr('data-title');
    var $html = $btn.attr('data-html');
    var $okClass = $btn.attr('data-ok');
    var $revise = $btn.attr('data-to-revise');
    var $row = $btn.attr('data-row');
    var $id = $btn.attr('data-id');
    var $okUrl = $btn.attr('data-ok-url');
    var $parentHtml = $btn.attr('data-parent-html');

    modalUpdate($title, $html, $okClass, $id, $okUrl, $parentHtml);
    if ($btn.attr('data-pre-url').length) {
        $.get($btn.attr('data-pre-url'))
            .done(function (data) {
                e.preventDefault();
                if (data.result === 'success') {
                    if (data.extra) {
                        $html = data.extra.html;
                        $title = data.extra.title;
                        modalUpdate($title, $html, $okClass, $id, $okUrl, $parentHtml, $revise, $row);
                    } else {
                        openNoty(data.result, data.message);
                    }
                }
            })
            .fail(function () {
                openNoty("error", "Ajax Error");
            })
        ;
    }

});

function modalUpdate(title, html, okClass, id, okUrl, parentHtml, revise, row) {

    var $header = $('#modalTitle');
    var $text = $('#modalText');
    var $okClass = $('#mOkClass');
    var $abortClass = $('#mAbortClass');

    $header.html(title);
    $text.html(html);
    $okClass.removeClass().addClass('btn').addClass('btn-success').addClass('rounded-0').addClass('btn-block').addClass(okClass).attr('data-id', id).attr('data-ok-url', okUrl).attr('data-parent-html', parentHtml).attr('data-to-revise', revise).attr('data-row', row);
    $abortClass.removeClass().addClass('btn').addClass('btn-danger').addClass('md-close').addClass('rounded-0').addClass('btn-block');
}

function initModal() {

    [].slice.call($('.md-trigger')).forEach(function (el, i) {

        var $modal = $('#' + el.getAttribute('data-modal'));
        el.addEventListener('click', function (ev) {
            $modal.addClass('md-show');
        });
    });

}

function initPerfectScrollbar(selector){
    var $ps = $(selector);
    if ($ps.length) {
        new PerfectScrollbar(selector);

        /* Always initiate left menu */
        new PerfectScrollbar('#pScrollerMenu');
    }
}

function initTimePicker(selector) {
    var $selectTimeFollow = $(selector);
    if ($selectTimeFollow.length) {

        $selectTimeFollow.timepicker({
            timeFormat: 'HH:mm',
            interval: 15,
            dynamic: false,
            dropdown: false,
            scrollbar: true
        });
    }
}

function initPickaDay(selector) {
    var $dateStart = $(selector);
    if ($dateStart.length) {
        new Pikaday({
            field: $dateStart[0],
            format: 'DD.MM.YY'
        });
    }
}

/* Noty Function
 * type = {alert, success, error, warning, information}
 */
function openNoty(type, text) {
    new Noty({
        layout: 'topRight',
        text: text,
        type: type,
        timeout: 3000,
        buttons: false
    }).show();
}

function initTooltips(selector) {
    
    var $tt = $(selector);
    $tt.tooltipster({
        theme: 'tooltipster-punk',
        contentAsHTML: true
    });
}

/* Display Clock upon start */
function startTime(autostart) {
    var url = $('#ajax-route-update-times').val();
    var urlApi = $('#ajax-route-nmaster-api').val();
    var today = new Date();
    var h = today.getHours();
    var m = today.getMinutes();
    var s = today.getSeconds();
    var $nmErrors = $('#nm-errors');
    m = checkTime(m);
    s = checkTime(s);
    $('#startClock').html("@" + h + ":" + m + ":" + s + " Uhr");
    var t = setTimeout(startTime, 1000);
}

function checkTime(i) {
    if (i < 10) {
        i = "0" + i
    }
    return i;
}

function closeModal() {
    $('.trigger-message-close').trigger('click');
}

$(window).hashchange(function () {
    var hash = location.hash.replace(/^#/, '');
    if (hash === '') {
        $('.trigger-message-close').trigger('click');
    }
});