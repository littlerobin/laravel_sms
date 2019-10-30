angular.module('callburnApp').controller('ContactsController', 
	[ 		'$scope', '$rootScope', '$state', 'contacts', 'groups', 'notify', 'Restangular', '$stateParams',
	function($scope,   $rootScope,   $state,   contacts ,  groups,   notify,   Restangular,   $stateParams){
 
	$scope.showInput = [];

	$scope.goToNotification = $rootScope.goToNotification;
	$scope.groups = groups.resource.groups;
	$rootScope.currentActiveRoute = 'addressbook';
	$scope.groupData = {};
	$scope.attachContactsData = {};
	$scope.showAddToGroupModal = false;

	$rootScope.footerData = {
		first:  '<span>Today you have added</span>' + 
				'<span>' + $rootScope.otherFooterData.today_added_contacts + ' contacts</span>',
		second: '<span>You have a total of</span>' + 
				'<span>' + $rootScope.otherFooterData.total_contacts + ' contacts</span>',
		third:  '<span>Your Favorite Countries are</span>' + 
				'<span>' + $rootScope.otherFooterData.facorite_countries + '</span>'
	}

	$rootScope.currentPage = 'dashboard';
	var updateContacts = function(contacts){
		$scope.contacts = contacts.resource.contacts;
		$scope.contactsPage = contacts.resource.page;
		$scope.pagesCount = Math.ceil(contacts.resource.count/10);
	}
	updateContacts(contacts);

	$scope.filterOrAddData = {};

	

	$scope.checkedContacts = {};
	$scope.isAllChecked = false;

	$scope.checkedUncheckContact = function(contactId, event)
	{
		$scope.checkedContacts[contactId] = $scope.checkedContacts[contactId] ? !$scope.checkedContacts[contactId] : true; 
	}

	$scope.checkUncheckAll = function(){
		$scope.isAllChecked = !$scope.isAllChecked;
		for(index in $scope.contacts){
			$scope.checkedContacts[$scope.contacts[index]._id] = $scope.isAllChecked;
		}
	}

	$scope.filterContacts = function(){
		Restangular.one('address-book/index-contacts').get($scope.filterOrAddData).then(function(data){
			updateContacts(data);
		});
	}

	$scope.changePage = function(page){
		if(page < 0 || page > $scope.pagesCount - 1){
			return;
		}
		var postData = $scope.filterOrAddData;
		postData.page = page;
		Restangular.one('address-book/index-contacts').get(postData).then(function(data){
			updateContacts(data);
		});
	}

	$scope.addContact = function(){
		Restangular.all('address-book/create-contact').post($scope.filterOrAddData).then(function(data){
			$scope.filterOrAddData = {};
			$scope.reloadContacts();
		})
	}

	var reloadGroups = function(){
		Restangular.one('address-book/index-groups').get().then(function(data){
			$scope.groups = data.resource.groups;
		})
	}

	$scope.addGroup = function(){
		if(!$scope.groupData.name){
          	notify({message: 'Please specify the name', classes: ['notification-alert-danger']})
			return;
		}
		Restangular.all('address-book/create-group').post($scope.groupData).then(function(data){
			reloadGroups();
		})
	}

	$scope.attachToGroup = function(){
		var postData = $scope.attachContactsData;
		postData.contact_ids = $scope.checkedContacts;
		Restangular.all('address-book/attach-contacts-to-group').post(postData).then(function(data){
			if(data.resource.error.no == 0){
				$scope.reloadContacts();
				$scope.showAddToGroupModal = false;
			}
		})
	}

	$scope.reloadContacts = function(){
		Restangular.one('address-book/index-contacts').get().then(function(data){
			updateContacts(data);
		})
	}

	$scope.changeName = function(id, name)
	{
		Restangular.one('address-book/update-contact', id).put({'name' : name}).then(function(data){
			if(data.resource.error.no == 0){
				for(index in $scope.contacts){
					if($scope.contacts[index]._id == id){
						$scope.contacts[index].name = name;
						$scope.showInput[id] = false;
						break;
					}
				}
			}
		})
	}

	$scope.sendMessage = function(){
		if(isContactsEmpty()){
			alert('No Contact Selected');
			return;
		}
		$state.go('campaign.compose-from-contacts', {contact_ids: JSON.stringify($scope.checkedContacts)});
	}

	$scope.openAddToGroupModal = function(){
		if(isContactsEmpty()){
			alert('No Contact Selected');
			return;
		}
		$scope.showAddToGroupModal = true;
	}

	$scope.removeContacts = function(paramData)
	{
		var postData = paramData ? paramData: JSON.stringify($scope.checkedContacts)
		Restangular.all('address-book/remove-contacts').remove({contact_ids: postData}).then(function(data){
			if(data.resource.error.no == 0){
				$scope.changePage($scope.contactsPage - 1);
				$scope.checkedContacts = {};
			} else{

			}
		})
	}

	$scope.removeOneContact = function(contactId){
		var paramData = {};
		paramData[contactId] = true;
		$scope.removeContacts(paramData);
	}

	var isContactsEmpty = function(){
		for(index in $scope.checkedContacts){
			if($scope.checkedContacts[index] == true){
				return false;
			}
		}
		return true;
	}
}]);