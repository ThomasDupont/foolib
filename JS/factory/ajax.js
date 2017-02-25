angular.module('routeApp').factory('Ajax', function($http, $location, $sce) {
    var controller = "ajax";
    return {
        csrfToken: "",
        csrf: function () {
            return $http.post(APP+"/csrf/set/" , {controller: "CSRF"});
        },
        contact: function (text) {
          return $http.post(APP+"/"+controller+"/sendcontact/" , {text : text, csrf: this.csrfToken});
        },
        getHome: function (text) {
          return $http.post(APP+"/"+controller+"/gethome/" , { csrf: this.csrfToken});
        },
        checkUser: function (text) {
          return $http.post(APP+"/"+controller+"/checkuser/" , { csrf: this.csrfToken});
        },
        disconnect: function () {
          return $http.post(APP+"/"+controller+"/disconnect/" , { csrf: this.csrfToken}).then(function (promise) {
              $location.path('login');
          });
        },
        login: function (log, psw) {
          return $http.post(APP+"/"+controller+"/login/" , {login : log, password: psw, csrf: this.csrfToken});
        },
        register: function (log, psw) {
          return $http.post(APP+"/"+controller+"/register/" , {login : log, password: psw, csrf: this.csrfToken});
        }
    }
});
