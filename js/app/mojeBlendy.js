app.controller('mojeBlendyCtrl', function ($mdMedia, $scope, $http, $window, $mdDialog, $rootScope) {
    
    $scope.lista = [];
    $scope.loaderDanych = false;
    $scope.strony = [];//tu tablica stron - liczba stron z phpa w lista.strony
    $scope.szukej = '';
    $scope.szukejInput = '';
    $scope.szukajkaOtwarta = false;
    $scope.duze = false;

    $scope.pobierzListe = function (skrolAp = true) {

        $scope.loaderDanych = true;
        let odwrut = '';
        if($rootScope.bDesc){
            odwrut = '1';
        }
        $http.get('core/http_blendy.php?akcja=mojeBlendy&strona='+$rootScope.bStrona+'&sort='+$rootScope.bSort+'&desc='+odwrut+'&szukaj='+$scope.szukej).then(function (response) {
            //First function handles success
            if (response.data === "Sesja wygasła - proszę odświezyć stronę i zalogować się ponownie") {
                $window.location = "#!login";
            } else {
                if(response.data.odp !=='OK'){
                    alert(response.data);
                }
                $scope.lista = response.data;
                $scope.loaderDanych = false;
                $scope.uzupelnijStrony();
            }
        }, function (response) {
            //Second function handles error
            alert("error loading blends list in http request");
        });
        if(skrolAp){
            let elmnt = document.getElementById('gura');
            elmnt.scrollIntoView({block: 'start'});
        }
    };
    $scope.pobierzListe();
    
    $scope.uzupelnijStrony = function(){
        $scope.strony = [];
        $scope.strony.length = 0;
        let strona
        for(i=0;i<$scope.lista.strony;i++){
            strona = i+1;
            $scope.strony.push(strona);
        }
    };
    
    $scope.przejdzDoStrony = function (str) {
        $rootScope.bStrona = str;
        $scope.pobierzListe();
    };
    
    $scope.ustawSortowanie = function (sort) {
        $rootScope.bSort = sort;
        $scope.pobierzListe();
    };
    
    $scope.toggleDesc = function () {
        $rootScope.bDesc = !$rootScope.bDesc;
        $scope.pobierzListe();
    };
    $scope.poszukej = function () {
        if($scope.szukajkaOtwarta){
            $rootScope.bStrona = 1;
            $scope.szukej = $scope.szukejInput;
            $scope.pobierzListe();
        }else{
            $scope.szukajkaOtwarta = true;
        }
    };
    $scope.zamknijSzukajke = function () {
        $scope.szukajkaOtwarta = false;
        //jakby zifowac szukejInput, to mozna poszukac potem zmazac tekst z inputa i zostanie wynik wyszukiwania - taki maly bug, ale byloby plynniej
        if($scope.szukej!==''){
            $rootScope.bStrona = 1;
            $scope.szukej = '';
            $scope.szukejInput = '';
            $scope.pobierzListe();
        }
    };
    
    $scope.$watch(function () {
        return $mdMedia('gt-xs');
    }, function (size) {
        if (size) {
            $scope.duze = true;
        }else{
            $scope.duze = false;
        }
    });
    
    $scope.usunBlend = function (ev,id) {
        angular.forEach($scope.lista.blendy, function (value, key) {
            if (value.id===id) {
                value.loader=true;
            }
        });
        
        var confirm = $mdDialog.confirm()
            .title('You are about to remove a blend')
            .textContent('Are you sure?')
            .ariaLabel('...')
            .targetEvent(ev)
            .ok('Yes')
            .cancel('No');
        
        $mdDialog.show(confirm).then(function(result){
        
            var formData = {
                'akcja' : 'usunBlend',
                'idBlendu' : id
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
                $scope.pobierzListe(false);

            }, function myError(response) {
                 alert("error deleting blend: "+response.StatusText);
            });
            
        }, function(){ //rezygnacja
            $scope.loaderDanych = false;
            angular.forEach($scope.lista.blendy, function (value, key) {
                if (value.id===id) {
                    value.loader=false;
                }
            });
        });
    };
    
    $scope.togglePubliczny = function (ev,id) {
        angular.forEach($scope.lista.blendy, function (value, key) {
            if (value.id===id) {
                value.loaderPubl=true;
            }
        });
        
        var confirm = $mdDialog.confirm()
            .title('You are about to change visibility of a blend')
            .textContent('Are you sure?')
            .ariaLabel('...')
            .targetEvent(ev)
            .ok('Yes')
            .cancel('No');
        
        $mdDialog.show(confirm).then(function(result){
        
            var formData = {
                'akcja' : 'togglePubliczny',
                'idBlendu' : id
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
                $scope.pobierzListe(false);

            }, function myError(response) {
                 alert("error toggling visibility: "+response.StatusText);
            });
            
        }, function(){ //rezygnacja
            $scope.loaderDanych = false;
            angular.forEach($scope.lista.blendy, function (value, key) {
                if (value.id===id) {
                    value.loaderPubl=false;
                }
            });
        });
    };
}); 
