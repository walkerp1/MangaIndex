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
    $('.report-button').click(function() {
        var button = $(this);
        var row = button.parents('tr');

        if(!button.data('open')) {
            button.data('open', true);

            var reportRow = $('.template .report-row').clone();
            var expand = $('.expand', reportRow);

            $('textarea, button, input', expand).prop('disabled', false);
            $('input[name="record"]', expand).val(row.data('record'));

            row.after(reportRow);

            var newHeight = expand.height('auto').height();
            expand.height(0).height(newHeight);
        }

        return false;
    });
});