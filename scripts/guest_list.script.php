<?php
//prevent anyone browsing to this script page via a GET request.
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    http_response_code(403);
    echo "<h1>" . http_response_code() . " Forbidden</h1>";
    exit;
}
?>
<?php if (isset($_POST['action']) && $_POST['action'] == "search") :
    include("../connect.php");
    $search = mysqli_real_escape_string($db, $_POST['search']);
    //find wedding guest list
    $guest_list_query = ('SELECT guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname, guest_list.guest_type,guest_list.guest_rsvp_code, guest_list.guest_extra_invites, guest_list.guest_group_id, invitations.guest_id, invitations.event_id, invitations.invite_rsvp_status, wedding_events.event_id, wedding_events.event_name  FROM guest_list LEFT JOIN invitations ON invitations.guest_id=guest_list.guest_id LEFT JOIN wedding_events ON wedding_events.event_id=invitations.event_id WHERE guest_list.guest_fname LIKE "%' . $search . '%" OR guest_list.guest_sname LIKE "%' . $search . '%"  ORDER BY guest_list.guest_sname');
    if ($search == "") {
        $guest_list_query = ('SELECT guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname, guest_list.guest_type, guest_list.guest_extra_invites, guest_list.guest_rsvp_code, guest_list.guest_group_id, invitations.guest_id, invitations.event_id, invitations.invite_rsvp_status, wedding_events.event_id, wedding_events.event_name  FROM guest_list LEFT JOIN invitations ON invitations.guest_id=guest_list.guest_id LEFT JOIN wedding_events ON wedding_events.event_id=invitations.event_id WHERE guest_list.guest_type="Group Organiser" OR guest_list.guest_type="Sole" ORDER BY guest_list.guest_sname');
    }
    $guest_list = $db->query($guest_list_query);
    $guest_list_result = $guest_list->fetch_assoc();
    $result_num = $guest_list->num_rows;


?>

    <?php if ($guest_list->num_rows > 0) : ?>
        <?php if ($search != "") : ?>
            <h2 class="notification-header">Guests Found Matching "<?= $search; ?>" <span class="notification"><?= $result_num; ?></span></h2>
        <?php endif ?>
        <?php foreach ($guest_list as $guest) : ?>
            <div class="guest-card my-2">
                <div class="guest-card-body">
                    <div class="guest-card-title">
                        <h3>
                            <a href="guest?action=view&guest_id=<?= $guest['guest_id']; ?>" class="guest_name">
                                <?php echo $guest['guest_fname'] . ' ' . $guest['guest_sname'];
                                if ($guest['guest_extra_invites'] > 0) {
                                    echo " +" . $guest['guest_extra_invites'];
                                } ?>
                            </a>
                        </h3>
                        <?php if ($guest['guest_type'] == "Group Organiser") : ?>
                            <a href="" class="group-btn">View Group
                                <svg class="icon">
                                    <use xlink:href="assets/img/icons/solid.svg#chevron-down"></use>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="guest-card-tags">
                        <span class="guest-card-tag">
                            <svg class="icon feather-icon">
                                <use xlink:href="assets/img/icons/feather.svg#calendar"></use>
                            </svg>
                            <a href=""><?= $guest['event_name']; ?></a>
                        </span>
                        <span class="guest-card-tag" data-invite-status="<?= $guest['invite_rsvp_status']; ?>">
                            <svg class="icon feather-icon">
                                <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                            </svg>
                            <?= $guest['invite_rsvp_status']; ?>
                        </span>
                        <span class="guest-card-tag">
                            <svg class="icon">
                                <use xlink:href="assets/img/icons/solid.svg#reply"></use>
                            </svg>
                            RSVP CODE: <?= $guest['guest_rsvp_code']; ?>
                        </span>
                        <span class="guest-card-tag" data-guest-type="<?= $guest['guest_type']; ?>">
                            <svg class="icon feather-icon">
                                <use xlink:href="assets/img/icons/feather.svg#user"></use>
                            </svg>
                            <?= $guest['guest_type']; ?>
                        </span>
                    </div>
                </div>
                <?php if ($guest['guest_type'] == "Group Organiser") : ?>
                    <div class="guest-group-card d-none">
                        <div class="guest-group">
                            <h3><svg class="icon feather-icon">
                                        <use xlink:href="assets/img/icons/feather.svg#users"></use>
                                    </svg><?= $guest['guest_fname']; ?>'s extra invites </h3>
                            <?php $guest_group = $db->query("SELECT guest_id, guest_fname, guest_sname, guest_rsvp_status FROM guest_list WHERE guest_group_id=" . $guest['guest_group_id'] . " AND guest_type='Member'"); ?>
                            <?php foreach ($guest_group as $member) : ?>
                                <a href="guest?action=view&guest_id=<?= $member['guest_id']; ?>" data-rsvp="<?= $member['guest_rsvp_status']; ?>"><?= $member['guest_fname'] . " " . $member['guest_sname']; ?> <svg class="icon feather-icon d-none">
                                        <use xlink:href="assets/img/icons/feather.svg#alert-circle"></use>
                                    </svg></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <h2>Sorry, no guests found matching those details</h2>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($_POST['action']) && $_POST['action'] == "guest_filter") :
    include("../connect.php");
    $rsvp_filter = $_POST['rsvp_filter'];
    $event_filter = $_POST['event_filter'];
    //default filter setting
    $filter = "WHERE invitations.event_id!=0 AND guest_list.guest_type='Group Organiser' OR guest_list.guest_type='Sole'";
    $rsvp = "";
    $event = "";
    $guest = "";
    switch ($rsvp_filter) {
        case "":
            $rsvp = "WHERE invitations.invite_rsvp_status IN ('Not Replied','Not Attending', 'Attending') ";
            break;
        case "Not Replied":
            $rsvp = "WHERE invitations.invite_rsvp_status ='Not Replied'";
            break;
        case "Not Attending":
            $rsvp = "WHERE invitations.invite_rsvp_status ='Not Attending'";
            break;
        case "Attending":
            $rsvp = "WHERE invitations.invite_rsvp_status ='Attending'";
            break;
    }
    switch ($event_filter) {
        case "":
            $event = "AND invitations.event_id >0 ";
            break;
        default:
            $event = "AND invitations.event_id=" . $event_filter;
    }
    //find wedding guest list
    $guest_list_query = ('SELECT guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname, guest_list.guest_type, guest_list.guest_rsvp_code, guest_list.guest_extra_invites, guest_list.guest_group_id, invitations.guest_id, invitations.event_id, invitations.invite_rsvp_status, wedding_events.event_id, wedding_events.event_name  FROM guest_list LEFT JOIN invitations ON invitations.guest_id=guest_list.guest_id LEFT JOIN wedding_events ON wedding_events.event_id=invitations.event_id ' . $rsvp . ' ' . $event . ' AND NOT guest_list.guest_type="Member" ORDER BY guest_list.guest_sname');
    $guest_list = $db->query($guest_list_query);
    $guest_list_result = $guest_list->fetch_assoc();
    $result_num = $guest_list->num_rows;
