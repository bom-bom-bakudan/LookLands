<?php

//Load the database configuration file
require_once 'dbConfig.php';

$jsonStr = file_get_contents('php://input');
$jsonObj = json_decode($jsonStr);


if(!empty($jsonObj->request_type) && $jsonObj->request_type == 'user_auth'){
    $credential = !empty($jsonObj->$credential) ? $jsonObj->credential : '';


    //Decode response payload from JWT token
    list($header,$payload,$signature) = explode(".",$credential);
    $responsePayload = json_decode(base64_decode($payload))


    if(!empty($responsePayload)){
        //The user's profile info
        $oauth_provider = 'google';
        $oauth_uid = !empty($responsePayload->sub) ? $responsePayload->sub : '';
        $first_name = !empty($responsePayload->given_name) ? $responsePayload->given_name : '' ;
        $last_name = !empty($responsePayload->family_name) ? $responsePayload->family_name : '' ;
        $email = !empty($responsePayload->email) ? $responsePayload->email : '';
        $avatar = !empty($responsePayload->avatar) ? $responsePayload->avatar : '';

        //Check whether the user data already exist in the database
        $query = "SELECT * FROM users WHERE oauth_provider = '".$oauth_provider."' AND oauth_uid = '".$oauth_uid."'" ;
        $result = $db->query($query);
        if($result->num_rows > 0){
            //Update user data if already exists
            $query = "UPDATE users SET first_name = '".$first_name."', last_name = '".$last_name."',email = '".$email."',avatar = '".$avatar."',update_at = NOW() WHERE oauth_provider = '".$oauth_provider."' AND oauth_uid = '".$oauth_uid."'";
            $update_at = $db->query($query);
        }
        else{
            //Insert user data
            $query = "INSERT INTO users VALUES (NULL, '".$oauth_provider."','".$oauth_uid."','".$first_name."','".$last_name."','".$email."','".$avatar."',NOW(),NOW())";
            $insert = $db->query($query);

        }

        $output = [
            'status' => 1,
            'msg' => 'Account data inserted successfully!',
            'pdata' => $responsePayload
        ];
        echo json_encode($output);
    }
    else{
        echo json_encode(['error' => 'Account data is not available!']);
    }

}



?>