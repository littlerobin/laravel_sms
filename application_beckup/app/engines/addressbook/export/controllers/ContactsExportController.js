angular.module('callburnApp').controller('ContactsExportController', 
	[ 		'$scope', '$rootScope', '$state', 'groups', 'contacts', 'Restangular',
	function($scope,   $rootScope,   $state,   groups ,  contacts,   Restangular){

	$scope.goToNotification = $rootScope.goToNotification;
	$scope.activeTab = 'contact';
	$rootScope.currentActiveRoute = 'addressbook';
	$scope.searchData = {};

	$rootScope.footerData = {
		first:  '<span>Today you have added</span>' + 
				'<span>' + $rootScope.otherFooterData.today_added_groups + ' groups</span>',
		second: '<span>You have a total of</span>' + 
				'<span>' + $rootScope.otherFooterData.total_groups + ' groups</span>',
		third:  '<span>Your biggest group is</span>' + 
				'<span>' + $rootScope.otherFooterData.biggest_group + '</span>'
	}

	/**************** EXPORT FROM GROUPS ******/
	var updateGroups = function(groups){
		$scope.groups = groups.resource.groups;
		$scope.groupsPage = groups.resource.page;
		$scope.groupsPagesCount = ( Math.ceil(groups.resource.count/10) > 0 ) ? Math.ceil(groups.resource.count/10) : 1 ;
	}
	updateGroups(groups);

	$scope.checkedGroups = {};
	$scope.isAllGroupsChecked = false;

	$scope.checkedUncheckGroup = function(groupId)
	{
		$scope.checkedGroups[groupId] = $scope.checkedGroups[groupId] ? !$scope.checkedGroups[groupId] : true; 
	}

	$scope.checkUncheckAllGroups = function(){
		$scope.isAllGroupsChecked = !$scope.isAllGroupsChecked;
		for(index in $scope.groups){
			$scope.checkedGroups[$scope.groups[index]._id] = $scope.isAllGroupsChecked;
		}
	}

	$scope.changeGroupsPage = function(page){
		if(page < 0 || page > $scope.groupsPagesCount - 1){
			return;
		}
		var postData = {};
		postData.page = page;
		postData.name = $scope.searchData.keyword;
		Restangular.one('address-book/index-groups').get(postData).then(function(data){
			updateGroups(data);
		});
	}

	/**************** EXPORT FROM Contacts ******/
	var updateContacts = function(contacts){
		$scope.contacts = contacts.resource.contacts;
		$scope.contactsPage = contacts.resource.page;
		$scope.contactsPagesCount = ( Math.ceil(contacts.resource.count/10) > 0 ) ? Math.ceil(contacts.resource.count/10) : 1 ;
	}
	updateContacts(contacts);

	$scope.checkedContacts = {};
	$scope.isAllContactsChecked = false;

	$scope.checkedUncheckContact = function(contactId, event)
	{
		$scope.checkedContacts[contactId] = $scope.checkedContacts[contactId] ? !$scope.checkedContacts[contactId] : true; 
	}

	$scope.checkUncheckAllContacts = function(){
		$scope.isAllContactsChecked = !$scope.isAllContactsChecked;
		for(index in $scope.contacts){
			$scope.checkedContacts[$scope.contacts[index]._id] = $scope.isAllContactsChecked;
		}
	}

	$scope.changeContactsPage = function(page){
		if(page < 0 || page > $scope.contactsPagesCount - 1){
			return;
		}
		var postData = {};
		postData.page = page;
		postData.phone_number = $scope.searchData.keyword;
		Restangular.one('address-book/index-contacts').get(postData).then(function(data){
			updateContacts(data);
		});
	}

	$scope.filterData = function(){
		Restangular.one('address-book/index-contacts').get({phone_number: $scope.searchData.keyword}).then(function(data){
			updateContacts(data);
		});
		Restangular.one('address-book/index-groups').get({name: $scope.searchData.keyword}).then(function(data){
			updateGroups(data);
		});
	}

	$scope.getExportUrl = function(){
		var selectedIds = $scope.activeTab == 'group' ? $scope.checkedGroups : $scope.checkedContacts;
		selectedIds = JSON.stringify(selectedIds);

		var url = '?' + 'group_or_contact=' + $scope.activeTab + '&selected_ids=' + selectedIds;
		if($scope.searchData.keyword){
			url += '&keyword=' + $scope.searchData.keyword;
		}
		return url;
	}
}])