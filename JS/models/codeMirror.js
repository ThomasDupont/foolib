/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var codeMirror = function() {
     // codemirror instance
        var priv = {};
        priv.theme = "midnight";
        priv.themeList = [
            "midnight",
            "minokai"
        ];
        priv.editor = [];
        this.init = function()
        {
            priv.addByClass('codeMirror');
        };

        this.updateTheme = function(t)
        {
            if(t.indexOf(priv.themeList) < -1) {
                priv.theme = t;
                priv.addByClass('codeMirror');
            } else {
                alert('thème non reconnu');
            }
        };

        priv.addByClass = function(_class)
        {
            var ta = document.getElementsByClassName(_class);
            for (var i = 0; i < ta.length; i++) {
                priv.refresh(ta[i]);
            }
        };
        priv.refresh = function(el)
        {
            var editor = CodeMirror.fromTextArea(el, {
                lineNumbers: true,
                lineWrapping: true,
                theme: priv.theme,
                readOnly: false // false, true, nocursor (no selection)
            });
            editor.refresh();
            priv.editor.push(editor);

        };
        this.refresh = function(el)
        {
            priv.refresh(el);
        };
        this.save = function ()
        {
            for (var i = 0; i < priv.editor.length; i++) {
                priv.editor[i].save();
            }
        };
        this.clear = function()
        {
            $(".CodeMirror-wrap").remove();
        }
}
