<?php
    if ($_POST['action'] == "edit") { //check if the action type of edit has been set in the post request
        include("../connect.php");
        //determine variables
        $lead_guest_id = $_POST['guest_id'];
        $guest_fname= mysqli_real_escape_string($db, $_POST['guest_fname']);
        $guest_sname= mysqli_real_escape_string($db, $_POST['guest_sname']);
        $guest_email= mysqli_real_escape_string($db, $_POST['guest_email']);
        $guest_address= htmlspecialchars($_POST['guest_address']);
        $guest_postcode= mysqli_real_escape_string($db, $_POST['guest_postcode']);
        $guest_group_id = $_POST['guest_group_id'];
        $event_id = $_POST['event_id'];
        //Update guest
        $guest = $db->prepare('UPDATE guest_list SET guest_fname=?, guest_sname=?, guest_email=?, guest_address=?, guest_postcode=?  WHERE guest_id =?');
        $guest->bind_param('sssssi',$guest_fname, $guest_sname, $guest_email, $guest_address, $guest_postcode,  $lead_guest_id);
        $guest->execute();
        $guest->close();

       //once the guest has been updated, determine if the user has added other members to the lead guest.
       //only set up a guest group if one is not present in the post request which would mean that the lead guest was a sole invite to start with
       if(isset($_POST['guest_group']) && $_POST['guest_group_id'] == null ){
        /////insert each guest into the guest list from the POST request
        //create a guest group if the guest being added has one or more extra invites
            //set up a group name using first and last name of primary guest
            $group_name = $guest_fname.' '.$guest_sname;
            //insert into guest group tables
            $group = $db->prepare('INSERT INTO guest_groups (guest_group_name, guest_group_organiser) VALUES (?,?)');
            $group->bind_param('si',$group_name, $lead_guest_id);
            $group->execute();
            $group->close();
            $new_group_id = $db->insert_id;
            //change the lead guest to a group organsier
            $guest_type = "Group Organiser";
            //update guest list with the guest group id
            $guest = $db->prepare('UPDATE guest_list SET guest_group_id=?, guest_type=?  WHERE guest_id =?');
            $guest->bind_param('isi',$new_group_id, $guest_type,  $lead_guest_id);
            $guest->execute();
            $guest->close();

        //guest array for all new added guests at the time of making the guest
        $guest_group = $_POST['guest_group'];
        $guest_array = array();
        $guest_type = "Member"; //only set as a member, these guests are a group member
        $new_guest = $db->prepare('INSERT INTO guest_list (guest_fname, guest_sname, guest_type, guest_group_id) VALUES (?,?,?,?)');
        foreach ($guest_group as $group_member) {
            // if the plus one box has been ticked then add them as a plus one
            $fname = $group_member['guest_fname'];
            if(isset($group_member['plus_one']) && $group_member['plus_one'] == "plus_one"){
                $fname = $guest_fname." ".$guest_sname."'s +1";
            }
            $new_guest->bind_param('sssi', $fname, $group_member['guest_sname'], $guest_type, $new_group_id);
            $new_guest->execute();
            //insert into an array for adding to the invites table
            $new_guest_id = $db->insert_id;
            array_push($guest_array, $new_guest_id);
        }
        $new_guest->close();
        if(isset($_POST['event_id'])){
            
            $invite_rsvp_status = "Not Replied";
            /////Add to invites table for each guest 
            $set_invites = $db->prepare('INSERT INTO invitations (guest_id, event_id, invite_rsvp_status, guest_group_id) VALUES (?,?,?,?)');
            foreach ($guest_array as $guest) {
                $set_invites->bind_param('iisi', $guest, $event_id, $invite_rsvp_status, $new_group_id);
                $set_invites->execute();
            }
    
            $set_invites->close();
        }
        //update the guest list with the amount of extra invites that they have based on how many guests have been added.
        
        $guest_extra_invites = count($guest_array);
        //update guest list with the guest group id
        $guest = $db->prepare('UPDATE guest_list SET guest_extra_invites=?  WHERE guest_id =?');
        $guest->bind_param('ii',$guest_extra_invites, $lead_guest_id);
        $guest->execute();
        $guest->close();
        //only insert into invites table if the user has selected an event to add the guests to


        }
        //update the guest group with any guests that have been added and if this guest is a group organiser
        if(isset($_POST['guest_group']) && $_POST['guest_group_id'] >0 ){
            
            //guest array for all new added guests at the time of making the guest
            $guest_group = $_POST['guest_group'];
            $guest_array = array();
            $guest_type = "Member"; //only set as a member, these guests are a group member
            $new_guest = $db->prepare('INSERT INTO guest_list (guest_fname, guest_sname, guest_type, guest_group_id) VALUES (?,?,?,?)');
            foreach ($guest_group as $group_member) {
                // if the plus one box has been ticked then add them as a plus one
                $fname = $group_member['guest_fname'];
                if(isset($group_member['plus_one']) && $group_member['plus_one'] == "plus_one"){
                    $fname = $guest_fname." ".$guest_sname."'s +1";
                }
                $new_guest->bind_param('sssi', $fname, $group_member['guest_sname'], $guest_type, $guest_group_id);
                $new_guest->execute();
                //insert into an array for adding to the invites table
                $new_guest_id = $db->insert_id;
                array_push($guest_array, $new_guest_id);
            }
            $new_guest->close();
            if(isset($_POST['event_id'])){
                $invite_rsvp_status = "Not Replied";
                /////Add to invites table for each guest 
                $set_invites = $db->prepare('INSERT INTO invitations (guest_id, event_id, guest_group_id, invite_rsvp_status) VALUES (?,?,?,?)');
                foreach ($guest_array as $guest_id) {
                    $set_invites->bind_param('iiis', $guest_id, $event_id, $guest_group_id, $invite_rsvp_status);
                    $set_invites->execute();
                }
        
                $set_invites->close();
            }
            //update the guest list with the amount of extra invites that they have based on how many guests have been added.
            //count how many members exist and update the table
            $guest_group = $db->query("SELECT guest_id FROM guest_list WHERE guest_group_id=".$guest_group_id." AND guest_type='Member'");
            
            $guest_extra_invites = $guest_group->num_rows;
            $guest_type="Group Organiser";
            //update guest list with the guest group id and make sure they are now a Group Organiser
            $guest = $db->prepare('UPDATE guest_list SET guest_extra_invites=?, guest_group_id=?, guest_type=?  WHERE guest_id =?');
            $guest->bind_param('iisi',$guest_extra_invites, $guest_group_id, $guest_type, $lead_guest_id);
            $guest->execute();
            $guest->close();
            //only insert into invites table if the user has selected an event to add the guests to
            
    
            }
            $response = "success";
    }


    if ($_POST['action'] == "create") { //check if the action type of create has been set in the post request
        include("../connect.php");
        $response="";
        //determine variables
        $guest_fname= mysqli_real_escape_string($db, $_POST['guest_fname']);
        $guest_sname= mysqli_real_escape_string($db, $_POST['guest_sname']);
        $guest_email= mysqli_real_escape_string($db, $_POST['guest_email']);
        $guest_address= htmlspecialchars($_POST['guest_address']);
        $guest_postcode= mysqli_real_escape_string($db, $_POST['guest_postcode']);
        //set extra invites to zero until guest group has been added
        $guest_extra_invites=0;
        //create and RSVP CODE
        $code = rand(1000,20000);
        $code_name = mb_substr($_POST['guest_sname'],0,2);
        $code_name = strtoupper($code_name);
        $guest_rsvp_code = $code_name . $code; // Generate random RSVP Code
        if(isset($_POST['guest_group']) && count($_POST['guest_group'])>=1){
            //if the guest has 1 or more extra invites then add them as a group organiser
            $guest_type= "Group Organiser";
        }else{
            $guest_type="Sole";
        }
        if(isset($_POST['guest_group'])){
            $guest_type= "Group Organiser";
        }
        $guest_rsvp_status = "Not Replied";
        //insert lead guest
        $guest = $db->prepare('INSERT INTO guest_list (guest_fname, guest_sname, guest_email, guest_address, guest_postcode, guest_rsvp_code, guest_rsvp_status,guest_extra_invites, guest_type) VALUES (?,?,?,?,?,?,?,?,?)');
        $guest->bind_param('sssssssis',$guest_fname, $guest_sname, $guest_email, $guest_address, $guest_postcode, $guest_rsvp_code,$guest_rsvp_status, $guest_extra_invites, $guest_type);
        $guest->execute();
        $guest->close();
        $lead_guest_id = $db->insert_id;//last id entered
       
        //add this guest to the event guest List they have been added to.
        if(isset($_POST['event_id'])){
            $event_id = $_POST['event_id'];
            $invite_rsvp_status="Not Replied";
            $invite = $db->prepare('INSERT INTO invitations (guest_id, event_id, invite_rsvp_status) VALUES (?,?,?)');
            $invite->bind_param('iis',$lead_guest_id, $event_id, $invite_rsvp_status);
            $invite->execute();
            $invite->close();
        }


       //once the guest has been added, determine if the user has added other members to the lead guest.
        if(isset($_POST['guest_group'])){
        /////insert each guest into the guest list from the POST request
        //create a guest group if the guest being added has one or more extra invites
            //set up a group name using first and last name of primary guest
            $group_name = $guest_fname.' '.$guest_sname;
            //insert into guest group tables
            $group = $db->prepare('INSERT INTO guest_groups (guest_group_name, guest_group_organiser) VALUES (?,?)');
            $group->bind_param('si',$group_name, $lead_guest_id);
            $group->execute();
            $group->close();
            $new_group_id = $db->insert_id;
            //update guest list with the guest group id
            $guest = $db->prepare('UPDATE guest_list SET guest_group_id=?  WHERE guest_id =?');
            $guest->bind_param('ii',$new_group_id, $lead_guest_id);
            $guest->execute();
            $guest->close();

        //guest array for all new added guests at the time of making the guest
        $guest_group = $_POST['guest_group'];
        $guest_array = array();
        $guest_type = "Member"; //only set as a member, these guests are a group member
        $new_guest = $db->prepare('INSERT INTO guest_list (guest_fname, guest_sname, guest_rsvp_status, guest_type, guest_group_id) VALUES (?,?,?,?,?)');
        foreach ($guest_group as $group_member) {
            // if the plus one box has been ticked then add them as a plus one
            $fname = $group_member['guest_fname'];
            if(isset($group_member['plus_one']) && $group_member['plus_one'] == "plus_one"){
                $fname = $guest_fname." ".$guest_sname."'s +1";
            }
            $new_guest->bind_param('ssssi', $fname, $group_member['guest_sname'], $guest_rsvp_status, $guest_type, $new_group_id);
            $new_guest->execute();
            //insert into an array for adding to the invites table
            $new_guest_id = $db->insert_id;
            array_push($guest_array, $new_guest_id);
        }
        $new_guest->close();
        if(isset($_POST['event_id'])){
            $invite_rsvp_status = "Not Replied";
            /////Add to invites table for each guest 
            $set_invites = $db->prepare('INSERT INTO invitations (guest_id, event_id, guest_group_id, invite_rsvp_status) VALUES (?,?,?,?)');
            foreach ($guest_array as $guest) {
                $set_invites->bind_param('iiis', $guest, $event_id, $new_group_id, $invite_rsvp_status);
                $set_invites->execute();
            }
    
            $set_invites->close();
        }
        //update the guest list with the amount of extra invites that they have based on how many guests have been added.
        
        $guest_extra_invites = count($guest_array);
        //update guest list with the guest group id
        $guest = $db->prepare('UPDATE guest_list SET guest_extra_invites=?  WHERE guest_id =?');
        $guest->bind_param('ii',$guest_extra_invites, $lead_guest_id);
        $guest->execute();
        $guest->close();
        //update invitations with the lead guest group id as well
        $lead_guest_inv = $db->prepare('UPDATE invitations SET guest_group_id=?  WHERE guest_id =?');
        $lead_guest_inv->bind_param('ii',$new_group_id, $lead_guest_id);
        $lead_guest_inv->execute();
        $lead_guest_inv->close();
        //only insert into invites table if the user has selected an event to add the guests to


        }
        $response="success";
       
    }
    echo $response;
?>