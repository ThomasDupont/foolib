
/***********************************************************************************************
 * Angular template - Angular example (user and Digital assets management) with a full native php REST API Angular friendly
 *   app.js Controller of Angular project
 *   Version: 0.1.2
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

(function () {
  'use strict';

    angular.module('routeApp', [
        'ngRoute',
        'ngSanitize'
    ]).config(['$routeProvider',
        function($routeProvider, IdleProvider, KeepaliveProvider) {
            $routeProvider
            .when('/login', {
                templateUrl: 'views/login/login.html',
                controller: 'LoginController'
            })
            .when('/contact/:msg?', {
                templateUrl: 'views/contact.html',
                controller: 'contactController'
            })
            .when('/register', {
                templateUrl: 'views/login/register.html',
                controller: 'LoginController'
            })
            .when('/home', {
                templateUrl: 'views/home.html'
            })
            .otherwise({
                templateUrl: 'views/home.html'
            });

        }
    ]);

})();
