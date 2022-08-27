app.controller('albumCtrl', function ($scope, $http, $window, $routeParams, $mdDialog) {
    
    $scope.idAlbmumuRequest = $routeParams.idAlbumu;
    $scope.lista = [];
    $scope.loaderDanych = false;

    $scope.pobierzListe = function () {
        $scope.loaderDanych = true;
        $http.get('core/http_album.php?akcja=release&idAlbumu='+$scope.idAlbmumuRequest+'&ts='+Date.now()).then(function (response) {
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
            alert("błąd pobierania klientów z pliku http...php");
        });
        let elmnt = document.getElementById('gura');
        elmnt.scrollIntoView({block: 'start'});
    };
    $scope.pobierzListe();

});
