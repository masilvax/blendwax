app.controller('cudzySetCtrl', function ($scope, $http, $window, $routeParams, $mdDialog) {
    
    $scope.idSetuRequest = $routeParams.idSetu;

    if($scope.idSetuRequest === 'new'){
        $scope.idSetuRequest = '0';
    }
    $scope.lista = [];
    $scope.loaderDanych = false;
    $scope.loaderUp=false;
    $scope.loaderDown=false;
    
    $scope.pobierzListe = function () {
        $scope.loaderDanych = true;
        let url = 'core/http_sety.php?akcja=cudzySet&idSetu='+$scope.idSetuRequest;

        $http.get(url).then(function (response) {
            //First function handles success
            if (response.data === "Sesja wygasła - proszę odświezyć stronę i zalogować się ponownie") {
                $window.location = "#!login";
            } else {
                if(response.data.odp !=='OK'){
                    alert(response.data);
                }
                $scope.lista = response.data;
                $scope.loaderDanych = false;
            }
        }, function (response) {
            //Second function handles error
            alert("Error loading set in http...php");
        });
        let elmnt = document.getElementById('gura');
        elmnt.scrollIntoView({block: 'start'});
    };
    $scope.pobierzListe();
    
    $scope.likeIt = function (id) {
        
        $scope.loaderUp=true;
        
        var formData = {
            'akcja' : 'likeIt',
            'idSetu' : id
        };
        var response = $http({
            method: "POST",
            url: 'core/http_sety.php',
            params: formData,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function mySucces(response) {
            if(response.data.odp!=='OK') {
                alert(response.data);
            }
            $scope.loaderUp=false;
            $scope.pobierzListe(false);

        }, function myError(response) {
                alert("error liking set: "+response.StatusText);
        });
    };
    
    $scope.dislikeIt = function (id) {

        $scope.loaderDown=true;
        
        var formData = {
            'akcja' : 'dislikeIt',
            'idSetu' : id
        };
        var response = $http({
            method: "POST",
            url: 'core/http_sety.php',
            params: formData,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function mySucces(response) {
            if(response.data.odp!=='OK') {
                alert(response.data);
            }
            $scope.loaderDown=false;
            $scope.pobierzListe(false);

        }, function myError(response) {
                alert("error liking set: "+response.StatusText);
        });
    };
    
});