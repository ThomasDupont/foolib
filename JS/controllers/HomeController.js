angular.module('routeApp').controller('HomeController', ['$scope', '$routeParams', '$http', '$location', 'Ajax', 'Upload',
    function($scope, $routeParams, $http, $location, Ajax, Upload){


        var vm = this,
            parent = $scope.$parent.$parent;
        if(!parent.isDisconnectable && (typeof parent.passByMain == undefined || parent.passByMain == false)) {
            location.replace('/');
            //$location.path('login');
        }
        vm.dataLoading = false;
        vm.codeLangage = [];
        vm.codeContent = [];
        vm.wantView = false;
        vm.userdir = USERDIR;




        vm.code = {
            currentcode: "",
            addCodeBool: false,
            nbSnippet: [1],
            addSnippet: false,
            view: function(code) {
                vm.wantView = !vm.wantView;
                this.currentcode = code;
            },
            createFile: function () {
                vm.dataLoading = true;
                var lang = [];
                var newObj = [];
                var d = new Date();
                for(var i=0;i<vm.codeLangage.length; i++) {
                    var tmpLang = vm.codeLangage[i].value;
                    lang.push(tmpLang);
                    newObj.push({
                        langage: tmpLang,
                        time: d.getTime(),
                        content: vm.codeContent[i]
                    });
                }
                _this = this;
                Upload.createFile(vm.codeContent, vm.codeTitle, lang, --i, function (promise) {
                    if(promise.data.success) {
                        id = promise.data.result[0];
                        var add = {
                            id: id,
                            name: vm.codeTitle,
                            codes: newObj
                        };
                        _this.addNode(add);
                        _this.addScreen(add);
                        _this.code.addCodeBool = !_this.code.addCodeBool;
                        alert('Votre snippet a été ajouté');
                    }
                    vm.dataLoading = !vm.dataLoading;
                });
            },
            addCode: function () {
                this.addCodeBool = !this.addCodeBool;
                this.addSnippet = !this.addSnippet;
                this.nbSnippet.length = 1;
            },
            growthNbSnippet: function() {
                this.nbSnippet.push(1);
            },
            addNode: function (el) {
                parent.tree.push(el);
                parent.nbSnippets = parent.tree.length;
            },
            supprCode : function (id) {
                Upload.supprCode(id).then(function(promise) {
                    vm.tempTree = [];
                    for(var i = 0;i<parent.tree.length; i++) {
                        var current = parent.tree[i];
                        if(current.id != id) {
                            vm.tempTree.push(parent.tree[i]);
                        }
                    }
                    delete parent.tree;
                    parent.tree = vm.tempTree;
                    delete vm.tempTree;
                    parent.nbSnippets = parent.tree.length;
                });
            },
            updateCode: function(el, id, name, callback) {
                var _id = id;
                Upload.updateCode(el, id, name).then(function (promise) {
                    //console.log(promise);
                    callback(_id);
                });
            },
            viewScreenAdd: false,
            addScreen: function(current) {
                current.file = [];

                //var file = document.getElementsByClassName("screenshot");
                var files = drop.getResultObject();
                for(var i=0; i<files.length; i++) {
                    Upload.upload(files[i].data, {pNodeId: $scope.parentNodeID, mongoId:current.id, type: 'create'} , function (promise) {
                        if(promise.data.success) {
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
            updateScreen: function(file, params, current) {
                params.pNodeId = $scope.parentNodeID;
                Upload.upload(file, params , function (promise) {
                    if(promise.data.success) {
                        current.path = promise.data.result.path;
                        current.id = promise.data.result.nodeId;
                    } else {
                        alert(promise.data.message);
                    }
                });
            },
            supprScreen: function(files, id) {
                Upload.supprScreen(files, id).then(function (promise) {

                });
            }
        };
        vm.search = {
            input: "",
            perform: function() {
                var find = [];
                for(var i=0; i<parent.tree.length;i++) {
                    var name = parent.tree[i].name;
                    if(name.match(this.input)) {
                        find.push({
                            name: name,
                            id: parent.tree[i].id,
                            codes: parent.tree[i].codes,
                            time: parent.tree[i].time
                        });
                    }
                }
                this.result = find;
            },
            result: ""
        };
      }
]);
