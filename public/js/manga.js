$(document).ready(function() {
    $('#delete-manga').click(function() {
        return confirm('Are you sure?');
    });

    // "Watch series" button on
    $('#watch-series').click(function() {
        var trigger = $(this);
        var seriesId = trigger.data('series');

        $.post('/user/notifications/watch', { series: seriesId }, function(data) {
            if(!data.result) {
                alert('Error ' + data.message);
            }
            else {
                trigger.animate({ opacity: 0 }, 100, function() {
                    if(data.watching) {
                        trigger.addClass('active').text('Watching');
                    }
                    else {
                        trigger.removeClass('active').text('Watch series');
                    }

                    trigger.animate({ opacity: 1 }, 100);
                });
            }
        });

        return false;
    });

    $('.series-paths-trigger').click(function() {
        var trigger = $(this);
        var row = trigger.parents('tr');
        var expandRow = row.next('tr.series-paths-expand');
        var expand = $('.paths', expandRow);

        if(expand.hasClass('active')) {
            expand.height(0);
        }
        else {
            var targetHeight = expand.height('auto').height();
            expand.height(0).height(targetHeight);
        }

        expand.toggleClass('active');

        return false;
    });

    $('#search-input').autocomplete({
        source: '/search/suggest',
        minLength: 2
    });

    // Dismiss all notifications confirm
    $('#dismiss-notify-all').click(function() {
        return confirm('Are you sure?');
    });

    // report button
    $('.report-link').click(function() {
        var trigger = $(this);
        var row = trigger.parents('tr');

        if(!trigger.data('open')) {
            trigger.data('open', true);

            var reportRow = $('.template .report-row').clone();
            var expand = $('.expand', reportRow);

            reportRow.data('trigger', trigger);

            $('textarea, button, input', expand).prop('disabled', false);
            $('input[name="record"]', expand).val(row.data('record'));

            row.after(reportRow);

            var newHeight = expand.height('auto').height();
            expand.height(0).height(newHeight);
        }

        return false;
    });

    $('table').on('click', '.button-report-cancel', function() {
        var trigger = $(this);
        var row = trigger.parents('tr');
        row.data('trigger').data('open', false);
        row.remove();
    });

    $('.report-reason').each(function() {
        var reason = $(this);
        var normalWidth = reason.width();

        reason.addClass('test-width');
        var fullWidth = reason.width();
        reason.removeClass('test-width');

        if(normalWidth !== fullWidth) {
            var view = $('<a href="#" class="report-reason-view">View</a>');
            view.data('text', reason.text());
            reason.after(view);
        }
    });

    $('#reports-table tr').on('click', '.report-reason-view', function() {
        var trigger = $(this);
        alert(trigger.data('text'));
    });

    if(location.hash.length > 1) {
        var filename = location.hash.substr(1);

        $('#index-table tbody tr').each(function() {
            var row = $(this);
            var fileLink = $('td:first-child a:first-child', row);

            if(fileLink.text() === filename) {
                row.addClass('highlight');

                // scroll into view
                var pos = row.offset().top;
                if(pos > $(window).innerHeight()) {
                    $(window).scrollTop(pos - 50);
                }

                return false;
            }
        });
    }
});