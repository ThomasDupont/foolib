angular.module('foolib').directive("code", function(){
    return {
        restrict: 'E',
        scope: {
            controller: '=controller',
            iteration: '=iteration'
        },
        createScope : false,
        templateUrl: 'views/template/code.html',
        link: function(scope){
            scope.controller.codeLangage[scope.iteration] = "php";
        }
    };
}).directive("listcode", function(){
    return {
        restrict: 'E',
        scope: {
          controller: '=controller',
          codes: '=codes',
          codeid: '=codeid',
          codename: '=codename'
        },
        createScope : false,
        templateUrl: 'views/template/listCode.html',
        link: function(scope) {
            scope.wantView = scope.updateCodeVar = false;
            scope.view = function() {
                scope.wantView = !scope.wantView;
            };
            scope.updateCode = function () {
                scope.updateCodeVar = !scope.updateCodeVar;
            };
            scope.updateCodeValidation = function(codes, id, codename) {
                scope.controller.code.updateCode(codes, id, codename);
                scope.updateCode();
            }
        }
    }
}).directive("viewcode", function(){
    return {
        restrict: 'E',
        scope: {
          c: '=controller'
        },
        createScope : false,
        templateUrl: 'views/template/viewcode.html',
        link: function(scope) {
            /**
            * @param {object} el, list of code for the current snippet
            * @param {string} id, id of the current snippet
            * @param {string} name, name of the current snippet
            */
            scope.updateCodeValidation = function(el, id, codename) {
                scope.c.mirror.save();
                for (var i = 0; i < el.codes.length; i++) {
                    el.codes[i].content = $('#updateCodeMirror'+i).val();
                }
                scope.current = el;
                scope.c.code.updateCode(el.codes, id, codename, scope.majFiles);
            };
            scope.majFiles = function (mongoId) {
                var files = document.getElementsByClassName("updateFile");
                for(var i=0;i<files.length; i++) {
                    if(typeof(files[i].files[0]) != 'undefined') {
                        scope.c.code.updateScreen(
                            files[i].files[0],
                            {position:i, mongoId:mongoId, type: 'update'},
                            scope.current.file[i]
                        );
                    }
                }
            };
            scope.supprScreen = function (files, index, id) {
                var tmp = [];
                var oldNodeId = null;
                for (var i = 0; i < files.length; i++) {
                    if(i != index) {
                        tmp.push(files[i]);
                    } else {
                        oldNodeId = files[i].id;
                    }

                }
                scope.c.code.supprScreen(tmp, id, oldNodeId);
            };
        }
    }
}).directive("screenshot", function(){
    return {
        restrict: 'E',
        scope: {
          c: '=controller'
        },
        createScope : false,
        templateUrl: 'views/template/screenshot.html',
        link: function(scope, element, attrs) {
            if(window.drop === undefined) {
                window.drop = new dropFile({
                    dropArea: 'dropArea',
                    fileInput: 'file',
                    supr: 'supr'
                }, 3);
                drop.init();
            }
        }
    }
});
