
var Toast = function () {
    /**
     * prompt a toast
     * @param  {string} id      id of the toast begin by toast
     * @param  {string} message the message to prompt
     * @param  {int}    delay   the delay for the time out
     */
    this.prompt = function (id, message, delay) {
        var d = delay || 1000;
        var el = $('#toast'+id+'');
        el.text(message);
        el.fadeIn();
        setTimeout(function() {
            el.fadeOut();
        }, d);
    }
};
window.toast = new Toast();
