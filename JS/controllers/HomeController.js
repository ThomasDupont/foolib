
angular.module('foolib').controller('HomeController', [
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
        vm.mainFactory.init().then(function (promise) {
                vm.dataLoading          = false;
                vm.codeLangage          = [];
                vm.codeContent          = [];
                vm.wantView             = false;
                vm.userdir              = USERDIR;
                vm.mirror               = new codeMirror();
                vm.mirrorTheme          = "midnight";
                mainFactory.viewClass   = 'main';
                vm.style_general        = mainFactory.style_general;
                vm.listSnippet          = [];
                vm.wantView             = false;
                vm.listlang             = LISTLANG;

                vm.code = {
                    currentcode: "",
                    addCodeBool: false,
                    nbSnippet  : [1],
                    addSnippet : false,
                    langage    : [],
                    langArr    : [],
                    langValue  : "HTML",
                    view: function (code, e) {

                        document.getElementById('results').style.display = "none";
                        if(e != null) {
                            vm.style_general.liste_ul_li(e);
                        }
                        vm.wantView = true;
                        vm.mirror.clear();
                        this.currentcode = code;
                        mainFactory.triggerFunction(true, function (){
                            vm.mirror.init();
                        }, $scope, 0);


                    },
                    showCodeInLoadPage: function (i) {
                        var i = i || 0;
                        vm.listSnippet = this.manageListCode(LISTLANG[i].label);
                        i++;
                        if(typeof LISTLANG[i] != 'undefined' && vm.listSnippet.length == 0) {
                            this.showCodeInLoadPage(i);
                        } else if (vm.listSnippet.length != 0) {
                            this.view(vm.listSnippet[0], null);
                        }
                    },
                    manageListCode: function(select) {
                        var snippets = [];
                        //affichage des code selectionnés lorsqu'ils contiennent le langage
                        for (var i = 0; i < mainFactory.tree.length; i++) {
                            var langArr = [],
                                continu = false,
                                codes   = mainFactory.tree[i];
                            for (var j = 0; j < codes.codes.length; j++) {
                                langArr.push(codes.codes[j].langage);
                                if(!continu) {
                                    var lang = codes.codes[j].langage;
                                    var langValue = "";
                                    for (var k = 0; k <  LISTLANG.length; k++) {
                                       if(LISTLANG[k].label == select) {
                                           langValue = LISTLANG[k].value;
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
                        this.langValue = select;
                        return snippets;

                    },
                    viewListeCode: function(e) {
                        vm.wantView = false;

                        vm.style_general.popup_languages_ul_li(e);

                        vm.listSnippet = this.manageListCode(vm.style_general.selectLang);
                        if(vm.listSnippet.length != 0) {
                            this.view(vm.listSnippet[0], null);
                        }
                    },
                    changeMirrorTheme: function() {
                        vm.mirror.updateTheme(vm.mirrorTheme);
                    },
                    supprCode: function (id) {
                        Upload.supprCode(id).then(function (promise) {
                            vm.tempTree = [];
                            for (var i = 0; i < mainFactory.tree.length; i++) {
                                var current = mainFactory.tree[i];
                                if (current.id != id) {
                                    vm.tempTree.push(mainFactory.tree[i]);
                                }
                            }
                            delete mainFactory.tree;
                            mainFactory.tree = vm.tempTree;
                            delete vm.tempTree;
                            mainFactory.nbSnippets = mainFactory.tree.length;
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

                vm.code.showCodeInLoadPage();
                //search bar
                vm.search = {
                    input: "",
                    perform: function () {
                        var find = [];
                        document.getElementById('results').style.display = (this.input.length > 1) ?  "block": "none";

                        var re = new RegExp(this.input,"i");
                        for (var i = 0; i < mainFactory.tree.length; i++) {
                            var name = mainFactory.tree[i].name;
                            var description = mainFactory.tree[i].description;
                            if (name.match(re)) {
                                find.push({
                                    name        : name,
                                    description : description,
                                    id          : mainFactory.tree[i].id,
                                    codes       : mainFactory.tree[i].codes,
                                    time        : mainFactory.tree[i].time
                                });
                            }
                        }
                        this.result = find;
                    },
                    result: ""
                };


                //profil
                vm.fileOk = false;
                $scope.fileNameChanged = function() {
                   vm.fileOk = true;
                }
                vm.genericModel = "";
                vm.updateProfil = {
                    closeModif: function(e) {
                        this.disabled = !this.disabled;
                        $('#pp_profile_edit').css('display', 'none');
                        vm.style_general.close_edit_profile(e);
                    },
                    viewModif: function(e) {
                        this.disabled = !this.disabled;
                        $('#pp_profile_edit').css('display', 'block');
                        vm.style_general.l_edit_profile(e);
                    },
                    update: function() {
                        if(vm.fileOk) {
                            var file = document.getElementsByClassName('fileUploadPPup');
                            var nodeID;
                            for(var i=0, nodes = mainFactory.nodes; i<nodes.length; i++) {
                                if(nodes[i].parentNode_ID == 0) {
                                    nodeId = nodes[i].node_ID;
                                }
                            }

                            Upload.upload(file[0].files[0], {pNodeId:nodeId, type: 'profil'}, function (promise) {
                                vm.fileOk = false;
                                mainFactory.pprofil = USERDIR+promise.data.result.path;
                            });
                        }
                        if(this.modifPasswordNew.length < 6 && this.modifPasswordNew.length > 0) {
                            alert('The password is too short');
                            return false;
                        }
                        Ajax.updateProfil(
                            this.modifPasswordOld,
                            this.modifPasswordNew,
                            mainFactory.userName,
                            mainFactory.userEmail
                        ).then(function (promise) {
                            if(promise.data.success) {
                                vm.showUpdate = !vm.showUpdate;
                            } else {
                                alert(promise.data.message);
                            }
                        });

                    },
                    disabled:true,
                    selectPP: function () {
                        this.disabled || document.getElementById('fileUploadPPup').click();
                    },
                    modifPasswordOld : "",
                    modifPasswordNew : ""
                }

        }).catch(function(error) {
            Ajax.onError(error)
        });
    }
]);
