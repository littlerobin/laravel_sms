angular.module('callburnApp').controller('ComposeStep2Controller', 
	[ 		'$scope', '$rootScope', 'Restangular', 'notify','FileUploader', '$stateParams', 'CampaignComposeService',
	function($scope,   $rootScope,   Restangular,   notify,  FileUploader,   $stateParams,   CampaignComposeService){

		$scope.CampaignComposeService = CampaignComposeService;
		console.debug($stateParams);
		/*
		|---------------------------------------------------------
		| COMPOSE STEP 2 - Phonenumbers
		|---------------------------------------------------------
		| Choose phonenumbers for campaign .
		| User can add manually , upload a file
		| choose from contacts or select groups .
		|
		*/
		if($stateParams.status){
			$scope.activeCampaignSubmenuItem = {status: $stateParams.status};
		} else{
			$scope.activeCampaignSubmenuItem = {status: 'contacts'};
		}

		$scope.activeCampaignSubmenu = function(status){
			$scope.activeCampaignSubmenuItem.status = status ||  $scope.activeCampaignSubmenuItem.status;
		}
		$scope.numbersSource = 'contacts';
		$scope.changeNumbersSource = function(source){
			$scope.numbersSource = source;
		}

		/*
		|---------------------------------------------------------
		| COMPOSE STEP 2 - EDIT PHONENUMBERS
		|---------------------------------------------------------
		| Remove/readd phonenumbers on edit screen
		*/
		$scope.editPhonenumbersRemove = function(id){
			CampaignComposeService.campaignData.removed_phonenumbers.push(id);
		}

		$scope.editPhonenumbersReadd = function(id){
			var index = CampaignComposeService.campaignData.removed_phonenumbers.indexOf(id);
			CampaignComposeService.campaignData.removed_phonenumbers.splice(index, 1);
		}


		/*
		|---------------------------------------------------------
		| MANUALLY
		|---------------------------------------------------------
		| Here will go all logic for compose step 2 , when user
		| wants to manually add, or upload file as a source of numbers.
		| 
		*/
		$scope.addedNumbers = '';
		$scope.listingSkip = 0;
		$scope.manuallyAddedPage = 0;
		$scope.manuallyAddedPagesCount = 1;
		$scope.manuallyAddedNumbers = [];

		if($stateParams.phonenumbers){
			$scope.numbersSource = 'manually';
			var paramPhonenumbers = JSON.parse($stateParams.phonenumbers);
			for(index in paramPhonenumbers){
				$scope.manuallyAddedNumbers.push(paramPhonenumbers[index]);
			}
			$scope.manuallyAddedPagesCount = Math.ceil(paramPhonenumbers.length/7);
		}

		$scope.changeManuallyAddedPage = function(pageNumber){
			if(pageNumber < 0 || pageNumber > $scope.manuallyAddedPagesCount - 1){
				return;
			}
			$scope.manuallyAddedPage = pageNumber;
			$scope.listingSkip = $scope.manuallyAddedPage * 7;
		}

		$scope.startUpload = function(){
			numbersFileUpload.uploadAll();
		}

		var numbersFileUpload = $scope.numbersFileUpload = new FileUploader({
		    url: 'phonenumbers/upload-phonenumbers',
		    alias : 'file',
		    autoUpload : true,
		    formData: [{is_campaign_create: true}]
		});

		numbersFileUpload.onAfterAddingFile = function(item){
			$scope.uploadingImageName = item.file.name;
		}

		numbersFileUpload.onErrorItem = function(item, response, status, headers){
			$rootScope.stopLoader();
		}

		numbersFileUpload.onBeforeUploadItem  = function(){
			$rootScope.startLoader();
		}

		numbersFileUpload.onSuccessItem = function(item, data, status, headers){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$scope.uploadingImageName = false;
				$scope.numbersResponseData = data.resource;
				var responsePhonenumbers = data.resource.phonenumbers;
				for(index in responsePhonenumbers){
					var isDuplicate = false;
					for(ind in $scope.manuallyAddedNumbers){
						if(responsePhonenumbers[index].number == $scope.manuallyAddedNumbers[ind].number){
							isDuplicate = true;
							break;
						}
					}
					if(!isDuplicate){
						$scope.manuallyAddedNumbers.push(responsePhonenumbers[index]);
					}
				}
				$scope.manuallyAddedPagesCount = Math.ceil($scope.manuallyAddedNumbers.length/7);
			} else{
				$scope.uploadingImageName = false;
		      	notify({message: data.resource.error.text, classes: ['notification-alert-danger']})
		    }
		};

		$scope.openFileSelect= function(){
			$timeout(function() {
			    angular.element('#hiddenNumbersFileInput').trigger('click');
			}, 100);
		}

		$scope.addNumbersManually = {};
		$scope.addNumbers = function(){
			var postData = $scope.addNumbersManually;
			postData.is_campaign_create = true;
			$rootScope.startLoader();
			Restangular.all('phonenumbers/add-phonenumbers').post(postData).then(function(data){
				$scope.addedNumbers = '';
				$rootScope.stopLoader();
				if(data.resource.error.no == 0){
					$scope.numbersResponseData = data.resource;
					var responsePhonenumbers = data.resource.phonenumbers;
					for(index in responsePhonenumbers){
						var isDuplicate = false;
						for(ind in $scope.manuallyAddedNumbers){
							if(responsePhonenumbers[index].number == $scope.manuallyAddedNumbers[ind].number){
								isDuplicate = true;
								break;
							}
						}
						if(!isDuplicate){
							$scope.manuallyAddedNumbers.push(responsePhonenumbers[index]);
						}
					}
					$scope.manuallyAddedPagesCount = Math.ceil($scope.manuallyAddedNumbers.length/7);
				} else{

				}
			})
		}

		$scope.removeManuallyAddedContact = function(index){
			$scope.manuallyAddedNumbers.splice(index, 1);
			$scope.manuallyAddedPagesCount = Math.ceil( $scope.manuallyAddedNumbers.length /7);
		}

		/*
		|---------------------------------------------------------
		| CONTACTS
		|---------------------------------------------------------
		| Here will go all logic for compose step 2 , when user
		| wants to use contacts as the source of the phonenumbers
		| 
		*/


		Restangular.one('address-book/index-contacts').get().then(function(data){
			$scope.contacts = data.resource.contacts;
			$scope.contactsPage = data.resource.page;
			$scope.contactsPagesCount = Math.ceil(data.resource.count/10);
		});

		var routeContactIds = $stateParams.phoneNumberChecked;
		if(routeContactIds){
			$scope.numbersSource = 'contacts';
			$scope.checkedContacts = JSON.parse(routeContactIds);
		} else{
			$scope.checkedContacts = {};
		}
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
			if(page < 0 || page > $scope.pagesCount - 1){
				return;
			}
			$rootScope.startLoader();
			Restangular.one('address-book/index-contacts').get({page: page}).then(function(data){
				$rootScope.stopLoader();
				$scope.contacts = data.resource.contacts;
				$scope.contactsPage = data.resource.page;
				$scope.contactsPagesCount = Math.ceil(data.resource.count/10);
			});
		}

		/*
		|---------------------------------------------------------
		| GROUPS
		|---------------------------------------------------------
		| 
		| Here will go all logic for compose step 2 , when user
		| wants to use groups(s) as the source of the phonenumbers
		| 
		*/

		Restangular.one('address-book/index-groups').get().then(function(data){
			$scope.groups = data.resource.groups;
			$scope.groupsPage = data.resource.page;
			$scope.groupsPagesCount = Math.ceil(data.resource.count/10);
		});

		var routeGroupIds = $stateParams.group_ids;
		
		if(routeGroupIds){
			$scope.numbersSource = 'groups';
			$scope.checkedGroups = JSON.parse(routeGroupIds);
		} else{
			$scope.checkedGroups = {};
		}
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
			Restangular.one('address-book/index-groups').get({page: page}).then(function(data){
				$scope.groups = data.resource.groups;
				$scope.groupsPage = data.resource.page;
				$scope.groupsPagesCount = Math.ceil(data.resource.count/10);
			});
		}

		
		$scope.goToStep3 = function(){
			var postData = [];
			switch($scope.numbersSource){
				case 'manually':
					for(index in $scope.manuallyAddedNumbers){
						if($scope.manuallyAddedNumbers[index].tariff){
							postData.push($scope.manuallyAddedNumbers[index].number)
						}
					}
					break;
				case 'contacts':
					postData = $scope.checkedContacts;
					break;
				case 'groups':
					postData = $scope.checkedGroups;
					CampaignComposeService.campaignData.selected_groups = $scope.checkedGroups;
					break;
				default:
					break;
			}
			$rootScope.startLoader();
			Restangular.all('phonenumbers/add-numbers-and-calculate-cost-' + $scope.numbersSource ).post({ file_id: CampaignComposeService.campaignData.campaign_voice_file_id,  data: postData}).then(function(data){
				if(data.resource.error.no == 0){
					CampaignComposeService.finalStepData.maxCost = data.resource.max_cost;
					CampaignComposeService.finalStepData.sendingTime = data.resource.sending_time;
					CampaignComposeService.finalStepData.numbersCount = data.resource.phonenumbers.length;
					CampaignComposeService.campaignData.phonenumbers = data.resource.phonenumbers;
					CampaignComposeService.campaignData.max_cost = data.resource.max_cost;
					if(CampaignComposeService.editingCampaign || 
						(CampaignComposeService.reusingCampaign && 
						(CampaignComposeService.reusingSource == 'both' || 
						CampaignComposeService.reusingSource == 'receipents' ) ))
					{
						var editingPhonenumbersData = [];
						for(index in CampaignComposeService.currentPhonenumbers){
							if(CampaignComposeService.campaignData.removed_phonenumbers.indexOf(CampaignComposeService.currentPhonenumbers[index]._id) == -1){
								editingPhonenumbersData.push($scope.currentPhonenumbers[index].phone_no);
							}
						}
						Restangular.all('phonenumbers/add-numbers-and-calculate-cost-manually').post({ file_id: CampaignComposeService.campaignData.campaign_voice_file_id,  data: editingPhonenumbersData}).then(function(data1){
							$rootScope.stopLoader();
							if(data1.resource.error.no == 0){
								if(CampaignComposeService.reusingCampaign && 
									(CampaignComposeService.reusingSource == 'both' || 
									CampaignComposeService.reusingSource == 'receipents'))
								{
									for(ind in data1.resource.phonenumbers){
										CampaignComposeService.campaignData.phonenumbers.push(data1.resource.phonenumbers[ind]);
									}
								}
								CampaignComposeService.finalStepData.maxCost += data1.resource.max_cost;
								CampaignComposeService.campaignData.max_cost += data1.resource.max_cost;
								CampaignComposeService.finalStepData.numbersCount += data1.resource.phonenumbers.length;
								if(data1.resource.phonenumbers.length == 0 && data.resource.phonenumbers.length){
	          						notify({message: 'There is no valid phonenumber selected. This should be changed to beautiful error', classes: ['notification-alert-danger']})
								} else{
									CampaignComposeService.composeStep = 3;
								}
							}
						})
					} else{
						$rootScope.stopLoader();
						if(data.resource.phonenumbers.length == 0){
	          				notify({message: 'There is no valid phonenumber selected. This should be changed to beautiful error', classes: ['notification-alert-danger']})
						} else{
							CampaignComposeService.composeStep = 3;
						}
					}
					
				}
			})
		}


	}]);