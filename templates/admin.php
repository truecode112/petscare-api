<html>
    <head>
        <style>

        </style>
    </head>
    <body>
        <?php
        $companies = Company::all();


        foreach ($companies as $key => &$company) {
            $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$company->company_id}");
            $companyServices = array();
            foreach ($services as $companyService) {
                $companyServices[] = array(
                    'id' => $companyService->service_id,
                    'name' => $companyService->service_name
                );
            }
            $contract = Contract::find(array('conditions' => "company_id = {$company->company_id}"));
            $companies[$key] = array(
                'id' => $company->company_id,
                'name' => $company->company_name,
                'website' => $company->website,
                'company_image' => $company->company_image != NULL ? COMPANY_PIC_PATH . $company->company_image : NULL,
                'address' => $company->address,
                'emailid' => $company->emailid,
                'contact_number' => $company->contact_number,
                'about' => $company->about,
                //'client_id' => empty($contract) ? NULL : $contract->client_id,
                //'contract_id' => empty($contract) ? NULL : $contract->contract_id,
                'player_id' => $company->player_id,
                    //  'status' => empty($contract) ? NULL : $contract->status,
            );

            //$company->to_array(array('except' => array('salt', 'password', 'added_on', 'updated_on')));

            $companies[$key]['services'] = $companyServices;
            /* if (empty($companies[$key]['services'])) {
              unset($companies[$key]);
              } */
        }
        /* foreach ($companies as $key => $cm) {
          if (empty($cm['services'])) {
          unset($companies[$key]);
          }
          } */
        ?>
        <table>
            <thead><th>Company Name</th><th>Email</th><th>Contact Number</th><th>Image</th><th>Website</th><th>address</th><th>About</th></thead>
        <tbody>
            <?php foreach ($companies as $key => &$company) { ?>
            <tr>
                <td><?php echo $company['name']; ?></td>
                <td><?php echo $company['emailid']; ?></td>
                <td><?php echo $company['contact_number']; ?></td>
                <td><?php echo $company['company_image']; ?></td>
                <td><?php echo $company['website']; ?></td>
                <td><?php echo $company['address']; ?></td>
                <td><?php echo $company['about']; ?></td>
            </tr>

            <?php } ?>

        </tbody>
    </table>
</body>
</html>