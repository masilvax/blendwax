app.controller('setCtrl', function ($scope, $http, $window, $routeParams, $mdDialog) {
    
    $scope.idSetuRequest = $routeParams.idSetu;
    $scope.edycja = false;
    $scope.edycjaUkryj = false;
    if($scope.idSetuRequest === 'new'){
        $scope.idSetuRequest = '0';
        $scope.edycja = true;
        $scope.edycjaUkryj = true;
    }
    $scope.lista = [];
    $scope.loaderDanych = false;
    $scope.loaderPubl = false;
    $scope.loaderZapis = false;
    $scope.loaderCofnij = false;
    $scope.loaderDodawanejPiosenki = false;
    $scope.loaderSzukanejPiosenki = false;
    $scope.model = [];
        $scope.model.dodanePiosenki = [];
        $scope.model.szukanePiosenki = [];
        $scope.model.blendPiosenki = [];
        $scope.model.dodawanaPiosenka = [];

    $scope.pobierzListe = function () {
        $scope.loaderDanych = true;
        let url = 'core/http_sety.php?akcja=set&idSetu='+$scope.idSetuRequest+'&ts='+Date.now();
        if($scope.idSetuRequest==='0'){
            url = 'core/http_sety.php?akcja=nowySet';
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
            alert("Error loading set in http...php");
        });
        let elmnt = document.getElementById('gura');
        elmnt.scrollIntoView({block: 'start'});
    };
    $scope.pobierzListe();
    
    $scope.pobierzBlendPiosenki = function () {//piosenki pasujace do ostatniej w secie
            
        $scope.loaderSzukanejPiosenki = true;
        //console.log($scope.lista.piosenki[$scope.lista.piosenki.length -1].id);
        
        let idOstatniejPiosenkiWSecie = 0;
        angular.forEach($scope.lista.piosenki, function (v,k) {
            if(v.kolejnosc===$scope.lista.piosenki.length){
                idOstatniejPiosenkiWSecie = v.id;
            }
        });
        
        var formData = {
            'akcja' : 'blendPiosenki',
            'idPiosenki' : idOstatniejPiosenkiWSecie//id ostatniej piosenki w secie
        };
        var response = $http({
            method: "POST",
            url: 'core/http_sety.php',
            params: formData,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function mySucces(response) {
            if(response.data.odp!=='OK') {
                alert(response.data);
            }else {
                if(response.data.piosenki!==false){
                    $scope.model.blendPiosenki = response.data.piosenki;
                }else{
                    $scope.model.blendPiosenki = [];
                }
            }
            $scope.loaderSzukanejPiosenki = false;

        }, function myError(response) {
            alert("Error searching blend track: "+response.StatusText);
            $scope.loaderSzukanejPiosenki = false;
        });
    };
    
    
    $scope.zapiszSet = function (ev) {
        
        $scope.loaderZapis = true;
        
        var confirm = $mdDialog.confirm()
            .title('Save set')
            .textContent('Confirm and save set changes?')
            .ariaLabel('...')
            .targetEvent(ev)
            .ok('Yes')
            .cancel('No');
        
        $mdDialog.show(confirm).then(function(result){
        
            var formData = {
                'akcja' : 'zapiszSet',
                'idSetu' : $scope.idSetuRequest,//$scope.lista.blend.id,//jak=0, to doda
                'nazwa' : $scope.lista.set.nazwa,
                'publiczny' : $scope.lista.set.publiczny,
                'link' : $scope.lista.set.link,
                'styl' : $scope.lista.set.styl,
                'piosenki' : angular.toJson($scope.lista.piosenki)
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
                if($scope.idSetuRequest==='0' && response.data.odp==='OK'){
                    $window.location = "#!set/"+response.data.idSetu;
                }else{
                    $scope.pobierzListe();
                    $scope.loaderZapis = false;
                    if($scope.idSetuRequest!=='0'){
                        $scope.edycja = false;
                    }
                }

            }, function myError(response) {
                 alert("Error saving set: "+response.StatusText);
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
    
    $scope.szukajPiosenki = function () {
            
        if($scope.model.szukajString.length > 3){
            $scope.loaderSzukanejPiosenki = true;
            var formData = {
                'akcja' : 'zaladujPiosenki',
                'szukaj' : $scope.model.szukajString
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
                        $scope.model.szukanePiosenki = response.data.piosenki;
                    }else{
                        $scope.model.szukanePiosenki = [];
                    }
                }
                $scope.loaderSzukanejPiosenki = false;

            }, function myError(response) {
                 alert("Error searching track: "+response.StatusText);
                 $scope.loaderSzukanejPiosenki = false;
            });
        }
    };
    
    $scope.dodajPiosenkeDoSeta = function () {
        //console.log($id.scrollHeight);
        if($scope.model.dodawanaPiosenka.id>0){//zrobilbym length, ale nie wiedziec czemu jak jest cos w tej tablicy to jest undefined
            $scope.model.dodawanaPiosenka.kolejnosc = $scope.lista.piosenki.length+1;
            $scope.lista.piosenki.push(angular.copy($scope.model.dodawanaPiosenka));//copy, bo bindowanie w obie strony robi niezle siupy
            $scope.model.dodawanaPiosenka = [];
            $scope.model.dodawanaPiosenka.length = 0;
            
            $scope.model.szukanePiosenki = [];
            $scope.model.szukanePiosenki.length = 0;
            
            $scope.pobierzBlendPiosenki();
        }
        
        setTimeout(function(){ 
            //console.log("Hello");
            var bottom = document.getElementById("bottom");
            bottom.scrollTop = bottom.scrollHeight;
        }, 500);
       
    };
    
    $scope.wybierzDodawanaPiosenke = function () {
        angular.forEach($scope.model.szukanePiosenki, function (value, key) {
            if (value.id===$scope.model.dodawanaPiosenka.id) {//bo dodawanaPiosenka.id jest w ng-model tego selecta
                //console.log(value.id+":"+$scope.model.dodawanaPiosenka.id);
                $scope.model.dodawanaPiosenka = angular.copy(value);
            }
        });
        angular.forEach($scope.model.blendPiosenki, function (value, key) {
            if (value.id===$scope.model.dodawanaPiosenka.id) {//bo dodawanaPiosenka.id jest w ng-model tego selecta
                //console.log(value.id+":"+$scope.model.dodawanaPiosenka.id);
                $scope.model.dodawanaPiosenka = angular.copy(value);
            }
        });
        //console.log($scope.model.dodawanaPiosenka.id);
    };
    
    $scope.przesunWGore = function(kolejnosc){//dwa foricze, bo sortowanie niekoniecznie musi byc po kolejnosci
        if(kolejnosc > 1){
            let klikany = kolejnosc;
            let poprzedni = kolejnosc - 1;
            let nastepny = kolejnosc + 1;//potrzebne tylko do wyczyszczenia opisu
            let klikanyTemp = -1;
            let poprzedniTemp = -2;
            
            angular.forEach($scope.lista.piosenki, function (v,k) {
                if(v.kolejnosc === poprzedni){
                    v.kolejnosc = klikanyTemp;
                    v.opis = '';
                }
                if(v.kolejnosc === klikany){
                    v.kolejnosc = poprzedniTemp;
                    v.opis = '';
                }
                if(v.kolejnosc === nastepny){
                    v.opis = '';
                }
            });
            angular.forEach($scope.lista.piosenki, function (v,k) {
                if(v.kolejnosc === poprzedniTemp){
                    v.kolejnosc = poprzedni;
                }
                if(v.kolejnosc === klikanyTemp){
                    v.kolejnosc = klikany;
                }
            });
        
            $scope.pobierzBlendPiosenki();
        }
    };
    
    $scope.przesunWDol = function(kolejnosc){//dwa foricze, bo sortowanie niekoniecznie musi byc po kolejnosci
        if(kolejnosc < $scope.lista.piosenki.length){
            let klikany = kolejnosc;
            let nastepny = kolejnosc + 1;
            let nastepniejszy = kolejnosc + 2;//tylko do wyczyszczenia opisu
            let klikanyTemp = -1;
            let nastepnyTemp = -2;
            
            angular.forEach($scope.lista.piosenki, function (v,k) {
                if(v.kolejnosc === nastepny){
                    v.kolejnosc = klikanyTemp;
                    v.opis = '';
                }
                if(v.kolejnosc === klikany){
                    v.kolejnosc = nastepnyTemp;
                    v.opis = '';
                }
                if(v.kolejnosc === nastepniejszy){
                    v.opis = '';
                }
            });
            angular.forEach($scope.lista.piosenki, function (v,k) {
                if(v.kolejnosc === nastepnyTemp){
                    v.kolejnosc = nastepny;
                }
                if(v.kolejnosc === klikanyTemp){
                    v.kolejnosc = klikany;
                }
            });
        
            $scope.pobierzBlendPiosenki();
        }
    };
    
    $scope.usunPiosnke = function (kolejnosc) {//trzy foricze, bo niekoniecznie musza byc po kolei (np. po zmianie kolejnosci), a trzeba najpierw ustalic od ktorego miejsca zaczynamy zmniejszac piosenkom kolejnosc. i trzeci foricz bo wydaje się, że przy usuwaniu omija następną iterację nie wiedzieć czemu
        let nastepny = kolejnosc + 1;
        //console.log('temu mam wyczyscic opis:'+nastepny);
        angular.forEach($scope.lista.piosenki, function (value, key) {

            //console.log('foricz czystek:'+value.kolejnosc);
            
            if(value.kolejnosc === nastepny) {
                value.opis = '';
                //console.log('czyscze opis:'+nastepny);
            }

        });
        
        angular.forEach($scope.lista.piosenki, function (value, key) {//przy usuwaniu omija natępną iterację

            //console.log('foricz usuwajek:'+value.kolejnosc);

            if (value.kolejnosc === kolejnosc) {
                $scope.lista.piosenki.splice(key, 1);
                //console.log('usuwam:'+value.kolejnosc);
            }

        });
        
        angular.forEach($scope.lista.piosenki, function (value, key) {
            if (value.kolejnosc >= kolejnosc) {
                //console.log('zmieniam kolejnosc przed:'+value.kolejnosc);
                value.kolejnosc = value.kolejnosc -1;
                //console.log('zmieniam kolejnosc po:'+value.kolejnosc);
            }
        });
        
        $scope.pobierzBlendPiosenki();
    };
    
    $scope.model.szukajString='';
    $scope.clearSearchTerm = function () {
        $scope.model.szukajString='';
    };

    $scope.updateSearch = function (e) {
        e.stopPropagation();
    };
    
    $scope.toggleEdycja = function () {
        $scope.edycja = !$scope.edycja;
        if($scope.edycja && $scope.lista.piosenki.length>0){
            $scope.pobierzBlendPiosenki();
        }
    };
    
    $scope.wyczyscOpis = function (kolejnosc) {
        angular.forEach($scope.lista.piosenki, function (value, key) {
            if (value.kolejnosc === kolejnosc) {
                value.opis = '';
            }
        });
    };
    
    $scope.togglePubliczny = function (ev) {
        if($scope.idSetuRequest === '0'){
            if($scope.lista.set.publiczny==='1'){
                $scope.lista.set.publiczny='0';
            }else{
                $scope.lista.set.publiczny='1';
            }
        }else{
        
            $scope.loaderPubl=true;
            
            var confirm = $mdDialog.confirm()
                .title('You are about to change visibility of this set')
                .textContent('Are you sure?')
                .ariaLabel('...')
                .targetEvent(ev)
                .ok('Yes')
                .cancel('No');
            
            $mdDialog.show(confirm).then(function(result){
            
                var formData = {
                    'akcja' : 'togglePubliczny',
                    'idSetu' : $scope.idSetuRequest
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
