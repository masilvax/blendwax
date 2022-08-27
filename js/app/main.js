// Script.js

var app = angular.module('blendwax', ['ngRoute', 'ngAnimate','ngMaterial','duScroll','ngSanitize','ngAdsense']);
  /*  .run( ['$rootScope', function( $rootScope )  {
    }]
);*/
app.config(['$routeProvider', '$interpolateProvider', '$mdThemingProvider', '$mdIconProvider', '$mdGestureProvider', function ($routeProvider, $interpolateProvider, $mdThemingProvider, $mdIconProvider,$mdGestureProvider) {
    
    
    $mdGestureProvider.skipClickHijack();//zeby na mobilnych inputy nie zostawały sfocusowane po kliknięciu poza nie
    
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
    
    /*$mdThemingProvider.theme('forest')
      .primaryPalette('purple')
      .accentPalette('teal');*/
      
    $mdThemingProvider.theme('default')
      .primaryPalette('purple')
      .accentPalette('teal');
      
    $mdThemingProvider.theme('ciemny')
      .primaryPalette('purple')
      .accentPalette('teal')
      .dark();
      
    $mdThemingProvider.enableBrowserColor({
        theme: 'default', // Default is 'default'
        palette: 'primary', // Default is 'primary', any basic material palette and extended palettes are available
        hue: '300' // Default is '800'
          
    });
     
    $mdIconProvider.defaultIconSet('img/icons/sets/social-icons.svg', 24);//???i tak to robię fontami
    
    $routeProvider
     .when("/myCollection", {
        templateUrl : "templates/mojaKolekcja.htm",
        controller : "mojaKolekcjaCtrl"
    })
    .when("/release/:idAlbumu", {
        templateUrl : "templates/album.htm",
        controller : "albumCtrl"
    })
    .when("/myBlends", {
        templateUrl : "templates/mojeBlendy.htm",
        controller : "mojeBlendyCtrl"
    })
    .when("/blend/:idBlendu", {
        templateUrl : "templates/blend.htm",
        controller : "blendCtrl"
    })
    .when("/mySets", {
        templateUrl : "templates/mojeSety.htm",
        controller : "mojeSetyCtrl"
    })
    .when("/set/:idSetu", {
        templateUrl : "templates/set.htm",
        controller : "setCtrl"
    })
    
    .when("/othersBlends", {
        templateUrl : "templates/cudzeBlendy.htm",
        controller : "cudzeBlendyCtrl"
    })
    .when("/othersBlend/:idBlendu", {
        templateUrl : "templates/cudzyBlend.htm",
        controller : "cudzyBlendCtrl"
    })
    .when("/othersSets", {
        templateUrl : "templates/cudzeSety.htm",
        controller : "cudzeSetyCtrl"
    })
    .when("/othersSet/:idSetu", {
        templateUrl : "templates/cudzySet.htm",
        controller : "cudzySetCtrl"
    })
    
    .when("/login", {
        templateUrl : "templates/login.htm",
        controller : "loginCtrl"
    })
    .when("/home", {
        templateUrl : "templates/home.htm",
        controller : "mainCtrl"
    })
    .when("/import", {
        templateUrl : "templates/import.htm",
        controller : "importCtrl"
    })
    .when("/register", {
        templateUrl : "templates/rejestracja.htm",
        controller : "rejestracjaCtrl"
    })
    .when("/cookies", {
        templateUrl : "templates/ciasteczka.htm",
        controller : "ciasteczkaCtrl"
    })
    .when("/terms", {
        templateUrl : "templates/warunki.htm",
        controller : "warunkiCtrl"
    })
    .when("/privacy", {
        templateUrl : "templates/prywatnosc.htm",
        controller : "prywatnoscCtrl"
    })
    .otherwise({
        redirectTo: '/home'
    });
    
}]);

app.directive('numberMaker', function () {
        return {
            link: function(scope, element, attrs) {
                element.on('keyup', function (e) {
                    e.target.value=e.target.value.replace(/[^0-9]/, '');
                })
            }
        }
});
app.directive('amountMaker', function () {
        return {
            link: function(scope, element, attrs) {
                element.on('keyup', function (e) {
                    e.target.value=e.target.value.replace(/[^0-9.]/, '');
                    e.target.value=e.target.value.replace("..", '');
                })
            }
        }
});

app.directive('ngFiles',['$parse', function ($parse) {
    function fn_link(scope, element, attrs) {
        var onChange = $parse(attrs.ngFiles);
        element.on('change', function (event) {
            onChange(scope, { $files: event.target.files })
        });
    };
    return { link: fn_link }
} ]);

