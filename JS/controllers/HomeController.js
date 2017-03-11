angular.module('routeApp').controller('HomeController', ['$scope', '$http', '$location', 'Ajax', 'Upload',
    function($scope, $http, $location, Ajax, Upload){

        var vm = this;
        var parent = $scope.$parent.$parent;
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
                        _this.addNode({
                            id: id,
                            name: vm.codeTitle,
                            codes: newObj
                        });
                        _this.addScreen(id);
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
                Upload.updateCode(el, id, name).then(function (promise) {
                    console.log(promise);
                    callback(promise);
                });
            },
            viewScreenAdd: false,
            addScreen: function(mongoId) {
                var nodeID;
                for(var i=0, nodes = $scope.$parent.$parent.nodes; i<nodes.length; i++) {
                    if(nodes[i].parentNode_ID == 0) {
                        nodeId = nodes[i].node_ID;
                    }
                }

                //var file = document.getElementsByClassName("screenshot");
                var files = drop.getResultObject();
                for(var i=0; i<files.length; i++) {
                    Upload.upload(files[i].data, {pNodeId: nodeId, mongoId:mongoId, type: 'create'} , function (promise) {
                        console.log(promise);
                    });
                }
                this.viewScreenAdd = false;
            },
            updateScreen: function(file, params, current) {
                Upload.upload(file, params , function (promise) {
                    if(promise.data.success) {
                        current = {
                            'path': promise.data.result.path,
                            'id': promise.data.result.nodeId
                        };
                    } else {
                        alert(promise.data.message);
                    }
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
