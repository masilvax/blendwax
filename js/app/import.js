app.controller('importCtrl', function ($scope, $rootScope, $http, $window, $mdDialog, $mdToast, $timeout) {
    
    let elmnt = document.getElementById('gura');
    elmnt.scrollIntoView({block: 'start'});
    
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
    });
    
    $scope.koniec=false;
    
    
    $scope.zatrzymajImportUpdate = function () {
        
        $rootScope.iLoaderImport = false;
        $rootScope.iButtonDisabled = false;
        $rootScope.iListaAlbumowNiezaimportowanych = [];
        $rootScope.iIterator = 0;
        $rootScope.iIteratorWFunkcji = 0;
        $rootScope.iButtonText = 'start';
        $rootScope.iTraktImportu = false;
        
        $scope.koniec=false;
        
        angular.forEach($rootScope.iTimeouty, function(v,k){
            $timeout.cancel(v);
        });
        
        $rootScope.iTimeouty = [];
        $rootScope.iTimeouty.length = 0;
        
    }
    
    $scope.uruchomIportUpdate = function() {
        $rootScope.iTraktImportu = true;
        $rootScope.iLoaderImport = true;
        $rootScope.iButtonDisabled = true;
        $rootScope.iButtonText = 'preparing';
        
        $scope.koniec=false;
        //to chyba nie moze byc w oddzielnej funkcji, bo asynchronicznie poleci
        //ta funkcja w phpie dopisze do kolekcji usera zaimportowane wczesniej a zwroci numery niezaimportowanych
        $http.get('core/http_import.php?akcja=pobierzAlbumyDoWciagniecia').then(function (response) {
            //First function handles success
            if (response.data === "Sesja wygasła - proszę odświezyć stronę i zalogować się ponownie") {
                $window.location = "#!login";
            } else {
                if(response.data.odp==="OK"){
                
                    $rootScope.iListaAlbumowNiezaimportowanych = [];
                    $rootScope.iListaAlbumowNiezaimportowanych.length = 0;
                    $rootScope.iIterator = 0;
                    $rootScope.iIteratorWFunkcji = 0;
                    
                    $rootScope.iListaAlbumowNiezaimportowanych = response.data.doImportu;
                    
                    $rootScope.iButtonDisabled = false;
                    $rootScope.iButtonText = 'stop';
                    
                    if($rootScope.iListaAlbumowNiezaimportowanych.length > 0){
                        angular.forEach($rootScope.iListaAlbumowNiezaimportowanych, function (value, key) {//tu sa same value chyba

                            $rootScope.iTimeouty.push( $timeout(function () { $scope.importujAlbumIPiosenki(value); },1000 * ($rootScope.iIterator+1)) );
                            $rootScope.iIterator++;
                            
                        });
                    }else{
                        $scope.showSimpleToast('import finished',3000);
                        $rootScope.iLoaderImport = false;
                        $rootScope.iButtonDisabled = false;
                        $rootScope.iButtonText = 'start';
                        $rootScope.iTraktImportu = false;
                    }
                }else{
                    $scope.showSimpleToast(response.data,3000);
                    $rootScope.iLoaderImport = false;
                    $rootScope.iButtonDisabled = false;
                    $rootScope.iButtonText = 'start';
                    $rootScope.iTraktImportu = false;
                }
                
            }
        }, function (response) {
            //Second function handles error
            alert("error loading discogs releases in http request");
        });
        
    };
    

    $scope.importujAlbumIPiosenki = function (idAlbumu) {

        if(!$rootScope.iTraktImportu){
            return false;
        }

        var formData = { 
            'akcja' : 'importPiosenek',
            'idAlbumuDiscogs' : idAlbumu
        };
        var response = $http({
            method: "POST",
            url: 'core/http_import.php',
            params: formData,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function mySuccess(response) {

            $scope.showSimpleToast(response.data);
                    
        }, function myError(response) {
                alert("Error while importing a release: "+response.statusText);
        });
        
        $rootScope.iIteratorWFunkcji++;//potrzebne zeby tu zwiekszal a nie poza timeoutem        
        console.log($rootScope.iListaAlbumowNiezaimportowanych.length+"--"+$rootScope.iIteratorWFunkcji);
        if($scope.iIteratorWFunkcji >= $rootScope.iListaAlbumowNiezaimportowanych.length){
            $rootScope.iLoaderImport = false;
            $rootScope.iButtonDisabled = false;
            $rootScope.iButtonText = 'start';
            $rootScope.iTraktImportu = false;
            $scope.koniec=true;
        }
    };
    
    
    $scope.showSimpleToast = function(strink,delay=500) {

        $mdToast.show(
            $mdToast.simple()
                .textContent(strink)
                .position('bottom right')
                .hideDelay(delay)
        );
    };

});