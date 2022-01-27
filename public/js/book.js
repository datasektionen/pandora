var day = 0;
var range = [0, 0];
var mouseDown = false;
$(document).ready(function () {
    $('.day .hour').on('mousedown', function () {
        mouseDown = true;
        day = $(this).parent().index();
        range = [$(this).index(), $(this).index()];
        $('.lighten-4').removeClass('lighten-4');
        $(this).addClass('lighten-4');
        $('.day .hour').on('mouseover', function () {
            if (!mouseDown) {
                return;
            }
            if ($(this).parent().index() !== day) {
                $('.lighten-4').removeClass('lighten-4');
                return;
            }
            range[1] = $(this).index();
            paintRange(range, day);
        });
    });

    $('.day .hour').on('mouseup', function () {
        mouseDown = false;
        presentBookBox();
    });

    $(document).keyup(function (e) {
        if (e.keyCode == 27) { // escape key maps to keycode `27`
            dismissDialog();
        }
    });

    $('.book-box-container').click(function (e) {
        dismissDialog();
    });
    $('.book-box').click(function (e) {
        e.stopPropagation();
    });
    $('#closedialog').click(function (e) {
        e.preventDefault();
        dismissDialog();
    });
    $('#show-all').on('click', function (e) {
        e.preventDefault();
        if ($('.hour.hidden').length > 0) {
            $('.hour.hidden').removeClass('hidden');
            $('.event').each(function () {
                $(this).css('top', parseInt($(this).css('top')) + 8 * 35);
            });
            $(this).html("Visa mindre &uarr;");
        } else {
            $('.day, .legend').each(function () {
                $(this).find('.hour:not(.placeholder)').slice(0, 8).addClass('hidden');
            });
            $('.event').each(function () {
                $(this).css('top', parseInt($(this).css('top')) - 8 * 35);
            });
            $(this).html("Visa mer &darr;");
        }
    });

    $('#copy').focus(function () {
        $(this).select();
    });
});

function dismissDialog() {
    $('.book-box-container').removeClass('visible');
    $('.hour-row .lighten-4').removeClass('lighten-4');
}

Date.prototype.toInputFormat = function () {
    var yyyy = this.getFullYear().toString();
    var mm = (this.getMonth() + 1).toString();
    var dd = this.getDate().toString();
    return yyyy + "-" + (mm[1] ? mm : "0" + mm[0]) + "-" + (dd[1] ? dd : "0" + dd[0]);
};

Date.prototype.addDays = function (days) {
    var dat = new Date(this.valueOf());
    dat.setDate(dat.getDate() + days);
    return dat;
}

function presentBookBox() {
    let loggedIn = $('.book-box-container').length > 0;
    $('.lighten-4').removeClass('lighten-4');
    if (!loggedIn) {
        $('.bottom-alert').removeClass('hidden-b');
        setTimeout(function () {
            $('.bottom-alert').addClass('hidden-b');
        }, 4000);
    } else {
        let min = (range[0] < range[1] ? range[0] : range[1]) - 1;
        let max = (range[1] > range[0] ? range[1] : range[0]);
        $('.book-box-container').addClass('visible');
        var dayObj = $('.day:nth-child(' + (day + 1) + ')');

        $('.book-box input[name="startdate"]').val(dayObj.find('.date-val').val());
        $('.book-box input[name="starttime"]').val((min < 10 ? '0' : '') + min + ":00");

        if (max == 24) {
            $('.book-box input[name="endtime"]').val("00:00");
            $('.book-box input[name="enddate"]').val((new Date(dayObj.find('.date-val').val())).addDays(1).toInputFormat());
            return;
        }

        $('.book-box input[name="endtime"]').val((max < 10 ? '0' : '') + max + ":00");
        $('.book-box input[name="enddate"]').val(dayObj.find('.date-val').val());
    }
}

function paintRange(range, day) {
    $('.lighten-4').removeClass('lighten-4');
    let min = 1 + (range[0] < range[1] ? range[0] : range[1]);
    let max = 1 + (range[1] > range[0] ? range[1] : range[0]);

    //$('.hour.lighten-4').removeClass('lighten-4');
    var dayObj = $('.day:nth-child(' + (day + 1) + ')');
    for (var i = min; i <= max; i++) {
        dayObj.find('.hour:nth-child(' + i + ')').addClass('lighten-4');
    }
}
