app.controller('mojaKolekcjaCtrl', function ($mdMedia, $scope, $rootScope, $http, $window, $mdDialog, $anchorScroll, $location) {
    
    $scope.lista = [];
    $scope.loaderDanych = false;
    $scope.strony = [];
    $scope.szukej = '';
    $scope.szukejInput = '';
    $scope.szukajkaOtwarta = false;
    $scope.duze = false;
    
    $scope.pobierzListe = function () {
        $scope.loaderDanych = true;
        let odwrut = '';
        if($rootScope.kDesc){
            odwrut = '1';
        }
        $http.get('core/http_kolekcja.php?akcja=myCollection&strona='+$rootScope.kStrona+'&sort='+$rootScope.kSort+'&desc='+odwrut+'&szukaj='+$scope.szukej).then(function (response) {
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
            alert("error loading collection in http request");
        });
        let elmnt = document.getElementById('gura');
        elmnt.scrollIntoView({block: 'start'});
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
        $rootScope.kStrona = str;
        $scope.pobierzListe();
        //$location.hash('gura');
        //$anchorScroll();

    };
    
    $scope.ustawSortowanie = function (sort) {
        $rootScope.kSort = sort;
        $scope.pobierzListe();
    };
    
    $scope.toggleDesc = function () {
        $rootScope.kDesc = !$rootScope.kDesc;
        $scope.pobierzListe();
    };
    $scope.poszukej = function () {
        if($scope.szukajkaOtwarta){
            $rootScope.kStrona = 1;
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
            $rootScope.kStrona = 1;
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

    $scope.usunAlbum = function (ev,id) {
        angular.forEach($scope.lista.albumy, function (value, key) {
            if (value.id===id) {
                value.loader=true;
            }
        });
        
        var confirm = $mdDialog.confirm()
            .title('You are about to remove a release from your collection')
            .textContent('Are you sure?')
            .ariaLabel('...')
            .targetEvent(ev)
            .ok('Yes')
            .cancel('No');
        
        $mdDialog.show(confirm).then(function(result){
        
            var formData = {
                'akcja' : 'usun',
                'idAlbumu' : id
            };
            var response = $http({
                method: "POST",
                url: 'core/http_kolekcja.php',
                params: formData,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).then(function mySucces(response) {
                if(response.data.odp!=='OK') {
                    alert(response.data);
                }
                $scope.pobierzListe();

            }, function myError(response) {
                 alert("error removing a release: "+response.StatusText);
            });
            
        }, function(){ //rezygnacja
            $scope.loaderDanych = false;
            angular.forEach($scope.lista.albumy, function (value, key) {
                if (value.id===id) {
                    value.loader=false;
                }
            });
        });
    };
    
}); 
