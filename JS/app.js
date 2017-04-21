
/***********************************************************************************************
 * Angular template - Angular example (user and Digital assets management) with a full native php REST API Angular friendly
 *   app.js Controller of Angular project
 *   Version: 0.1.2
 * Copyright 2016 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

(function () {
  'use strict';

    angular.module('foolib', [
        'ngRoute',
        'ngSanitize',
    ]).config(['$routeProvider',
        function($routeProvider, IdleProvider, KeepaliveProvider) {
            $routeProvider
            .when('/login', {
                templateUrl: 'views/login/login.html',
                controller: 'LoginController'
            })
            .when('/home', {
                templateUrl: 'views/home.html'
            })
            .when('/add', {
                templateUrl: 'views/add.html'
            })
            .when('/terms', {
                templateUrl: 'views/terms.html',
                controller: 'TermsController'
            })
            .when('/link', {
                templateUrl: 'views/login/link.html',
                controller: 'LinkController'
            })
            .otherwise({
                templateUrl: 'views/home.html'
            });

        }
    ]);

})();