?>
    <?php if ($guest_list->num_rows > 0) : ?>
        <h2 class="notification-header">Guests Found <span class="notification"><?= $result_num; ?></span></h2>

        <?php foreach ($guest_list as $guest) : ?>
            <div class="guest-card my-2">
                <div class="guest-card-body">
                    <div class="guest-card-title">
                        <h3>
                            <a href="guest?action=view&guest_id=<?= $guest['guest_id']; ?>" class="guest_name">
                                <?php echo $guest['guest_fname'] . ' ' . $guest['guest_sname'];
                                if ($guest['guest_extra_invites'] > 0) {
                                    echo " +" . $guest['guest_extra_invites'];
                                } ?>
                            </a>
                        </h3>
                        <?php if ($guest['guest_type'] == "Group Organiser") : ?>
                            <a href="" class="group-btn">View Group
                                <svg class="icon">
                                    <use xlink:href="assets/img/icons/solid.svg#chevron-down"></use>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="guest-card-tags">
                        <span class="guest-card-tag">
                            <svg class="icon feather-icon">
                                <use xlink:href="assets/img/icons/feather.svg#calendar"></use>
                            </svg>
                            <a href=""><?= $guest['event_name']; ?></a>
                        </span>
                        <span class="guest-card-tag" data-invite-status="<?= $guest['invite_rsvp_status']; ?>">
                            <svg class="icon feather-icon">
                                <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                            </svg>
                            <?= $guest['invite_rsvp_status']; ?>
                        </span>
                        <span class="guest-card-tag">
                            <svg class="icon">
                                <use xlink:href="assets/img/icons/solid.svg#reply"></use>
                            </svg>
                            RSVP CODE: <?= $guest['guest_rsvp_code']; ?>
                        </span>
                        <span class="guest-card-tag" data-guest-type="<?= $guest['guest_type']; ?>">
                            <svg class="icon feather-icon">
                                <use xlink:href="assets/img/icons/feather.svg#user"></use>
                            </svg>
                            <?= $guest['guest_type']; ?>
                        </span>
                    </div>
                </div>
                <?php if ($guest['guest_type'] == "Group Organiser") : ?>
                    <div class="guest-group-card d-none">
                        <div class="guest-group">
                            <h3><svg class="icon feather-icon">
                                        <use xlink:href="assets/img/icons/feather.svg#users"></use>
                                    </svg><?= $guest['guest_fname']; ?>'s extra invites </h3>
                            <?php $guest_group = $db->query("SELECT guest_id, guest_fname, guest_sname, guest_rsvp_status FROM guest_list WHERE guest_group_id=" . $guest['guest_group_id'] . " AND guest_type='Member'"); ?>
                            <?php foreach ($guest_group as $member) : ?>
                                <a href="guest?action=view&guest_id=<?= $member['guest_id']; ?>" data-rsvp="<?= $member['guest_rsvp_status']; ?>"><?= $member['guest_fname'] . " " . $member['guest_sname']; ?> <svg class="icon feather-icon d-none">
                                        <use xlink:href="assets/img/icons/feather.svg#alert-circle"></use>
                                    </svg></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <h2>Sorry, no guests found matching those details</h2>
    <?php endif; ?>
<?php endif; ?>