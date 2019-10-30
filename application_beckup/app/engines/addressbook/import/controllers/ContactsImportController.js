angular.module('callburnApp').controller('ContactsImportController', 
	[ 		'$scope', '$rootScope', '$state', 'Restangular', '$stateParams', 'FileUploader', '$timeout', 'groups', 'notify',
	function($scope,   $rootScope,   $state,   Restangular,   $stateParams,   FileUploader,   $timeout,   groups,   notify){


	$scope.goToNotification = $rootScope.goToNotification;
	$rootScope.currentPage = 'dashboard';
	$rootScope.currentActiveRoute = 'addressbook';
	$scope.showErrorsFilter = false;
	$scope.import_step = 1;
	$scope.responsePhonenumbers = [];

	$scope.addedNumbers = '';
	$scope.page = 0;
	$scope.pagesCount = 0;

	$scope.listingSkip = 0;
	$scope.uploadingImageName = '';
	$scope.selectedGroup = {};
	$scope.groups = groups.resource.groups;

	$rootScope.previousStep = function(){
		$scope.import_step = 1;
	}
	$rootScope.nextStep = function(){
		if($scope.responsePhonenumbers.length == 0){
          	notify({message: 'There is no phonenumber', classes: ['notification-alert-danger']})
			return;
		}
		$scope.import_step = 2;
	}

	$rootScope.footerData = {
		first:  '<span>Step 1</span>' + 
				'<span>Upload a batch file using given format</span>',
		second: '<span>Step 2</span>' + 
				'<span>Review batch file data</span>'
	}
	
	$scope.$watch('import_step', function(newVal, oldVal){
		if(newVal == 1){
			$rootScope.isFooter1Active = true;
			$rootScope.isFooter2Active = false;

			$rootScope.showPreviousIcon = false;
			$rootScope.showNextIcon = true;
		}
		if(newVal == 2){
			$rootScope.isFooter1Active = false;
			$rootScope.isFooter2Active = true;

			$rootScope.showPreviousIcon = true;
			$rootScope.showNextIcon = false;
		}
	});

	$scope.checkedContacts = {};
	$scope.isAllChecked = false;

	$scope.checkedUncheckContact = function(number)
	{
		$scope.checkedContacts[number] = $scope.checkedContacts[number] ? !$scope.checkedContacts[number] : true; 
	}

	$scope.checkUncheckAll = function(){
		$scope.isAllChecked = !$scope.isAllChecked;
		for(index in $scope.responsePhonenumbers){
			$scope.checkedContacts[$scope.responsePhonenumbers[index].number] = $scope.isAllChecked;
		}
	}

	$scope.changePage = function(pageNumber){
		if(pageNumber < 0 || pageNumber > $scope.pagesCount - 1){
			return;
		}
		$scope.page = pageNumber;
		$scope.listingSkip = $scope.page * 10;
	}

	$scope.changeName = function(number, name){
		for(index in $scope.responsePhonenumbers){
			if($scope.responsePhonenumbers[index].number == number){
				$scope.responsePhonenumbers[index].name = name;
			}
		}
	}

	$scope.addGroup = function(number){
		if(!$scope.selectedGroup[number]){
			return;
		}
		var group = JSON.parse( $scope.selectedGroup[number] );
		for(index in $scope.responsePhonenumbers){
			if($scope.responsePhonenumbers[index].number == number){
				$scope.responsePhonenumbers[index].groups.push({_id: group._id, name: group.name});
			}
		}
	}

	$scope.removeGroup = function(number, position){
		for(index in $scope.responsePhonenumbers){
			if($scope.responsePhonenumbers[index].number == number){
				$scope.responsePhonenumbers[index].groups.splice(position, 1);
			}
		}
	}

	$scope.addNumbers = function(){
		var postData = {phonenumbers: $scope.addedNumbers};
		$rootScope.startLoader();
		Restangular.all('phonenumbers/add-phonenumbers').post(postData).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$scope.numbersResponseData = data.resource;
				$scope.responsePhonenumbers = data.resource.phonenumbers;
				addPlaceForGroup();
				$scope.import_step = 2;
				$scope.pagesCount = Math.ceil(data.resource.count/10);
			}
		})
	}

	$scope.startUpload = function(){
		$rootScope.startLoader();
		numbersFileUpload.uploadAll();
	}

	$scope.saveImportedNumbers = function(){
		var newArray = new Array();
		var actual = $scope.checkedContacts;
		for (actIndex in actual) {
			if (actual[actIndex]) {
				newArray.push(actIndex);
			}
		}
		var sendingArrayData = [];
		if(newArray.length > 0){
			for(index in newArray){
				for(tempIndex in $scope.responsePhonenumbers){
					if($scope.responsePhonenumbers[tempIndex].number == newArray[index]){
						sendingArrayData.push($scope.responsePhonenumbers[tempIndex]);
					}
				}
			}
		} else{
			sendingArrayData = $scope.responsePhonenumbers;
		}

		Restangular.all('address-book/import-contacts').post({'contacts_data': sendingArrayData}).then(function(data){
			if(data.resource.error.no == 0){
				$state.go('addressbook.contacts');
			}
		})
	}

	var numbersFileUpload = $scope.numbersFileUpload = new FileUploader({
	    url: 'phonenumbers/upload-phonenumbers',
	    alias : 'file',
	    autoUpload : true
	});

	numbersFileUpload.onAfterAddingFile = function(item){
		$scope.uploadingImageName = item.file.name;
	}

	numbersFileUpload.onSuccessItem = function(item, data, status, headers){
		$rootScope.stopLoader();
		if(data.resource.error.no == 0){
			$scope.uploadingImageName = false;
			$scope.numbersResponseData = data.resource;
			$scope.responsePhonenumbers = data.resource.phonenumbers;
			$scope.import_step = 2;
			$scope.pagesCount = Math.ceil(data.resource.count/10);
			addPlaceForGroup(true);
		} else{
			notify({message: data.resource.error.text, classes: ['notification-alert-danger']})
		}
	};

	numbersFileUpload.onErrorItem = function(item, response, status, headers){
		$rootScope.stopLoader();
		$scope.uploadingImageName = false;
	}

	$scope.openFileSelect = function(){
		$timeout(function() {
		    angular.element('#hiddenNumbersFileInput').trigger('click');
		}, 100);
	}

	var addPlaceForGroup = function(isNeed){
		for(index in $scope.responsePhonenumbers){
			$scope.responsePhonenumbers[index].groups = [];
			if($scope.responsePhonenumbers[index].group && isNeed){
				$scope.responsePhonenumbers[index].groups.push($scope.responsePhonenumbers[index].group);
			}
		}
	}

}]).filter('notSelected', function() {
  return function(input, scope, number) {
  	for(index in scope.responsePhonenumbers){
		if(scope.responsePhonenumbers[index].number == number){
			var groups = scope.responsePhonenumbers[index].groups
		}
	}
    var out = [];
  	angular.forEach(input, function(group) {
  		var needToAdd = true;
  		for(index in groups){
  			if(groups[index].id == group._id){
      			needToAdd = false;
      			break;
  			}
  		}
  		if(needToAdd){
  			out.push(group);
  		}
    })
    return out;
  };
}).filter('showErrors', function() {
  return function(input, scope) {
  	if(!scope.showErrorsFilter){
  		scope.pagesCount = Math.ceil(input.length/10);
  		return input;
  	}
    var out = [];
  	angular.forEach(input, function(number) {
  		if(number.status != 'success'){
  			out.push(number);
  		}
    })
    scope.pagesCount = Math.ceil(out.length/10);
    return out;
  };
});