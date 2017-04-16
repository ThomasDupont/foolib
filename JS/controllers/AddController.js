angular.module('routeApp').controller('AddController', ['$scope', '$routeParams', '$http', '$location', 'Ajax', 'Upload',
    function ($scope, $routeParams, $http, $location, Ajax, Upload) {
        var vm = this,
            parent = $scope.$parent.$parent;
        if (!parent.isDisconnectable && (typeof parent.passByMain == undefined || parent.passByMain == false)) {
            location.replace('/');
        }
        vm.dataLoading = false;
        vm.codeLangage = [];
        vm.codeContent = [];
        vm.codeTitle = "";
        vm.codeDesc = "";
        //document.getElementsByTagName('nav')[0].style.display = 'none';
        $scope.$parent.viewClass = 'main';
        vm.mirror = new codeMirror();
        vm.mirrorTheme = "midnight";
        setTimeout(function() {
            vm.mirror.init();
        }, 100);

        vm.code = {
            addCodeBool: false,
            nbSnippet: [{
                'lang': "php",
                'content': ""
            }],
            addSnippet: false,

            createFile: function () {
                vm.mirror.save();

                if ($("#codeMirror0").val() == "" || vm.codeTitle == "" || lang == "" || vm.codeDesc == "") {
                    alert("formulaire incomplet");
                    return false;
                }
                vm.dataLoading = true;
                var lang = [];
                var newObj = [];
                var d = new Date();

                for (var i = 0; i < this.nbSnippet.length; i++) {
                    var codeContent = $("#codeMirror"+i).val();
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
                _this = this;
                Upload.createFile(vm.codeContent, vm.codeTitle, vm.codeDesc, lang, --i, function (promise) {

                    id = promise.data.result[0];
                    var add = {
                        id: id,
                        name: vm.codeTitle,
                        codes: newObj
                    };
                    _this.addNode(add);
                    _this.addScreen(add);
                    alert('Votre snippet a été ajouté');
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
                setTimeout(function() {
                    vm.mirror.refresh(document.getElementById("codeMirror"+len));
                }, 50);
            },
            decreaseNbSnippet: function(it) {
                this.nbSnippet.splice(it, 1);
                /*
                var neu = [];
                for (var i = 0; i < this.nbSnippet.length; i++) {
                    if(i != it) {
                        delete this.nbSnippet[i];
                        //neu.push(this.nbSnippet[i]);
                    }
                }
                */
                console.log(this.nbSnippet);
                //this.nbSnippet = neu;
            },
            addNode: function (el) {
                parent.tree.push(el);
                parent.nbSnippets = parent.tree.length;
            },
            viewScreenAdd: false,
            addScreen: function (current) {
                current.file = [];

                //var file = document.getElementsByClassName("screenshot");
                var files = drop.getResultObject();
                for (var i = 0; i < files.length; i++) {
                    Upload.upload(files[i].data, {pNodeId: $scope.parentNodeID, mongoId: current.id, type: 'create'}, function (promise) {
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
    }
]);
