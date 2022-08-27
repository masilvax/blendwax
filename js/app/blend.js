app.controller('blendCtrl', function ($scope, $http, $window, $routeParams, $mdDialog) {
    
    $scope.idBlenduRequest = $routeParams.idBlendu;
    $scope.edycja = false;
    $scope.edycjaUkryj = false;
    if($scope.idBlenduRequest === 'new'){
        $scope.idBlenduRequest = '0';
        $scope.edycja = true;
        $scope.edycjaUkryj = true;
    }
    $scope.lista = [];
    $scope.loaderDanych = false;
    $scope.loaderPubl = false;
    $scope.loaderZapis = false;
    $scope.loaderCofnij = false;
    $scope.loaderPiosenki1 = false;
    $scope.loaderPiosenki2 = false;
    $scope.model = [];
        $scope.model.piosenki1 = [];
        $scope.model.piosenki2 = [];

    $scope.pobierzListe = function () {
        $scope.loaderDanych = true;
        let url = 'core/http_blendy.php?akcja=blend&idBlendu='+$scope.idBlenduRequest+'&ts='+Date.now();
        if($scope.idBlenduRequest==='0'){
            url = 'core/http_blendy.php?akcja=nowyBlend';
        }
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
            alert("Error loading blend list in http...php");
        });
        let elmnt = document.getElementById('gura');
        elmnt.scrollIntoView({block: 'start'});
    };
    $scope.pobierzListe();
    
    $scope.zapiszBlend = function (ev) {
        
        $scope.loaderZapis = true;
        
        var confirm = $mdDialog.confirm()
            .title('Save blend')
            .textContent('Confirm and save blend changes?')
            .ariaLabel('...')
            .targetEvent(ev)
            .ok('Yes')
            .cancel('No');
        
        $mdDialog.show(confirm).then(function(result){
        
            var formData = {
                'akcja' : 'zapiszBlend',
                'idBlendu' : $scope.idBlenduRequest,//$scope.lista.blend.id,//jak=0, to doda
                'idPiosenki1' : $scope.lista.blend.id_piosenki1,
                'idPiosenki2' : $scope.lista.blend.id_piosenki2,
                'opis' : $scope.lista.blend.opis,
                'publiczny' : $scope.lista.blend.publiczny
            };
            var response = $http({
                method: "POST",
                url: 'core/http_blendy.php',
                params: formData,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).then(function mySucces(response) {
                if(response.data.odp!=='OK') {
                    alert(response.data);
                    $scope.loaderZapis = false;
                }else{
                    if($scope.idBlenduRequest==='0'){
                        $window.location = "#!blend/"+response.data.idBlendu;
                    }else{                    
                        $scope.pobierzListe();
                        $scope.loaderZapis = false;
                        $scope.edycja = false;
                    }
                }
                


            }, function myError(response) {
                 alert("Error saving blend: "+response.StatusText);
            });
            
        }, function(){ //rezygnacja
            $scope.loaderZapis = false;
        });
    };
    
    $scope.cofnijZmiany = function (ev) {
        
        $scope.loaderCofnij = true;
        
        var confirm = $mdDialog.confirm()
            .title('Discard changes')
            .textContent('Discard all changes?')
            .ariaLabel('...')
            .targetEvent(ev)
            .ok('Yes')
            .cancel('No');
        
        $mdDialog.show(confirm).then(function(result){
            
            $scope.pobierzListe();
            $scope.loaderCofnij = false;
            //$scope.edycja = false;
            
        }, function(){ //rezygnacja
            $scope.loaderCofnij = false;
        });
    };
    
    $scope.zaladujPiosenki1 = function () {
            
        if($scope.model.szukajP1.length > 3){
            $scope.loaderPiosenki1 = true;
            var formData = {
                'akcja' : 'zaladujPiosenki',
                'szukaj' : $scope.model.szukajP1
            };
            var response = $http({
                method: "POST",
                url: 'core/http_blendy.php',
                params: formData,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).then(function mySucces(response) {
                if(response.data.odp!=='OK') {
                    alert(response.data);
                }else {
                    if(response.data.piosenki!==false){
                        $scope.model.piosenki1 = response.data.piosenki;
                    }else{
                        $scope.model.piosenki1 = [];
                    }
                }
                $scope.loaderPiosenki1 = false;

            }, function myError(response) {
                 alert("Error loading tracks to deck 1: "+response.StatusText);
                 $scope.loaderPiosenki1 = false;
            });
        }
    };
    
    $scope.zaladujPiosenki2 = function () {
            
        if($scope.model.szukajP2.length > 3){
            $scope.loaderPiosenki2 = true;
            var formData = {
                'akcja' : 'zaladujPiosenki',
                'szukaj' : $scope.model.szukajP2
            };
            var response = $http({
                method: "POST",
                url: 'core/http_blendy.php',
                params: formData,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).then(function mySucces(response) {
                if(response.data.odp!=='OK') {
                    alert(response.data);
                }else {
                    if(response.data.piosenki!==false){
                        $scope.model.piosenki2 = response.data.piosenki;
                    }else{
                        $scope.model.piosenki2 = [];
                    }
                }
                $scope.loaderPiosenki2 = false;

            }, function myError(response) {
                 alert("Error loading tracks to deck 2: "+response.StatusText);
                 $scope.loaderPiosenki2 = false;
            });
        }
    };
    
    $scope.aktualizujDeck1 = function () {
        angular.forEach($scope.model.piosenki1, function (value, key) {
            if (value.id===$scope.lista.blend.id_piosenki1) {
                
                $scope.lista.blend.artysta1=value.artysta;
                $scope.lista.blend.album1=value.album;
                $scope.lista.blend.piosenka1=value.piosenka;
                $scope.lista.blend.pozycja1=value.pozycja;
                $scope.lista.blend.discogs_id1=value.discogs_id;
            }
        });
    };
    $scope.aktualizujDeck2 = function () {
        angular.forEach($scope.model.piosenki2, function (value, key) {
            if (value.id===$scope.lista.blend.id_piosenki2) {
                
                $scope.lista.blend.artysta2=value.artysta;
                $scope.lista.blend.album2=value.album;
                $scope.lista.blend.piosenka2=value.piosenka;
                $scope.lista.blend.pozycja2=value.pozycja;
                $scope.lista.blend.discogs_id2=value.discogs_id;
            }
        });
    };
    
    $scope.model.szukajP1='';
    $scope.clearSearchTermP1 = function () {
        $scope.model.szukajP1='';
    };
    $scope.model.szukajP2='';
    $scope.clearSearchTermP2 = function () {
        $scope.model.szukajP2='';
    };
    $scope.updateSearch = function (e) {
        e.stopPropagation();
    };
    
    $scope.toggleEdycja = function () {
        $scope.edycja = !$scope.edycja;
    };
    
    $scope.togglePubliczny = function (ev) {
        if($scope.idBlenduRequest === '0'){
            if($scope.lista.blend.publiczny==='1'){
                $scope.lista.blend.publiczny='0';
            }else{
                $scope.lista.blend.publiczny='1';
            }
        }else{
        
            $scope.loaderPubl=true;
            
            var confirm = $mdDialog.confirm()
                .title('You are about to change visibility of this blend')
                .textContent('Are you sure?')
                .ariaLabel('...')
                .targetEvent(ev)
                .ok('Yes')
                .cancel('No');
            
            $mdDialog.show(confirm).then(function(result){
            
                var formData = {
                    'akcja' : 'togglePubliczny',
                    'idBlendu' : $scope.idBlenduRequest
                };
                var response = $http({
                    method: "POST",
                    url: 'core/http_blendy.php',
                    params: formData,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).then(function mySucces(response) {
                    if(response.data.odp!=='OK') {
                        alert(response.data);
                    }
                    $scope.pobierzListe();
                    $scope.loaderPubl=false;

                }, function myError(response) {
                    alert("error toggling visibility: "+response.StatusText);
                });
                
            }, function(){ //rezygnacja
                $scope.loaderDanych = false;
                $scope.loaderPubl=false;
            });
        }
    };

});
