angular.module('routeApp')
.directive("code", function(){
    return {
        restrict: 'E',
        scope: {
            controller: '=controller',
            iteration: '=iteration'
        },
        createScope : false,
        templateUrl: 'views/template/code.html',
        link: function(scope, element, attrs){

            scope.optionList = [{
                  id: 1,
                  label: 'php',
                  value: "php"
            }, {
                  id: 2,
                  label: 'javascript',
                  value: "javascript"
            }, {
                  id: 3,
                  label: 'html',
                  value: "html"
            }, {
                  id: 4,
                  label: 'css',
                  value: "css"
            }, {
                  id: 5,
                  label: 'objective-c',
                  value: "objc"
            }];
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
        link: function(scope, element, attrs) {
            scope.wantView = scope.updateCodeVar = false;
            scope.view = function(code) {


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
        link: function(scope, element, attrs) {


            scope.updateCodeValidation = function(el, id, codename) {
                scope.c.mirror.save();
                for (var i = 0; i < el.codes.length; i++) {
                    el.codes[i].content = $('#updateCodeMirror'+i).val();
                    console.log(el.codes[i].content);
                }
                scope.current = el;
                // le callback lance le test des nouveaux fichiers
                scope.c.code.updateCode(el.codes, id, codename, scope.majFiles);
            };
            scope.majFiles = function (mongoId) {
                var files = document.getElementsByClassName("updateFile");
                var toSend = [];
                for(var i=0;i<files.length; i++) {
                    if(typeof(files[i].files[0]) != 'undefined') {
                        scope.c.code.updateScreen(
                            files[i].files[0],
                            {position:i, mongoId:mongoId, type: 'update'},
                            scope.current.file[i]
                        );
                    }
                }
/*
                scope.c.wantView = !scope.c.wantView;
                scope.c.mirror.clear();
*/
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
