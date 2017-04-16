/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var codeMirror = function() {
     // codemirror instance
        var priv = {};
        priv.theme = "midnight";
        this.themeList = [
            "midnight",
            "monokai",
            "blackboard",
            "cobalt",
            "eclipse",
            "mbo",
            "neat",
            "elegant",
            "neo"
        ];
        priv.editor = [];
        this.init = function()
        {
            priv.addByClass('codeMirror');
        };

        this.updateTheme = function(t)
        {
            this.clear();

            for (var i = 0; i < this.themeList.length; i++) {
                if(this.themeList[i] == t) {
                    if(document.getElementById('mirror'+t) == null) {
                        var head  = document.getElementsByTagName('head')[0];
                        var link  = document.createElement('link');
                        link.rel  = 'stylesheet';
                        link.type = 'text/css';
                        link.href = '/vendor/codemirror/CodeMirror/theme/'+t+'.css';
                        link.id   = 'mirror'+t;
                        head.appendChild(link);
                    }
                    priv.theme = t;
                    priv.addByClass('codeMirror');
                }
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
