var Reader = function() {

    var instance = this;

    this.container = $('#reader');
    this.path = this.container.data('path');
    this.files = this.container.data('files');
    this.pageImage = $('#reader-page img', this.container);
    this.currentIndex = 0;

    this.setIndex = function(newIndex) {
        if(newIndex >= 0 && newIndex < this.files.length) {
            this.currentIndex = newIndex;
        }
    };

    this.loadImage = function(setHistory) {
        var url = this.imageUrl(this.files[this.currentIndex]);
        this.pageImage.prop('src', url);

        if(setHistory) {
            this.setHistory(false);
        }
    };

    this.imageUrl = function(filePath) {
        return '/reader/image?' + $.param({ path: this.path, file: filePath });
    };

    this.setHistory = function(replace) {
        var state = {
            index: this.currentIndex
        };

        var url = this.pageUrl(this.currentIndex);

        if(replace) {
            history.replaceState(state, document.title, url);
        }
        else {
            history.pushState(state, document.title, url);
        }
    };

    $(window).on('popstate', function(e) {
        if(e.originalEvent.state) {
            var state = e.originalEvent.state;
            if(state.index !== undefined) {
                instance.setIndex(state.index);
                instance.loadImage(false);
            }
        }
    });

    this.pageUrl = function(index) {
        return window.location.protocol + '//' + window.location.host + window.location.pathname + '?' + $.param({ index: index });
    };

    this.next = function() {
        if((this.currentIndex + 1) < this.files.length) {
            this.currentIndex++;
            this.loadImage(true);
        }

        this.scrollTop();
    };

    this.pageImage.click(function() { instance.next(); });

    this.scrollTop = function() {
        var currentScroll = $(document.body).scrollTop();
        $(document.body).animate({ scrollTop: 0 }, currentScroll);
    };

    this.init = function() {
        var index = this.container.data('index');
        this.setIndex(index);
        this.loadImage(false);
    };
};

$(document).ready(function() {
    var reader = new Reader();
    reader.init();
    reader.setHistory(true);
});