var Reader = function() {

    var instance = this;

    this.container = $('#reader');
    this.path = this.container.data('path');
    this.files = this.container.data('files');
    this.pageImage = $('#reader-page img', this.container);

    this.loadIndex = function(index) {
        if(index < 0 || index >= this.files.length) {
            console.error('Invalid index: ' + index);
            return;
        }

        var url = this.imageUrl(this.files[index]);
        this.pageImage.prop('src', url);
    };

    this.imageUrl = function(filePath) {
        return '/reader/image/?' + $.param({ path: this.path, file: filePath });
    };

};

$(document).ready(function() {
    var reader = new Reader();
    reader.loadIndex(0);
});