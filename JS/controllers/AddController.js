angular.module('foolib').controller('AddController', [
    '$scope',
    '$routeParams',
    '$http',
    '$location',
    'Ajax',
    'Upload',
    'mainFactory',
    function ($scope, $routeParams, $http, $location, Ajax, Upload, mainFactory) {
        var vm = this;
        vm.mainFactory = mainFactory;
        vm.mainFactory.init().then(function () {
            vm.dataLoading          = false;
            vm.codeLangage          = [];
            vm.codeContent          = [];
            vm.codeTitle            = "";
            vm.codeDesc             = "";
            mainFactory.viewClass   = 'main';
            vm.mirror               = new codeMirror();
            vm.mirrorTheme          = "midnight";
            vm.listlang             = LISTLANG;
            mainFactory.triggerFunction(true, function (){
                vm.mirror.init();
            }, $scope);

            vm.code = {
                addCodeBool: false,
                nbSnippet: [{
                    'lang': "php",
                    'content': ""
                }],
                addSnippet: false,

                createFile: function () {
                    vm.mirror.save();
                    if (document.getElementById('codeMirror0').value == "" || vm.codeTitle == "") {
                        alert("incomplete form");
                        return false;
                    }
                    vm.dataLoading = true;
                    var lang       = [];
                    var newObj     = [];
                    var d          = new Date();
                    var _this = this;

                    for (var i = 0; i < this.nbSnippet.length; i++) {
                        var codeContent = document.getElementById('codeMirror'+i).value;
                        vm.codeContent.push(codeContent);
                        this.nbSnippet[i].content = codeContent;
                        var tmpLang = this.nbSnippet[i].lang;
                        lang.push(tmpLang);
                        newObj.push({
                            langage: tmpLang,
                            time: d.getTime(),
                            content: this.nbSnippet[i].content
                        });
                    }

                    Upload.createFile(vm.codeContent, vm.codeTitle, vm.codeDesc, lang, --i, function (promise) {
                        var id = promise.data.result[0];
                        var add = {
                            id: id,
                            name: vm.codeTitle,
                            description: vm.codeDesc,
                            codes: newObj
                        };
                        _this.addNode(add);
                        _this.addScreen(add);
                        alert('Your snippet has been add');
                        _this.addCodeBool = !_this.addCodeBool;
                        vm.dataLoading = !vm.dataLoading;
                        $location.path('home');
                    }, function (data) {
                        alert(data.message);
                        _this.addCodeBool = !_this.addCodeBool;
                        vm.dataLoading = !vm.dataLoading;
                    });
                },
                addCode: function () {
                    this.addCodeBool = !this.addCodeBool;
                    if(this.addCodeBool) {
                        setTimeout(function() {
                            vm.mirror.init();
                        }, 100);
                    } else {
                        vm.mirror.clear();
                    }
                    this.addSnippet = !this.addSnippet;
                    this.nbSnippet= [{
                        'lang': "php",
                        'content': ""
                    }];
                },
                growthNbSnippet: function () {
                    this.nbSnippet.push({
                        'lang': "php",
                        'content': ""
                    });
                    var len = this.nbSnippet.length - 1;
                    mainFactory.triggerFunction(true, function() {
                        vm.mirror.refresh(document.getElementById("codeMirror"+len));
                    }, $scope);
                },
                decreaseNbSnippet: function(it) {
                    this.nbSnippet.splice(it, 1);
                },
                addNode: function (el) {
                    mainFactory.tree.push(el);
                    mainFactory.nbSnippets = mainFactory.tree.length;
                },
                viewScreenAdd: false,
                addScreen: function (current) {
                    current.file = [];
                    var files = drop.getResultObject();
                    for (var i = 0; i < files.length; i++) {
                        Upload.upload(files[i].data, {
                            pNodeId: $scope.parentNodeID,
                            mongoId: current.id,
                            type: 'create'
                        }, function (promise) {
                            if (promise.data.success) {
                                current.file.push({
                                    path: promise.data.result.path,
                                    id: promise.data.result.nodeId
                                });
                            } else {
                                alert(promise.data.message);
                            }
                        });
                    }
                    this.viewScreenAdd = false;
                },
            };
        }).catch(function(error) {
            Ajax.onError(error)
        });
    }
]);
