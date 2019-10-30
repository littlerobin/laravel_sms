angular.module('callburnApp').controller('GroupsController', 
	[ 		'$scope', '$rootScope', '$state', 'groups', 'Restangular', '$stateParams', 'notify',
	function($scope,   $rootScope,   $state,   groups ,  Restangular,   $stateParams,   notify){


	$scope.goToNotification = $rootScope.goToNotification;
	$rootScope.currentPage = 'dashboard';
	$rootScope.currentActiveRoute = 'addressbook';
	$scope.filterOrAddData = {};

	$scope.showInput = [];

	$rootScope.footerData = {
		first:  '<span>Today you have added</span>' + 
				'<span>' + $rootScope.otherFooterData.today_added_groups + 'groups</span>',
		second: '<span>You have a total of</span>' + 
				'<span>' + $rootScope.otherFooterData.total_groups + ' groups</span>',
		third:  '<span>Your biggest group is</span>' + 
				'<span>' + $rootScope.otherFooterData.biggest_group + '</span>'
	}

	var updateGroups = function(groups){
		$scope.groups = groups.resource.groups;
		$scope.groupsPage = groups.resource.page;
		$scope.pagesCount = Math.ceil(groups.resource.count/10);
	}
	updateGroups(groups);

	$scope.checkedGroups = {};
	$scope.isAllChecked = false;

	$scope.checkedUncheckGroup = function(groupId)
	{
		$scope.checkedGroups[groupId] = $scope.checkedGroups[groupId] ? !$scope.checkedGroups[groupId] : true; 
	}

	$scope.checkUncheckAll = function(){
		$scope.isAllChecked = !$scope.isAllChecked;
		for(index in $scope.groups){
			$scope.checkedGroups[$scope.groups[index]._id] = $scope.isAllChecked;
		}
	}

	$scope.filterGroups = function(){
		Restangular.one('address-book/index-groups').get($scope.filterOrAddData).then(function(data){
			updateGroups(data);
			$scope.checkedGroups = {};
		});
	}

	$scope.changePage = function(page){
		if(page < 0 || page > $scope.pagesCount - 1){
			return;
		}
		var postData = $scope.filterOrAddData;
		postData.page = page;
		Restangular.one('address-book/index-groups').get(postData).then(function(data){
			updateGroups(data);
			$scope.checkedGroups = {};
		});
	}

	$scope.addGroup = function(){
		if(!$scope.filterOrAddData.name){
          	notify({message: 'Please specify the name', classes: ['notification-alert-danger']})
			return;
		}
		Restangular.all('address-book/create-group').post($scope.filterOrAddData).then(function(data){
			$scope.reloadGroups();
			$scope.filterOrAddData = {};
		})
	}

	$scope.reloadGroups = function(){
		Restangular.one('address-book/index-groups').get().then(function(data){
			updateGroups(data);
		})
	}

	$scope.mergeGroups = function(){
		if(isGroupsEmpty()){
			alert('No group selected');
			return;
		}
		var postData = {'name': 'New name', 'ids': $scope.checkedGroups};
		Restangular.all('address-book/merge-groups').post(postData).then(function(data){
			$scope.reloadGroups();
		})
	}

	$scope.changeName = function(id, name)
	{
		Restangular.one('address-book/update-group', id).put({'name' : name}).then(function(data){
			if(data.resource.error.no == 0){
				for(index in $scope.groups){
					if($scope.groups[index]._id == id){
						$scope.groups[index].name = name;
						$scope.showInput[id] = false;
						break;
					}
				}
			}
		})
	}

	$scope.sendMessage = function(){
		if(isGroupsEmpty()){
			alert('No group selected');
			return;
		}
		$state.go('campaign.compose-from-groups', {group_ids: JSON.stringify($scope.checkedGroups)});
	}

	$scope.removeGroups = function(paramData)
	{
		var postData = paramData ? paramData : JSON.stringify($scope.checkedGroups)
		Restangular.all('address-book/remove-groups').remove({group_ids: postData}).then(function(data){
			if(data.resource.error.no == 0){
				$scope.changePage($scope.groupsPage - 1);
			} else{

			}
		})
	}

	$scope.removeOneGroup = function(groupId){
		var paramData = {};
		paramData[groupId] = true;
		$scope.removeGroups(paramData);
	}

	/**
	 * MANGE CONTACTS FOR THE GROUP
	 */
	$scope.showManageContactsModal = false;
	$scope.groupContacts = [];
	$scope.currentGroup = {};
	$scope.manageContacts = function(group){
		$scope.currentGroup = group;
		Restangular.one('address-book/index-contacts').get({group_id: group._id}).then(function(data){
 			updateContacts(data);
 			$scope.showManageContactsModal = true;
 		});
	}


	var updateContacts = function(contacts){
 		$scope.groupContacts = contacts.resource.contacts;
 		$scope.contactsPage = contacts.resource.page;
 		$scope.contactsPagesCount = Math.ceil(contacts.resource.count/10);
 	}

	$scope.filterOrAddContactData = {};


	$scope.filterContacts = function(){
		var postData = $scope.filterOrAddContactData;
		postData.group_id = $scope.currentGroup._id; 
 		Restangular.one('address-book/index-contacts').get(postData).then(function(data){
 			updateContacts(data);
 		});
 	}

 	$scope.changeContactsPage = function(page){
 		if(page < 0 || page > $scope.contactsPagesCount - 1){
 			return;
 		}
 		var postData = $scope.filterOrAddContactData;
 		postData.page = page;
 		postData.group_id = $scope.currentGroup._id;
 		Restangular.one('address-book/index-contacts').get(postData).then(function(data){
 			updateContacts(data);
 		});
 	}

 	$scope.addContact = function(){
		Restangular.all('address-book/create-contact').post($scope.filterOrAddContactData).then(function(data){
			if(data.resource.error.no == 0){
				$scope.filterOrAddContactData = {};
				var contactIds = {};
				contactIds[data.resource.contact._id] = true;
				var postData = {
					group_id: $scope.currentGroup._id,
					contact_ids: contactIds
				}
				Restangular.all('address-book/attach-contacts-to-group').post(postData).then(function(data1){
					if(data1.resource.error.no == 0){
						Restangular.one('address-book/index-contacts').get({group_id: $scope.currentGroup._id}).then(function(data2){
				 			updateContacts(data2);
				 		});
					}
				})
			}
		})
	}

	$scope.detachContact = function(contactId){
		var contactIds = {};
		contactIds[contactId] = true;
		var postData = {
			group_id: $scope.currentGroup._id,
			contact_ids: contactIds
		}
		Restangular.all('address-book/detach-contacts-from-group').post(postData).then(function(data1){
			if(data1.resource.error.no == 0){
				Restangular.one('address-book/index-contacts').get({group_id: $scope.currentGroup._id}).then(function(data2){
		 			updateContacts(data2);
		 		});
			}
		})
	}

	var isGroupsEmpty = function(){
		for(index in $scope.checkedGroups){
			if($scope.checkedGroups[index] == true){
				return false;
			}
		}
		return true;
	}


}]);