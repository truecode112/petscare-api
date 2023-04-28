<?php
 class DatabaseLayer
 { 			
    public function __constructor(){
    }
	//Client Signin signup
    public function clientLogin($emailid,$password)
	{
		$dbObject = new DbConnect();
		$sql = "select * from tbl_clients where emailid='$emailid' AND password='$password'";
        $result = mysqli_query($dbObject->getDb(), $sql);
		$count=mysqli_num_rows($result);
		$row=mysqli_fetch_array($result);
		if($count > 0)
		{
				return $isClientRegistered=array("id" => $row['client_id'],
					"username" => $row['username'],
					"firstname" => $row['firstname'],
					"lastname" => $row['lastname'],
					"emailid" => $row['emailid'],
					"profile_image" => $row['profile_image']? USER_PIC_URL_PATH. $row['profile_image'] : null,
					"contact_number" => $row['contact_number'],
					"client_address" => $row['client_address'],
					"client_notes" => $row['client_notes'],
				);
        }
		return false;	
    }
	public function isClientExist($emailid)
	{
		$dbObject = new DbConnect();
		$sql = "select * from tbl_clients where emailid='$emailid'";
        $result = mysqli_query($dbObject->getDb(), $sql);
		$count=mysqli_num_rows($result);
		if($count > 0)
		{
			return true;
        }
		else
		{
			return false;
		}		
    }
    public function addNewClient($username,$password,$firstname, $lastname,$profile_image, $emailid,$contact_number,$client_address,$client_notes)
	{
        $dbObject = new DbConnect();
        if(!$this->isClientExist($emailid))
		{
			$sql = "Insert into  tbl_clients(username,password,firstname,lastname,profile_image,emailid,contact_number,client_address,client_notes) values('$username','$password','$firstname', '$lastname','$profile_image','$emailid','$contact_number','$client_address','$client_notes')";
			$result=mysqli_query($dbObject->getDb(), $sql);	
			if($profile_image){
				move_uploaded_file($_FILES['profile_image']['tmp_name'],'../'.USER_PIC_URL.$profile_image);
			}
			if($result)
			{		
						
				return true;
			}
			return false;	
       }
    }
	//Staff login register
	public function companyLogin($emailid,$password)
	{
		$dbObject = new DbConnect();
		$sql = "select * from tbl_companies where emailid = '$emailid' and password='$password'";
        $result = mysqli_query($dbObject->getDb(), $sql);
		$count=mysqli_num_rows($result);
		$row=mysqli_fetch_array($result);
		if($count > 0 )
		{
			return $company=array("id"=>$row['company_id'],
			"company_name"=>$row['company_name'],
			"emailid"=>$row['emailid'],
			"website"=>$row['website'],
			"company_image"=>$row['company_image'] ? COMPANY_PIC_PATH.$row['company_image'] : null,
			"address"=>$row['address'],
			"contact_number"=>$row['contact_number'],
			"about"=>$row['about'],
			);
		}
		return false;
    }
	public function isCompanyExists($emailid)
	{
		
		$dbObject = new DbConnect();
		$sql = "select * from tbl_companies where emailid = '$emailid'";
        $result = mysqli_query($dbObject->getDb(), $sql);
		$count=mysqli_num_rows($result);
		if($count > 0)
		{
			return true;
        }
		else
		{
			return false;
		}		
    }
	public function getCompanyList()
	{
		$db = new DbConnect();
		$selectCompany = "select * from tbl_companies";
		$result = mysqli_query($db->getDb(), $selectCompany);
		$count=mysqli_num_rows($result);
		$companies=array();
		if($count > 0)
		{
			while($row=mysqli_fetch_array($result))
			{
				 $companies[] = array(
								"id" => $row['company_id'],
								"name" => $row['company_name'],
								"website"=>$row['website'],
								'company_image'=>$row['company_image'] ? COMPANY_PIC_PATH.$row['company_image'] : null,
								"address"=>$row['address'],
								"emailid"=>$row['emailid'],
								"contact_number"=>$row['contact_number'],
								"about"=>$row['about'],
								"services" => $this->getCompanyServices($row['company_id'])
							);
			}
		}
		return $companies;
	}
	
	public function getCompanyServices($company_id){
		$db = new DbConnect();
		$queryServices = "select s.service_id, s.service_name from tbl_company_services cs, tbl_services s where cs.service_id = s.service_id AND cs.company_id = {$company_id}";
		$result = mysqli_query($db->getDb(), $queryServices);
		$count=mysqli_num_rows($result);
		$services=array();
		if($count > 0)
		{
			while($row=mysqli_fetch_array($result))
			{
				 $services[] = array(
								"id" => $row['service_id'],
								"name" => $row['service_name']
							);
			}
		}
		return $services;
	}
	
    public function addNewCompany($company_name,$password,$company_image, $website, $address, $emailid ,$contact_number,$about,$service_ids)
	{
		$dbObject = new DbConnect();
		if(!$this->isCompanyExists($emailid))
		{
			$sql = "Insert into tbl_companies(company_name,password,company_image,website,address,emailid,contact_number,about) values('$company_name','$password','$company_image', '$website', '$address','$emailid','$contact_number','$about')";
			$result=mysqli_query($dbObject->getDb(), $sql);	
			if($company_image)
			{
				move_uploaded_file($_FILES['company_image']['tmp_name'],'../'.COMPANY_PIC_URL.$company_image);
			}
			$companyId = mysqli_insert_id($dbObject->getDb());
			if(isset($service_ids)){
				foreach($service_ids as $serviceID)
				{
					$table = "INSERT INTO tbl_company_services(company_id,service_id,no_of_visits,price,additional_walks,addtional_visits) VALUES ($companyId,$serviceID,'','','','')";
					mysqli_query($dbObject->getDb(),$table);
				}
			}
			if($result)
			{				
				return true;
			}
			return false;	
		}
	}
	public function getServicesList()
	{
		
		$db = new DbConnect();
		$query = "select * from tbl_services";
		
		$result = mysqli_query($db->getDb(), $query);
		$count=mysqli_num_rows($result);
		
		$services=array();
		if($count > 0)
		{
			
			while($row=mysqli_fetch_array($result))
			{
				 $services[] = array(
								"id" => $row['service_id'],
								"name" => $row['service_name']
							);
			}
		}
		return $services;
	}
	public function isContractExists($companyId,$client_id)
	{
		echo $companyId;
		
		$db=new DbConnect();
		$sql="select * from tbl_contracts where company_id=$companyId AND client_id=$client_id";
		$result=mysqli_query($db->getDb(),$sql);
		$row=mysqli_fetch_array($result);
		$count=mysqli_num_rows($result);
		$isInserted=array();
		if($count>0)
		{
			$isInserted[]=array("id"=>$row['company_id']);
		}
		else
		{
			return false;
		}
		return $isInserted;
	}
	public function addContractRequest($company_id,$client_id,$status)
	{
			$res="";
			$db=new DbConnect();
			foreach($company_id as $companyId)
			{
				if(!$this->isContractExists($companyId,$client_id))
				{
					$sql="insert into tbl_contracts(company_id,client_id,status) values($companyId,$client_id,'$status')";
					$res=mysqli_query($db->getDb(),$sql);		
				}					
			}	
			if($res)
			{
				return true;
			}
			else{
				return false;
			}		
	}
 }
?>