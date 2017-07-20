var URL = "http://localhost/angular_ci/api/index.php/";
app.controller('ItemController', function(dataFactory,$scope,$http){
  $scope.data = [];
  $scope.libraryTemp = {};
  $scope.totalItemsTemp = {};


    $scope.totalItems = 0;
  $scope.pageChanged = function(newPage) {
    getResultsPage(newPage);
  };

 getResultsPage(1);
  function getResultsPage(pageNumber) {
      if(! $.isEmptyObject($scope.libraryTemp)){
          dataFactory.httpRequest(URL + '/api/getData.php?search='+$scope.searchText+'&page='+pageNumber).then(function(data) {
            $scope.data = data.data;
            $scope.removeRow = function(title){        
                var index = -1;   
                var comArr = eval( $scope.data );
                for( var i = 0; i < comArr.length; i++ ) {
                  if( comArr[i].title === title ) {
                    index = i;
                    break;
                  }
                }
                if( index === -1 ) {
                  alert( "Something gone wrong" );
                }
                $scope.data.splice( index, 1 );    
              };
          
            $scope.totalItems = data.total;
          });
      }else{
        dataFactory.httpRequest(URL + 'Crud/get?page='+pageNumber).then(function(data) {
          $scope.data = data.data;
          $scope.totalItems = data.total;
        });
      }
  }
  $scope.saveAdd = function(){
    dataFactory.httpRequest(URL + 'Crud/add','POST',{},$scope.form).then(function(data) {
      $scope.data.push(data);
      $(".modal").modal("hide");
    });
  }

  $scope.edit = function(id){
    dataFactory.httpRequest(URL + '/api/edit.php?id='+id).then(function(data) {
    	console.log(data);
      	$scope.form = data;
    });
  }

  $scope.saveEdit = function(){
    dataFactory.httpRequest(URL + '/api/update.php?id='+$scope.form.id,'POST',{},$scope.form).then(function(data) {
      	$(".modal");
        $scope.data = apiModifyTable($scope.data,data.id,data);
    });
  }

  $scope.remove = function(item,index){
    var result = confirm("Are you sure delete this item?");
   	if (result) {
      dataFactory.httpRequest(URL + '/api/delete.php?id='+item.id,'DELETE').then(function(data) {
          $scope.data.splice(index,1);
      });
    }
  }
   
});