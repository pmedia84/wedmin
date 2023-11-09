<?php
//prevent anyone browsing to this script page via a GET request.
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    http_response_code(403);
    echo "<h1>" . http_response_code() . " Forbidden</h1>";
    exit;
}
require("functions.php");
db_connect($db);
//T== Type of list
//Groups, shows all guest groups
// All guests
//variable for loading the query type, I.e all guests, confirmed guests or not replied etc
$rsvp = "";
//e == the filter type, such as event, use the id number
$event_id = null;
//event filter
$e_filter = "";
//search filter
$s = "";
$search="";
if (isset($_POST['rsvp'])) {
    $rsvp = $_POST['rsvp'];
}
//change rsvp filter
switch ($rsvp) {
    case "confirmed":
        $rsvp = "WHERE invitations.invite_rsvp_status='Attending'";
        break;
    case "declined":
        $rsvp = "WHERE invitations.invite_rsvp_status='Not Attending'";
        break;
    case "awaiting":
        $rsvp = "WHERE invitations.invite_rsvp_status='Not Replied'";
        break;
    default:
        $rsvp = "WHERE guest_list.guest_rsvp_status IN ('Not Replied','Not Attending', 'Attending')";
}
if (isset($_POST['event_filter'])) {
    $event_id = $_POST['event_filter'];
}
//change rsvp filter
switch ($event_id) {
    case NULL:
        $e_filter = " ";
        break;
    case  !NULL:
        $e_filter = "AND wedding_events.event_id=" . $event_id;
}
//load the search filter if set from POST request
if(isset($_POST['search'])){
    $s=$_POST['search'];
//change rsvp filter
switch($s){
    case NULL:
        $search="";
        break;
     case  !NULL:
        $search="AND guest_list.guest_fname LIKE '%".$s."%' OR guest_list.guest_sname LIKE '%".$s."%'";   
        break;


}
}

$totals_filter = "";
switch ($event_id) {
    case NULL:
        $totals_filter = "WHERE invitations.event_id > 0 ";
        break;
        case  !NULL:
            $totals_filter = "WHERE event_id=" . $event_id;
        }
        $totals_q = $db->query("SELECT 
COUNT(guest_id) AS all_guests, 
COUNT(CASE invite_rsvp_status WHEN 'Attending' THEN 1 ELSE NULL END) AS attending, 
COUNT(CASE invite_rsvp_status WHEN 'Not Attending' THEN 1 ELSE NULL END) AS declined,
COUNT(CASE invite_rsvp_status WHEN 'Not Replied' THEN 1 ELSE NULL END) AS awaiting
 FROM invitations " . $totals_filter);
$all_guests = 0;
$attending = 0;
$declined = 0;
$awaiting = 0;
if ($totals_q->num_rows > 0) {
    $totals_r = mysqli_fetch_assoc($totals_q);
    $all_guests = $totals_r['all_guests'];
    $attending = $totals_r['attending'];
    $declined = $totals_r['declined'];
    $awaiting = $totals_r['awaiting'];
}
if(isset($_POST['event_filter']) && $_POST['event_filter']!=NULL){
    //event filter heading only 
    $event_q= $db->query("SELECT event_id, event_name FROM wedding_events WHERE event_id=".$event_id);
    if($event_q->num_rows>0){
        $event_r=mysqli_fetch_assoc($event_q);
    }
}
//find wedding guest list
$guest_list_q = $db->query('SELECT guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname, guest_list.guest_type, guest_list.guest_extra_invites, guest_list.guest_group_id, guest_list.guest_rsvp_code,guest_list.guest_rsvp_status, invitations.event_id, invitations.invite_rsvp_status, wedding_events.event_id, wedding_events.event_name, guest_groups.guest_group_id, guest_groups.guest_group_name  FROM guest_list LEFT JOIN invitations ON invitations.guest_id=guest_list.guest_id LEFT JOIN guest_groups ON guest_groups.guest_group_id=guest_list.guest_group_id LEFT JOIN wedding_events ON wedding_events.event_id=invitations.event_id ' . $rsvp . " " . $e_filter . ' ' . $search . '');

?>

<?php if (isset($_POST['event_filter']) && $_POST['event_filter']!=NULL) : ?>
    <h2 class="my-2">Guest List for <?= $event_r['event_name']; ?></h2>
    <?php else : ?>
        <h2 class="my-2">Guest List for all events</h2>
<?php endif; ?>
<?php if(isset($_POST['search']) && $_POST['search']>NULL):?>
    <?php if($guest_list_q->num_rows>1):?>
        <h3 class="my-2"><?= $guest_list_q->num_rows;?> Guests found matching "<?=$_POST['search'];?>"</h3>
        <?php else:?>
            <h3 class="my-2"><?= $guest_list_q->num_rows;?> Guest found matching "<?=$_POST['search'];?>"</h3>

    <?php endif;?>
<?php endif;?>
<div class="table-filter-header">
    <a href="guest_list?rsvp=all<?php if($event_id!=NULL){echo "&e=".$_POST['event_filter'];} if(isset($_POST['s'])){echo "&s=".$_POST['s'];}?>" class="table-filter-header_link active">All Guests (<?=$all_guests;?>)</a >
    <a href="guest_list?rsvp=confirmed<?php if($event_id!=NULL){echo "&e=".$_POST['event_filter'];} if(isset($_POST['s'])){echo "&s=".$_POST['s'];}?>" class=" table-filter-header_link">Attending (<?=$attending;?>)</a >
    <a href="guest_list?rsvp=declined<?php if($event_id!=NULL){echo "&e=".$_POST['event_filter'];} if(isset($_POST['s'])){echo "&s=".$_POST['s'];}?>" class="table-filter-header_link ">Declined (<?=$declined;?>)</a >
    <a href="guest_list?rsvp=awaiting<?php if($event_id!=NULL){echo "&e=".$_POST['event_filter'];} if(isset($_POST['s'])){echo "&s=".$_POST['s'];}?>" class="table-filter-header_link ">Awaiting (<?=$awaiting;?>)</a >
</div>
<?php if ($guest_list_q->num_rows > 0) : ?>
    <div class="my-2 table-wrapper">
    <table class="std-table">
        <thead>
            <th>Name</th>
            <th>RSVP</th>
            <th>Event</th>
            <th>Group</th>
        </thead>
        <?php foreach($guest_list_q as $guest):?>
            <tr>
                <td><a href="" data-guest_id="<?=$guest['guest_id'];?>" data-data="load_guest" class="canvas_open" data-state="closed"><?=$guest['guest_fname']." ".$guest['guest_sname'];?></a></td>
                <td><span class="status-pill" data-rsvp="<?=$guest['invite_rsvp_status'];?>"><svg class="icon feather-icon"><use xlink:href="assets/img/icons/feather.svg#circle"></use></svg> <?=$guest['invite_rsvp_status'];?></span></td>
                <td><a href="event?event_id=<?=$guest['event_id'];?>&action=view"><?=$guest['event_name'];?></a></td>
                <td><a href="event?event_id=<?=$guest['event_id'];?>&action=view"><?=$guest['guest_group_name'];?></a></td>
            </tr>
        <?php endforeach;?>
    </table>
    </div>
<?php endif; ?>