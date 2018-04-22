angular.module('foolib').factory('Upload', function($http, $location, $sce) {
    var controller = "code";
    return {
        csrfToken: "",

        upload: function (file, params, onSuccess) {

            var reader = new FileReader();

            if(typeof(file) != "undefined") {
                var csrf = this.csrfToken;
                reader.readAsDataURL(file);
                reader.onload = function(e) {
                    var filename = file.name;
                    var split = filename.split('.').pop();
                    if(split.length < 2) {
                        alert("fichier sans extension");
                        return false;
                    }
                    var ext = false;
                    for(var i = 0; i < FILEEXT.length; i++) {
                        if(FILEEXT[i] == split) ext = true;
                    }
                    if(!ext){
                        alert("fichier avec extension non autorisée");
                        return false;
                    }
                    $http.post(
                       APP+"/user/upload/",
                       {file : reader.result, filename: filename, params: params, csrf: csrf}
                   ).then(function (result){
                       if(result.data.success) {
                           onSuccess(result) ;
                       }
                   });
                };
            }
        },

        getCodes: function() {
            return $http.post(
                APP+"/"+controller+"/getcodes",
                {csrf: this.csrfToken}
            );
        },
        createFile: function (content, title, description, langage, iteration, onSuccess, onError) {
            //var base64 = "data:text/txt;base64,"+btoa(content);
            var base64 = [];
            for(var i=0; i<content.length; i++) {
                base64.push(btoa(content[i]));
            }

            $http.post(
                APP+"/"+controller+"/createfile",
                {file : base64, filename: title, description: description, langage: langage, iteration: iteration, csrf: this.csrfToken}
            ).then( function(promise) {

                if(promise.data.success) {
                    onSuccess(promise) ;
                } else {
                    onError(promise.data);
                }
            });
        },
        supprCode: function(id) {
            return $http.post(APP+"/"+controller+"/supprcode",
                {id: id, csrf: this.csrfToken}
            );
        },
        updateCode: function(el, id, name) {
            return $http.post(APP+"/"+controller+"/updatecode",
                {codes: el, id: id, name: name, csrf: this.csrfToken}
            );
        },
        supprScreen: function(files, id, oldNodeId) {
            return $http.post(APP+"/"+controller+"/supprscreen",
                {files: files, id: id, oldNodeId: oldNodeId, csrf: this.csrfToken}
            );
        }
    };
});
