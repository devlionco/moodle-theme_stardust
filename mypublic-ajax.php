<?php

require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

$action = required_param('action', PARAM_TEXT);

//TODO: security checks, token implementation or use moodle webservices


if ($action === "mypublicpage-save-shortform") {
    save_mypublicpage_shortform();
}

function save_mypublicpage_shortform() {
    global $USER, $DB;

    $userid = required_param('userid', PARAM_INT);
    $username = optional_param('username', '', PARAM_TEXT);
    // $password = optional_param('password', '', PARAM_RAW);
    $fullname = optional_param('fullname', '', PARAM_TEXT);
    $phone1 = optional_param('phone1', '', PARAM_TEXT);
    $institution = optional_param('institution', '', PARAM_TEXT);
    $address = optional_param('address', '', PARAM_TEXT);
    // $phone2 = optional_param('phone2', '', PARAM_TEXT);
    $icq = optional_param('icq', '', PARAM_INT);
    $birthday = optional_param('birthday', '', PARAM_RAW);
    $interests = optional_param('interests', '', PARAM_RAW);


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
        if(!empty($icq)){
            $user->icq = $icq;
        }

        $result = $DB->update_record('user', $user, $bulk=false);
        $response['user'] = ($result === true) ? 'OK' : $result;

        // update birthday
        if(!empty($birthday)){
            $birthday = new DateTime($birthday, core_date::get_server_timezone_object());
            $birthdayunix = $birthday->getTimestamp();
            $birthdayfieldid = $DB->get_field('user_info_field', 'id', array('shortname' => 'birthday'));  // SG - ugly hack to define birthday data field
            if ($dataid = $DB->get_field('user_info_data', 'id', array('userid' => $userid, 'fieldid' => $birthdayfieldid))) {
                $birthdayfielddata = new stdClass();
                $birthdayfielddata->fieldid = $birthdayfieldid;
                $birthdayfielddata->userid = $userid;
                $birthdayfielddata->id = $dataid;
                $birthdayfielddata->data = $birthdayunix;
                $result = $DB->update_record('user_info_data', $birthdayfielddata);
            } else {
                $result = $DB->insert_record('user_info_data', $birthdayfielddata);
            }
            $response['birthday'] = ($result === true) ? 'OK' : $result;
        }

        // update interests
        if(!empty($interests)){
            $interests = json_decode($interests);           // SG - if interests come as array
            //$interests = explode(", ", $interests[0]);    //SG - if interests come in one line - I devide it for tags
            $result = core_tag_tag::set_item_tags('core', 'user', $userid, context_user::instance($userid), $interests);
            $response['interests'] = 'OK';
        }

    echo json_encode($response);
};
