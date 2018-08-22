<?php

require_once(__DIR__.'/../../config.php');

$action = required_param('action', PARAM_TEXT);

//TODO: security checks, token implementation or use moodle webservices 


if ($action === "mypublicpage-save-shortform") {
    save_mypublicpage_shortform();
}

function save_mypublicpage_shortform() {
    global $USER, $DB;
    
    $userid = required_param('userid', PARAM_INT);
    $username = optional_param('username', '', PARAM_TEXT);
    $password = optional_param('password', '', PARAM_RAW);
    $fullname = optional_param('fullname', '', PARAM_TEXT);
    $phone1 = optional_param('phone1', '', PARAM_TEXT);
    $institution = optional_param('institution', '', PARAM_TEXT);
    $address = optional_param('address', '', PARAM_TEXT);
    $phone2 = optional_param('phone2', '', PARAM_TEXT);

        $fullname = preg_replace('/\s+/', ' ',$fullname);
        $arrname = explode(' ', $fullname);
        $firstname = trim($arrname[0]);
        $lastname = '';
        if(isset($arrname[1])){
            $lastname = $arrname[1];
        }

        $user = $DB->get_record('user', array('id' => $userid));
        if(!empty($username)){
            $user->username = $username;
        }
        if(!empty($password)){
            $user->password = hash_internal_user_password($password);
        }
        if(!empty($firstname)){
            $user->firstname = $firstname;
        }
        if(!empty($lastname)){
            $user->lastname = $lastname;
        }
        if(!empty($phone1)){
            $user->phone1 = $phone1;
        }
        if(!empty($phone2)){
             $user->phone2 = $phone2;
        }
        if(!empty($institution)){
            $user->institution = $institution;
        }
        if(!empty($address)){
            $user->address = $address;
        }

        $result = $DB->update_record('user', $user, $bulk=false); 

    return $result;
};