app.directive("scroll", function ($window) {
    return function(scope, element, attrs) {
        angular.element($window).bind("scroll", function() {
            console.log(this.pageYOffset); 
            if (this.pageYOffset >= 30) {
                 scope.menuShowSwitch = true;
                 //console.log('Scrolled below header.');
             } else {
                 scope.menuShowSwitch = false;
                 //console.log('Header is in view.');
             }
            scope.$apply();
        });
    };
});

app.controller('ciasteczkaCtrl', function ($scope) {
    let elmnt = document.getElementById('gura');
    elmnt.scrollIntoView({block: 'start'});
});
app.controller('warunkiCtrl', function ($scope) {
    let elmnt = document.getElementById('gura');
    elmnt.scrollIntoView({block: 'start'});
});
app.controller('prywatnoscCtrl', function ($scope) {
    let elmnt = document.getElementById('gura');
    elmnt.scrollIntoView({block: 'start'});
});

app.controller('loginCtrl', function ($scope, $http, $window, $rootScope, $mdTheming) {  
    
    $scope.zaloguj = function () {
        var formDataKlient = {
            'akcja_s' : 'login',
            'login' : $scope.login,
            'haslo' : $scope.haslo,
        };
        var response = $http({
            method: "POST",
            url: 'core/session.php',
            params: formDataKlient,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function mySucces(response) {
            if (angular.isUndefined(response.data.login)) {
                alert(response.data);
            } else {
                $window.location = "#!myCollection";
                //$rootScope.ileZostaloPobierz();
                $rootScope.kto = response.data.login;
            }
            
        }, function myError(response) {
             alert("Error during log in");
        });
        
    };
});

    
app.controller('mainCtrl', function ($scope, $http, $window, $mdSidenav, $rootScope, $mdToast, $mdTheming) {  

    /*let elmnt = document.getElementById('home');
    elmnt.scrollIntoView({block: 'start'});//elmnt is null!!! */
    
    $rootScope.iLoaderImport = false;
    $rootScope.iButtonDisabled = false;
    $rootScope.iListaAlbumowNiezaimportowanych = [];
    $rootScope.iIterator = 0;
    $rootScope.iIteratorWFunkcji = 0;
    $rootScope.iButtonText = 'start';
    $rootScope.iTraktImportu = false;
    $rootScope.iTimeouty = [];
    
    $rootScope.kStrona = 1;
    $rootScope.kSort = 'artist';
    $rootScope.kDesc = false;
    
    $rootScope.bStrona = 1;
    $rootScope.bSort = 'artysta1';
    $rootScope.bDesc = false;
    
    $rootScope.sStrona = 1;
    $rootScope.sSort = 'data';
    $rootScope.sDesc = true;
    
    $scope.kontakt = [];
        $scope.kontakt.loader=false;

    $scope.openLeftMenu = function() {
        $mdSidenav('left').toggle();
    };
    
    $scope.wyloguj = function () {

        var formDataKlient = {
            'akcja_s' : 'logout'
        };
        var response = $http({
            method: "POST",
            url: 'core/session.php',
            params: formDataKlient,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function mySuccess(response) {
    	    if(response.data==="OK") {
    		location.reload();
    	    } else {
    		alert(response.data);
    	    }
        }, function myError(response){
    	    alert("Error during log out : "+response.statusText);
        });

    };
    
    $scope.przejdzDo = function(url){
        $mdSidenav('left').toggle();
        $window.location = "#!"+url;
    }
    $scope.przejdzDoNormalnie = function(url){
        $window.location = "#!"+url;
    }
    $scope.przejdzDoNajnormalniej = function(url){
        $window.location = url;
    }
    $scope.przesunDo = function (sekcja) {
        let elmnt = document.getElementById(sekcja);
        elmnt.scrollIntoView({behavior: 'smooth', block: 'start'});
    }
    
    $scope.wyslijEmail = function(){
        
        $scope.kontakt.loader=true;
        var formDataKlient = {
            'email' : $scope.kontakt.email,
            'tresc' : $scope.kontakt.question
        };
        //alert(formDataKlient.email + formDataKlient.tresc);
        var response = $http({
            method: "POST",
            url: 'core/msg.php',
            params: formDataKlient,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function mySuccess(response) {
    	    if(response.data==="OK") {
                $mdToast.show(
                    $mdToast.simple()
                        .textContent('Thank you for your message. I will reply as soon as I read it.')
                        .position('bottom right')
                        .hideDelay(10000)
                );
    	    } else {
    		alert(response.data);
    	    }
            $scope.kontakt.loader=false;
        }, function myError(response){
    	    alert("Error sending message: "+response.statusText);
            $scope.kontakt.loader=false;
        });
    }

    $scope.potwierdzCiasto = function(){//ajax tylko, żeby na przyszłość zapisał do sesji,bo wystarczy,że kliknie i zmienna sie ustawi na true
        var formDataKlient = {
            'akcja_s' : 'potwierdzCiasto'
        };
        var response = $http({
            method: "POST",
            url: 'core/session.php',
            params: formDataKlient,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function mySuccess(response) {
    	    if(response.data!=="OK") {
                $mdToast.show(
                    $mdToast.simple()
                        .textContent("Something went wrong while accepting cookies, but do not worry. It's not a big deal :)")
                        .position('bottom right')
                        .hideDelay(10000)
                );
    	    }

        }, function myError(response){
    	    alert("Error accepting cookies: "+response.statusText);
        });
        $rootScope.potwierdzoneCiasto = true;
    }
    
    /*var removeFunction = $mdTheming.setBrowserColor({
      theme: 'default', // Default is 'default'
      palette: 'primary', // Default is 'primary', any basic material palette and extended palettes are available
      hue: '300' // Default is '800'
    });

    $scope.$on('$destroy', function () {
      removeFunction(); // COMPLETELY removes the browser color
    });*/
    //alert($rootScope.potwierdzoneCiasto);
    //$scope.potwierdzCiasto();
});

app.controller('toastCtrl', function($scope,$mdToast) {
    $scope.closeToast = function() {
        $mdToast.hide();
    }
});


app.run(function($rootScope, $window, $http, $location, $anchorScroll, $document) {
    //when the route is changed scroll to the proper element.
    /*$anchorScroll.yOffset = 50;
    $rootScope.$on('$routeChangeSuccess', function(newRoute, oldRoute) {
        if($location.hash()) $anchorScroll();
    });*/
    
    //tu sprawdzanie sesji, poza rootscope.on, bo by w nieskonczonosc window.location robil
        
    //alert("abs:"+$location.absUrl());
    
        
        var formDataKlient = { 
            'akcja_s' : 'check'
        };
        var response = $http({
            method: "POST",
            url: 'core/session.php',
            params: formDataKlient,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function mySuccess(response) {

            if (response.data==="NIE") {
                
                if($location.absUrl()!=='http://blendwax.com/#!/login' && $location.absUrl()!=='http://blendwax.com/#!/register'){//GDY NIE JEST ZALOGOWANY, zeby mozna bylo odswiezac gdy url!=register lub url!=login i zeby z discogsa przekierowalo do register
                
                    $window.location = "#!home";
                }
            } else {
                $rootScope.kto = response.data.login;
                $rootScope.potwierdzoneCiasto = response.data.potwierdzoneCiasto;
            }
                
        }, function myError(response) {
             alert("Error checking who's logged in: "+response.statusText + ". It sometimes happens when you start the browser and there's BlendWax on one of the cards. Just hit 'OK' and it should be OK");
        });
    
    $anchorScroll.yOffset = 50;
    $rootScope.stateIsLoading = false;
    
    $rootScope.$on('$routeChangeStart',function(){
        $rootScope.stateIsLoading = true;
    });
    $rootScope.$on('$routeChangeSuccess',function(){
        //console.log($location.hash()+"---"+$document[0].body.scrollTop+"---"+$document[0].documentElement.scrollTop);
        //$document[0].body.scrollTop = $document[0].documentElement.scrollTop = 0;// tego chyba nie musi być bo i tak wjezdza na góre
        //alert('chuj');
        $rootScope.stateIsLoading = false;
        //if($location.hash()) $anchorScroll();//przesuwa na gore - chuja tam - nie przesuwa. A u Napieraly przesuwa EDIT: bo mam #view i to jest ten anchor
        /*let elmnt = document.getElementById('ngwju');
        elmnt.scrollIntoView({behavior: 'smooth', block: 'start'});*/
        //$location.hash();
        //$anchorScroll();
        //let elmnt = document.getElementById('gura');
        //elmnt.scrollIntoView({block: 'start'});
    });
    $rootScope.$on('$routeChangeError', function() {
        //catch error
    });
    
    
});
