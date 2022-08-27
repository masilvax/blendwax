app.controller('rejestracjaCtrl', function ($scope, $rootScope, $http, $window, $mdDialog, $mdToast) {
    
    /*
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
            $window.location = "#!home";
        }
                
    }, function myError(response) {
            alert("Błąd przy sprawdzaniu zalogowania: "+response.statusText);
    });*/
    
    $scope.zarejestruj = function (idAlbumu) {

        var formData = { 
            'akcja' : 'zarejestruj',
            'haslo' : $scope.haslo,
            'haslo2' : $scope.haslo2
        };
        var response = $http({
            method: "POST",
            url: 'core/http_rejestracja.php',
            params: formData,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function mySuccess(response) {

            if(response.data.odp!=="OK"){
                alert(response.data);
            }else{
                $rootScope.kto = response.data.kto;
                $window.location = "#!myCollection";
            }
            
        }, function myError(response) {
                alert("Error while registering a user: "+response.statusText);
        });

    };

});