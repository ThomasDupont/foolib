
angular.module('routeApp').controller('HomeController', ['$scope', '$routeParams', '$http', '$location', 'Ajax', 'Upload',
    function ($scope, $routeParams, $http, $location, Ajax, Upload) {


        var vm = this,
            parent = $scope.$parent.$parent;
        if (!parent.isDisconnectable && (typeof parent.passByMain == undefined || parent.passByMain == false)) {
            location.replace('/');
            //$location.path('login');
        }
        vm.dataLoading = false;
        vm.codeLangage = [];
        vm.codeContent = [];
        vm.wantView = false;
        vm.userdir = USERDIR;
        vm.mirror = new codeMirror();
        vm.mirrorTheme = "midnight";
        $scope.$parent.viewClass = 'main';
        vm.style_general = $scope.$parent.style_general;
        vm.listSnippet = [];
        vm.wantView = false;
        setTimeout(function() {
            vm.mirror.init();
        }, 100);

        vm.code = {
            currentcode: "",
            addCodeBool: false,
            nbSnippet: [1],
            addSnippet: false,
            langage: [],
            langArr: [],
            langValue: "HTML",
            view: function (code, e) {
                console.log(code);
                document.getElementById('results').style.display = "none";
                if(e != null) {
                    vm.style_general.liste_ul_li(e);
                }
                //this.langage = code.langArr;
                vm.wantView = true;
                vm.mirror.clear();
                //vm.wantView = !vm.wantView;
                this.currentcode = code;
                //if(vm.wantView) {
                    setTimeout(function() {
                        vm.mirror.init();
                    }, 100);
                //} else {
                    //vm.mirror.clear();
                //}

            },
            viewListeCode: function(e) {
                vm.wantView = false;

                vm.style_general.popup_languages_ul_li(e);
                var snippets = [];
                //affichage des code selectionnés lorsqu'ils contiennent le langage
                for (var i = 0; i < $scope.$parent.tree.length; i++) {
                    var langArr = [];
                    var continu = false;
                    var codes = $scope.$parent.tree[i];
                    for (var j = 0; j < codes.codes.length; j++) {
                        langArr.push(codes.codes[j].langage);
                        if(!continu) {
                            var lang = codes.codes[j].langage;
                            var langValue = "";
                            for (var k = 0; k <  $scope.$parent.optionList.length; k++) {

                               if($scope.$parent.optionList[k].label == vm.style_general.selectLang) {

                                   langValue = $scope.$parent.optionList[k].value;
                               }
                            }

                            if(langValue == lang) {
                                //creation tableau de langue

                                snippets.push(codes);
                                continu = true;
                            }
                        }
                    }

                    codes.langArr = langArr;
                }
                this.langValue = vm.style_general.selectLang;
                vm.listSnippet = snippets;
                if(snippets.length != 0) {
                    this.view(snippets[0], null);
                }
            },
            changeMirrorTheme: function() {
                vm.mirror.updateTheme(vm.mirrorTheme);
            },
            supprCode: function (id) {
                Upload.supprCode(id).then(function (promise) {
                    vm.tempTree = [];
                    for (var i = 0; i < parent.tree.length; i++) {
                        var current = parent.tree[i];
                        if (current.id != id) {
                            vm.tempTree.push(parent.tree[i]);
                        }
                    }
                    delete parent.tree;
                    parent.tree = vm.tempTree;
                    delete vm.tempTree;
                    parent.nbSnippets = parent.tree.length;
                });
            },
            updateCode: function (el, id, name, callback) {
                var _id = id;
                Upload.updateCode(el, id, name).then(function (promise) {
                    //console.log(promise);
                    callback(_id);
                });
            },
            updateScreen: function (file, params, current) {
                params.pNodeId = $scope.parentNodeID;
                Upload.upload(file, params, function (promise) {
                    if (promise.data.success) {
                        current.path = promise.data.result.path;
                        current.id = promise.data.result.nodeId;
                    } else {
                        alert(promise.data.message);
                    }
                });
            },
            supprScreen: function (files, id, oldNodeId) {
                _this = this;
                Upload.supprScreen(files, id, oldNodeId).then(function (promise) {
                    _this.currentcode.file = files;
                });
            }
        };

        //search bar
        vm.search = {
            input: "",
            perform: function () {
                var find = [];
                if(this.input.length > 1) {
                    document.getElementById('results').style.display = "block";
                } else {
                    document.getElementById('results').style.display = "none";
                }
                for (var i = 0; i < parent.tree.length; i++) {
                    var name = parent.tree[i].name;
                    if (name.match(this.input)) {
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


        //profil
        //vm.showUpdate = false;
        vm.fileOk = false;
        $scope.fileNameChanged = function() {
           vm.fileOk = true;
        }
        vm.genericModel = "";
        vm.updateProfil = {
            /*show: function () {
                vm.showUpdate = !vm.showUpdate;
            },*/
            update: function() {
                if(vm.fileOk) {
                    var file = document.getElementsByClassName('fileUploadPPup');
                    var nodeID;
                    for(var i=0, nodes = $scope.$parent.$parent.nodes; i<nodes.length; i++) {
                        if(nodes[i].parentNode_ID == 0) {
                            nodeId = nodes[i].node_ID;
                        }
                    }

                    Upload.upload(file[0].files[0], {pNodeId:nodeId, type: 'profil'}, function (promise) {
                        vm.fileOk = false;
                        $scope.$parent.$parent.pprofil = USERDIR+promise.data.result.path;
                    });
                }
                Ajax.updateProfil(
                    this.modifPasswordOld,
                    this.modifPasswordNew,
                    $scope.$parent.userName,
                    $scope.$parent.userEmail
                ).then(function (promise) {
                    if(promise.data.success) {
                        vm.showUpdate = !vm.showUpdate;
                    } else {
                        alert(promise.data.message);
                    }
                });

            },
            modifPasswordOld : "aaaa",
            modifPasswordNew : "bbbb"
        }
    }
]);
