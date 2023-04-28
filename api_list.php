<!DOCTYPE html>
<html>
    <head>
        <title>Petscare APP API LIST</title>
        <style>
            body {
                font-family: Arial, Helvetica, Sans-Serif;
                font-size: 0.9em;
            }
            #apiList th {
                background: #808080 repeat-x scroll center left;
                padding: 7px 15px;
                text-align: left;
                border: 1px solid #000;
                background-color: dimgray;
                color: #fff;
            }
            #apiList td {
                background: none repeat-x scroll center left;
                color: #000;
                padding: 7px 15px;
                font-family: Verdana;
                border: 1px solid #000;
                /*border: 1px solid #808080;*/
            }
            #apiList tr.odd td {
                background: #e8e8ec repeat-x scroll center left;
                cursor: pointer;
                /*border: 1px solid #808080;*/
            }
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" type="text/javascript"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#apiList tr:odd").addClass("odd");
                $("#apiList tr:not(.odd)").hide();
                $("#apiList tr:first-child").show();
                $("#apiList tr.odd").click(function () {
                    $(this).next("tr").toggle();
                    $(this).find(".arrow").toggleClass("up");
                });
            });
        </script>
    </head>
    <body style="width: 100%">
        <div style="color: white; padding: 10px; text-decoration: none; font-family: Arial; font-size: 30px; padding-left: 5px; background-color: #0090e9">
            <div style="margin: 0px 15.7%;">
                API HELP
            </div>
        </div>
        <br />
        <?php
        if (!in_array($_SERVER['SERVER_NAME'], array('192.168.2.150', 'localhost')) && (isset($_SERVER['HTTP_CLIENT_IP']) || isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !(in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '192.168.2.150', 'fe80::1', '::1')) || php_sapi_name() === 'cli-server'))) {
            $url = "http://18.220.158.43/petscare/api/";
        } else {
            $url = "http://192.168.2.150:8989/api/";
        }
        ?> 
        <?php $i = 1; ?>
        <div style="width: 100%">
            <table id="apiList" style="width: 69%; margin: 0px 15.5%;">
                <tr style="border-radius: 5px;">
                    <th style="width: 1%;">No</th>
                    <th style="width: 5%;">Method</th>
                    <th style="width: 70%;">Url</th>
                </tr>

                <!-- Client Login -->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "clientlogin"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Client Login</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "clientlogin"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>emailid</td>
                                <td>string</td>
                                <td>emailid</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>password</td>
                                <td>string</td>
                                <td>Password</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>playerid</td>
                                <td>string</td>
                                <td>PlayerId</td>
                            </tr>

                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!-- Client Login -->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "clientresetpassword"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Client Login</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "clientresetpassword"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>client_id</td>
                                <td>string</td>
                                <td>Client Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>current_password</td>
                                <td>string</td>
                                <td>Current Password</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>new_password</td>
                                <td>string</td>
                                <td>New Password</td>
                            </tr>

                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!-- Client Register -->				
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "clientregister"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Client Regitration</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "clientregister"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>

                            <tr>
                                <td>password</td>
                                <td>string</td>
                                <td>Password</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>firstname</td>
                                <td>string</td>
                                <td>firstname</td>
                            </tr><tr></tr>
                            <tr>
                                <td>lastname</td>
                                <td>string</td>
                                <td>lastname</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>profile_image</td>
                                <td>file</td>
                                <td>profile_image(Optional)</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>emailid</td>
                                <td>string</td>
                                <td>emailid</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>contact_number</td>
                                <td>string</td>
                                <td>contact_number</td>
                            </tr>
                            <tr></tr><tr>
                                <td>client_address</td>
                                <td>string</td>
                                <td>client_address</td>
                            </tr>
                            <tr></tr><tr>
                                <td>client_notes</td>
                                <td>string</td>
                                <td>client_notes</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>playerid</td>
                                <td>string</td>
                                <td>PlayerId</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>					
                <!-- Company Login -->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "companylogin"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company Login</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "companylogin"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>emailid</td>
                                <td>string</td>
                                <td>emailid</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>password</td>
                                <td>string</td>
                                <td>Password</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>playerid</td>
                                <td>string</td>
                                <td>PlayerId</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!-- Company registeration -->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "companyregister"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company Regitration</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "companyregister"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>company_name</td>
                                <td>string</td>
                                <td>company_name</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>password</td>
                                <td>string</td>
                                <td>Password</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>company_image</td>
                                <td>file</td>
                                <td>company_image(Optional)</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>website</td>
                                <td>string</td>
                                <td>website</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>address</td>
                                <td>string</td>
                                <td>address</td>
                            </tr>
                            <tr></tr><tr>
                                <td>emailid</td>
                                <td>string</td>
                                <td>emailid</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>contact_number</td>
                                <td>string</td>
                                <td>contact_number</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>about</td>
                                <td>string</td>
                                <td>about</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>service_id</td>
                                <td>array</td>
                                <td>service_id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>playerid</td>
                                <td>string</td>
                                <td>PlayerId</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!---------Company Listing Client side after request accepted or rejected----------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "clientid/companies"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company List(with request status = accepted)</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "clientid/companies"; ?></p>

                    </td>
                </tr>		
                <!---------Company Listing----------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "clientid/all/companies"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company List</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "clientid/all/companies"; ?></p>

                    </td>
                </tr>
                <!---------Client Listing company----------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "companyid/clients"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Client List Company side</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "companyid/clients"; ?></p>

                    </td>
                </tr>
                <!---------Service Listing----------->

                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "services"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Get services list</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "services"; ?></p>

                    </td>
                </tr>	
                <!-- Company Profile Update -->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "company/profile/companyid"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company Profile Update</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "company/profile/companyid"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>company_name</td>
                                <td>string</td>
                                <td>company_name</td>
                            </tr>

                            <tr></tr>
                            <tr>
                                <td>website</td>
                                <td>string</td>
                                <td>website</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>address</td>
                                <td>string</td>
                                <td>address</td>
                            </tr>
                            <tr></tr><tr>
                                <td>emailid</td>
                                <td>string</td>
                                <td>emailid</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>contact_number</td>
                                <td>string</td>
                                <td>contact_number</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>about</td>
                                <td>string</td>
                                <td>about</td>
                            </tr>

                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!--------------- Compnay Image update ------------------------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "company/profilepic"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company Image Update</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "company/profilepic"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>company_id</td>
                                <td>string</td>
                                <td>Company Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>company_image</td>
                                <td>file</td>
                                <td>company_image</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!-- Contract request ---->	
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "contractrequest"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Contract Request</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "contractrequest"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>company_id</td>
                                <td>array</td>
                                <td>company_id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>client_id</td>
                                <td>int</td>
                                <td>client_id</td>
                            </tr>

                            <tr></tr>

                        </table>
                    </td>
                </tr>
                <!-- Profile Update ----->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "client/profile/id"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Profile Edit</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "client/profile/id"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>firstname</td>
                                <td>string</td>
                                <td>firstname</td>
                            </tr><tr></tr>
                            <tr>
                                <td>lastname</td>
                                <td>string</td>
                                <td>lastname</td>
                            </tr>

                            <tr></tr>
                            <tr>
                                <td>emailid</td>
                                <td>string</td>
                                <td>emailid</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>contact_number</td>
                                <td>string</td>
                                <td>contact_number</td>
                            </tr>
                            <tr></tr><tr>
                                <td>client_address</td>
                                <td>string</td>
                                <td>client_address</td>
                            </tr>
                            <tr></tr><tr>
                                <td>client_notes</td>
                                <td>string</td>
                                <td>client_notes</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!-- Profile Picture Update ----->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color:#CC9900;">POST</td>
                    <td><?php echo $url . "client/profilepic"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Profile Picture Edit</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "client/profilepic"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>client_id</td>
                                <td>string</td>
                                <td>ClienId</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>profile_image</td>
                                <td>file</td>
                                <td>Profile image</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!--- client logout ---->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #0090e9;">PUT</td>
                    <td><?php echo $url . "clientlogout/id"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Client Logout</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "clientlogout/id"; ?></p>
                    </td>
                </tr>
                <!--- company logout ---->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #0090e9;">PUT</td>
                    <td><?php echo $url . "companylogout/id"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company Logout</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "companylogout/id"; ?></p>
                    </td>
                </tr>
                <!--- COntract status update --->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color:#CC9900;">POST</td>
                    <td><?php echo $url . "contractstatusupdate"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Contract Status Update</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "contractstatusupdate"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>contract_id</td>
                                <td>string</td>
                                <td>ContractId</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>status</td>
                                <td>string</td>
                                <td>Status</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!---- Pets insert ---->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color:#CC9900;">POST</td>
                    <td><?php echo $url . "petsinsert"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Pet Insert</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "petsinsert"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>client_id</td>
                                <td>string</td>
                                <td>ClienId</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>pet_image</td>
                                <td>file</td>
                                <td>Pet image</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>pet_name</td>
                                <td>string</td>
                                <td>Pet name</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>age</td>
                                <td>string</td>
                                <td>Pet Age</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>medical_detail</td>
                                <td>string</td>
                                <td>Medical Detail</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>pet_notes</td>
                                <td>string</td>
                                <td>Pet Notes</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>latitude</td>
                                <td>string</td>
                                <td>latitude</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>longitude</td>
                                <td>string</td>
                                <td>longitude</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!---- Pets update ---->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color:#CC9900;">POST</td>
                    <td><?php echo $url . "petupdate/petid"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Pet Insert</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "petupdate/petid"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>client_id</td>
                                <td>string</td>
                                <td>ClienId</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>pet_image</td>
                                <td>file</td>
                                <td>Pet image</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>pet_name</td>
                                <td>string</td>
                                <td>Pet name</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>age</td>
                                <td>string</td>
                                <td>Pet Age</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>medical_detail</td>
                                <td>string</td>
                                <td>Medical Detail</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>pet_notes</td>
                                <td>string</td>
                                <td>Pet Notes</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>latitude</td>
                                <td>string</td>
                                <td>latitude</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>longitude</td>
                                <td>string</td>
                                <td>longitude</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!-----Pet List------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "clientid/pets"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Pets List</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "clientid/pets"; ?></p>

                    </td>
                </tr>	
                <!-----Pet Delete------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "pet/petid/clientid/delete"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Pet Delete</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "pet/petid/clientid/delete"; ?></p>

                    </td>
                </tr>	
                <!------- Appointment Api-------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color:#CC9900;">POST</td>
                    <td><?php echo $url . "appointment"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Appoinment Insert</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "appointment"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>client_id</td>
                                <td>string</td>
                                <td>Client Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>company_id</td>
                                <td>string</td>
                                <td>Company Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>service_id</td>
                                <td>string</td>
                                <td>Service Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>date</td>
                                <td>string</td>
                                <td>Date</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>visits</td>
                                <td>string</td>
                                <td>Visits</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>visit_hours</td>
                                <td>string</td>
                                <td>Visit Hours</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>pet_id</td>
                                <td>string</td>
                                <td>Pet Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>message</td>
                                <td>string</td>
                                <td>message</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!--------Appointment Listing-------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "type/id/appointments"; ?></td>

                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Appointment Listing</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "type/id/appointments"; ?></p>
                        <p>type(client/compnay) and id(clinetid/compnayid)</p>
                    </td>
                </tr>	
                <!--------Appointment Listing with status-------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "type/id/staus/appointments"; ?></td>

                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Appointment Listing</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "type/id/status/appointments"; ?></p>
                        <p>type(client/compnay) and id(clinetid/compnayid) , status(accepted/pending/rejected)</p>
                    </td>
                </tr>	
                <!--------Appointment Listing according to status-------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "status/appointments"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Appointment Listing according to status</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "status/appointments"; ?></p>
                        <p>Status(accepted/pending/rejected)</p>
                    </td>
                </tr>	
                <!--------Appointment Status update-------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color:#CC9900;">POST</td>
                    <td><?php echo $url . "appointmentstatusupdate"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Appoinment status update</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "appointmentstatusupdate"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>appointment_id</td>
                                <td>string</td>
                                <td>Appointment Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>status</td>
                                <td>string</td>
                                <td>Status</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!--------Appointment History-------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color:#CC9900;">POST</td>
                    <td><?php echo $url . "type/id/appointmenthistory"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Appoinment History</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "type/id/appointmenthistory"; ?></p>
                        <p>type(client/compnay) and id(clinetid/compnayid)</p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>date</td>
                                <td>string</td>
                                <td>Date</td>
                            </tr>
                            <tr></tr>

                        </table>
                    </td>
                </tr>
                <!-----Staff Insert ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color:#CC9900;">POST</td>
                    <td><?php echo $url . "staffinsert"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Staff Insert</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "staffinsert"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>company_id</td>
                                <td>string</td>
                                <td>Company Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>firstname</td>
                                <td>string</td>
                                <td>First Name</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>lastname</td>
                                <td>string</td>
                                <td>Last Name</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>emailid</td>
                                <td>string</td>
                                <td>Email Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>profile_image</td>
                                <td>file</td>
                                <td>Profile image</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>contact_number</td>
                                <td>string</td>
                                <td>Contact Number</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!-----Staff assign ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color:#CC9900;">POST</td>
                    <td><?php echo $url . "staffassign"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Staff Assign</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "staffassign"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>appointment_id</td>
                                <td>string</td>
                                <td>Appointment Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>staff_id</td>
                                <td>string</td>
                                <td>Staff Id</td>
                            </tr>

                            <tr></tr>
                        </table>
                    </td>
                </tr>

                <!-----Staff Listing ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "compnayid/staffs"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Staff Listing</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "compnayid/staffs"; ?></p>


                    </td>
                </tr>
                <!-----Staff Delete ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "staff/staffid/compnayid/delete"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Staff Delete</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "staff/staffid/compnayid/delete"; ?></p>


                    </td>
                </tr>
                <!------ staff update --------->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color:#CC9900;">POST</td>
                    <td><?php echo $url . "staffupdate/staffid"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Staff Update</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "staffupdate/staffid"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>company_id</td>
                                <td>string</td>
                                <td>Company Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>firstname</td>
                                <td>string</td>
                                <td>First Name</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>lastname</td>
                                <td>string</td>
                                <td>Last Name</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>emailid</td>
                                <td>string</td>
                                <td>Email Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>profile_image</td>
                                <td>file</td>
                                <td>Profile image</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>contact_number</td>
                                <td>string</td>
                                <td>Contact Number</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!-- Client forgot password -->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "client/forgotpassword"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Forgot password for client</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "client/forgotpassword"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>emailid</td>
                                <td>string</td>
                                <td>Email Id</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!-- Company forgot password -->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "company/forgotpassword"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Forgot password for company</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "company/forgotpassword"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>emailid</td>
                                <td>string</td>
                                <td>Email Id</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!-- Price adding -->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "priceadd"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Price adding for service</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "priceadd"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>service_id</td>
                                <td>string</td>
                                <td>Service Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>full_hour_price</td>
                                <td>string</td>
                                <td>Full Hour Price</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>half_hour_price</td>
                                <td>string</td>
                                <td>Half Hour Price</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>additional_hours_price</td>
                                <td>string</td>
                                <td>Additional Hours Price</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>additional_visits_price</td>
                                <td>string</td>
                                <td>Additional Visits Price</td>
                            </tr>
                            <tr></tr>
                        </table>
                    </td>
                </tr>
                <!--  Appoinment resend  -->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "clientid/appointments/resend"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Price adding for service</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "clientid/appointments/resend"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>date</td>
                                <td>string</td>
                                <td>Date</td>
                            </tr>
                            <tr></tr>

                        </table>
                    </td>
                </tr>
                <!--  Payment  -->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "payment"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Changing status in appointment when payment done</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "payment"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>appointment_id</td>
                                <td>string</td>
                                <td>Appointment Id</td>
                            </tr>
                            <tr></tr>

                        </table>
                    </td>
                </tr>
                <!--  Company status active and account store in table  -->
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "compnay/account"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company status change with storing account id in database</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "compnay/account"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>company_id</td>
                                <td>string</td>
                                <td>Company Id</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>account_id</td>
                                <td>string</td>
                                <td>Account Id</td>
                            </tr>
                            <tr></tr>

                        </table>
                    </td>
                </tr>
                <!-----Company and Client Delete from contract ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "compnayid/clientid/delete"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company and Client Delete From Contract</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "compnayid/clientid/delete"; ?></p>
                    </td>
                </tr>
                <!-----admin ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #CC9900;">POST</td>
                    <td><?php echo $url . "admin"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Login To Admin Panel</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "admin"; ?></p>
                        <h5>Parameters :</h5>
                        <table>
                            <tr>
                                <td>Parameter Name</td>
                                <td>DataType</td>
                                <td>Description</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>username</td>
                                <td>string</td>
                                <td>username</td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td>password</td>
                                <td>password</td>
                                <td>password</td>
                            </tr>
                            <tr></tr>

                        </table>
                    </td>
                </tr>
                <!-----Company list for admin ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "companies"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company List For Admin Panel</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "companies"; ?></p>
                    </td>
                </tr>
                <!-----Client list for admin ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "clients"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p> Client List For Admin Panel</p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "clients"; ?></p>
                    </td>
                </tr>
                <!-----Client delete for admin panel ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "clientid/delete/client"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Client Delete </p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "clientid/delete/client"; ?></p>
                    </td>
                </tr>
                <!-----Company delete for admin panel ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "companyid/delete/company"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company Delete </p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "companyid/delete/company"; ?></p>
                    </td>
                </tr>
                <!-----Single Company details for admin panel ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "company/companyid/details"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company Delete </p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "company/companyid/details"; ?></p>
                    </td>
                </tr>
                <!-----Single Client details for admin panel ------>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="background-color: #4DAB58;">GET</td>
                    <td><?php echo $url . "client/clientid/details"; ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h5>Description :</h5>
                        <p>Company Delete </p>
                        <h5>Friendly Url :</h5>
                        <p><?php echo $url . "client/clientid/details"; ?></p>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
