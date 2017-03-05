angular.module('routeApp').factory('Upload', function($http, $location, $sce) {
    var controller = "code";
    return {
        csrfToken: "",
        test: function (vm) {
            //vm.injectNewFile = 'Salut';
            $http.post("http://localhost:8080/test").then(function(promise) {
                console.log(promise);
            });
        },

        upload: function (file, parentNodeId, profil , onSuccess) {

            var reader = new FileReader();
            reader.readAsDataURL(file.files[0]);
            var csrf = this.csrfToken;
            reader.onload = function(e) {
                 $http.post(
                   APP+"/ajax/upload/",
                   {file : reader.result, filename: file.files[0].name, pNodeId: parentNodeId, profil, csrf: csrf}
               ).then( function (promise){
                   if(promise.data.success) {
                       onSuccess(promise) ;
                   }
               });
            };
        },

        getCodes: function() {
            return $http.post(
                APP+"/"+controller+"/getcodes/",
                {csrf: this.csrfToken}
            );
        },
        createFile: function (content, title, langage, iteration, onSuccess) {
            //var base64 = "data:text/txt;base64,"+btoa(content);
            var base64 = [];
            for(var i=0; i<content.length; i++) {
                base64.push(btoa(content[i]));
            }

            $http.post(
                APP+"/"+controller+"/createfile/",
                {file : base64, filename: title, langage: langage, iteration: iteration, csrf: this.csrfToken}
            ).then( function(promise) {

                if(promise.data.success) {
                    onSuccess(promise) ;
                }
            });
        },
        supprCode: function(id) {
            return $http.post(APP+"/"+controller+"/supprcode/",
                {id: id, csrf: this.csrfToken}
            );
        },
        updateCode: function(el, id, name) {
            return $http.post(APP+"/"+controller+"/updatecode/",
                {codes: el, id: id, name: name, csrf: this.csrfToken}
            );
        }/*,

        deleteNode : function (nodeId) {
            return $http.post(
                APP+"/"+controller+"/deletenode/",
                {nodeId: nodeId, csrf: this.csrfToken}
            );
        },
        createFolder : function (nodeId, name) {
            return $http.post(
                APP+"/"+controller+"/createfolder/",
                {nodeId: nodeId, name: name, csrf: this.csrfToken}
            );
        }*/
    };
});
