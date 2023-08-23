<?php
include("../connect.php");
if (isset($_GET['action'])) {
    
    if($_GET['action']=="load_guest_list"){
        //load guest list from the db and send back to the front page
        include("../connect.php");
        //find wedding guest list
        $guest_list_query = ('SELECT guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname ,guest_list.guest_extra_invites, guest_list.guest_rsvp_code,guest_list.guest_address, guest_list.guest_postcode, guest_list.guest_group_id, invitations.guest_id, invitations.event_id, invitations.invite_rsvp_status, wedding_events.event_id, wedding_events.event_name, guest_groups.guest_group_id, guest_groups.guest_group_name FROM guest_list  
                                LEFT JOIN invitations ON guest_list.guest_id = invitations.guest_id
                                LEFT JOIN wedding_events ON invitations.event_id = wedding_events.event_id
                                LEFT JOIN guest_groups ON guest_groups.guest_group_id = guest_list.guest_group_id
                                WHERE invitations.guest_id=guest_list.guest_id
                                
                                ORDER BY  guest_groups.guest_group_name');
        $guest_list = $db->query($guest_list_query);
        $guest_list_result = $guest_list->fetch_assoc();

        echo
        '<div class="table-wrapper"><table class="std-table">
            <tr>
                <th>Name</th>
                <th>Extra Invites</th>
                <th>RSVP Code</th>
                <th>Address</th>
                <th>Post Code</th>
                <th>Guest Group</th>
            </tr>'; 
    foreach($guest_list as $guest){
        if($guest['guest_extra_invites']>=1){
            $plus= "+".$guest['guest_extra_invites'];
        }else{
            $plus="";
        }
        echo' <tr>
        <td>'.$guest['guest_fname'].' '.$guest['guest_sname'].'</td>
        <td>'.$guest['guest_extra_invites'].'</td>
        <td>'.$guest['guest_rsvp_code'].'</td>
        <td>'.$guest['guest_address'].'</td>
        <td>'.$guest['guest_postcode'].'</td>
        <td>'.$guest['guest_group_name'].'</td>
    </tr>                   
    ';}

    echo '</table>';
    echo '</div>';
    }


        
}



