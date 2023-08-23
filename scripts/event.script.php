<?php
//load active guest list for event from get request
if ((array_key_exists('action', $_GET))) {
    if ($_GET['action'] == "load") {
        include("../connect.php");
        //define event id
        $event_id = $_GET['event_id'];
        //load all invites details
        $guest_allocated_query = ('SELECT invite_id FROM invitations  WHERE event_id=' . $event_id);
        $invites = $db->query($guest_allocated_query);
        $guests_allocated = $invites->num_rows;
        //find additional invites
        $extra_invites_query = ('SELECT guest_list.guest_id, SUM(guest_list.guest_extra_invites) AS extra_inv, invitations.guest_id FROM guest_list  LEFT JOIN invitations ON invitations.guest_id=guest_list.guest_id WHERE invitations.event_id=' . $event_id);
        $extra_invites = $db->query($extra_invites_query);
        $extra_inv = $extra_invites->fetch_array();
        $total_inv = $extra_inv['extra_inv'];

        //
        $invites_sent = ('SELECT invite_id FROM invitations  WHERE event_id=' . $event_id . ' AND invite_status="Sent"');
        $invites = $db->query($invites_sent);
        $invites_sent = $invites->num_rows;
        //find event details
        $event = $db->prepare('SELECT * FROM wedding_events WHERE event_id =' . $event_id);
        $event->execute();
        $event->store_result();
        $event->bind_result($event_id, $event_name, $event_location, $event_address, $event_postcode, $event_date, $event_time, $event_end, $event_notes, $event_capacity);
        $event->fetch();
        $event_time = strtotime($event_time);
        $time = date('H:ia', $event_time);
        $event_date = strtotime($event_date);
        $date = date('D d M Y', $event_date);
    }
}
?>
<?php if (array_key_exists('action', $_GET)) : ?>
    <?php if ($_GET['action'] == "load") : ?>
        <h3>Invite Details</h3>
        <p>Note that the figures below also include guests that can bring others with them.</p>
        <div class="event-card-invites">
            <div class="event-card-invites-textbox">
                <p>Invites Available </p><span><?= $event_capacity  - $guests_allocated; ?></span>
            </div>
            <div class="event-card-invites-textbox">
                <?php
                ?>
                <p>Invites Sent </p><span><?= $invites_sent; ?></span>
            </div>

            <div class="event-card-invites-textbox">
                <p>Guests Allocated </p><span><?=$guests_allocated; ?></span>
            </div>
        </div>

        <h3>Guest List</h3>
        <p>To remove a guest from this event, click the minus button beside their name.</p>
        <p>You can only remove guests that are group organisers or sole invites.</p>
        <table class="event-card-guestlist-table ">
            <?php
            $guest_list_query = ('SELECT guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname, guest_list.guest_extra_invites, guest_list.guest_type, guest_list.guest_group_id, invitations.event_id, invitations.guest_id, invitations.invite_status FROM guest_list  LEFT JOIN invitations ON invitations.guest_id = guest_list.guest_id WHERE event_id=' . $event_id . ' AND NOT guest_list.guest_type="Member" ORDER BY guest_list.guest_group_id, guest_list.guest_fname ASC');
            $guest_list = $db->query($guest_list_query);

            ?>

            <tr>
                <th>Name</th>
                <th>Remove</th>
            </tr>
            <?php foreach ($guest_list as $guest) :
                if ($guest['guest_extra_invites'] >= 1) {
                    $plus = "+" . $guest['guest_extra_invites'];
                } else {
                    $plus = "";
                }

            ?>
                <tr>
                    <td><a <?php if($guest['guest_type']=="Member"):?> class="text-muted"<?php endif;?> href="guest.php?action=view&guest_id=<?= $guest['guest_id']; ?>"><?= $guest['guest_fname'] . " " . $guest['guest_sname'] . ' ' . $plus; ?></a></td>
                    <td><?php if ($guest['guest_type'] == "Group Organiser" || $guest['guest_type'] == "Sole") : ?>
                            <label class="checkbox-form-control">
                                <button class="btn-primary btn-secondary remove_guest" data-guestid="<?= $guest['guest_id']; ?>" data-guesttype="<?= $guest['guest_type']; ?>" data-guestgroupid="<?= $guest['guest_group_id']; ?>"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#user-minus"></use></svg></button>
                            </label>
                        <?php else : ?>
                            <label class="checkbox-form-control">
                                <button class="btn-primary btn-secondary remove_guest disabled" disabled><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#user-minus"></use></svg></button>
                            </label>
                        <?php endif; ?>
                    </td>

                </tr>
            <?php endforeach; ?>


        </table>
        <h3>Guests Available To Assign</h3>
        <p>This will also assign any additional guests you may have added.</p>
        <label class="checkbox-form-control" for="check_all">
            <input type="checkbox" id="check_all" />
            Assign All Available Guests
        </label>
        <?php
        $available_inv_query = ('SELECT guest_id, guest_fname, guest_sname, guest_extra_invites, guest_type FROM guest_list WHERE guest_type<>"Member" AND NOT EXISTS(SELECT guest_id, event_id FROM invitations WHERE guest_list.guest_id=invitations.guest_id )');
        $available_inv = $db->query($available_inv_query);
        $available_inv_result = $available_inv->fetch_assoc();
        ?>
        <form action="scripts/event.script.php" method="POST" enctype="multipart/form-data" id="assign_guests">
            <table class="event-card-guestlist-table">
                <tr>
                    <th>Name</th>
                    <th>Assign</th>
                </tr>
                <?php foreach ($available_inv as $inv) :
                    if ($inv['guest_extra_invites'] >= 1) {
                        $plus = "+" . $inv['guest_extra_invites'];
                    } else {
                        $plus = "";
                    }
                ?>
                    <tr>
                        <td><a href="guest.php?action=view&guest_id=<?= $inv['guest_id']; ?>"><?= $inv['guest_fname'] . " " . $inv['guest_sname'] . ' ' . $plus; ?></a></td>
                        <td>
                            <label class="checkbox-form-control" for="guest">
                                <input class="assign_check" type="checkbox" id="guest" name="guest_id[]" value="<?= $inv['guest_id']; ?>" />
                            </label>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="button-section my-3">
                <button class="btn-primary form-controls-btn" type="submit"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#user-plus"></use></svg> Assign Selected Guests </button>
            </div>
        </form>
        <script>
            //add a new guest to the event
            $("#assign_guests").submit(function(event) {

                event.preventDefault();

                var formData = new FormData($("#assign_guests").get(0));
                var event_id = <?php echo $event_id; ?>;
                formData.append("action", "assign");
                formData.append("event_id", event_id);
                $.ajax({ //start ajax post
                    type: "POST",
                    url: "scripts/event.script.php",
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() { //animate button
                        $("#active_guest_list").fadeOut(300);
                    },

                    success: function(data, responseText) {

                        var event_id = <?php echo $event_id; ?>;
                        url = "scripts/event.script.php?action=load&event_id=" + event_id;
                        $.ajax({ //load active guest list
                            type: "GET",
                            url: url,
                            encode: true,
                            success: function(data, responseText) {
                                $("#active_guest_list").html(data);
                                $("#active_guest_list").fadeIn(500);
                            }
                        });
                    }
                });

            });
        </script>
        <script>
            //remove guests from list
            $(".remove_guest").on("click", function() {

                var formData = new FormData();
                var guest_id = $(this).data("guestid");
                var guest_type = $(this).data("guesttype");
                var guest_group_id = $(this).data("guestgroupid");
                var event_id = <?php echo $event_id; ?>;
                formData.append("action", "remove_guest");
                formData.append("event_id", event_id);
                formData.append("guest_id", guest_id);
                formData.append("guest_type", guest_type);
                formData.append("guest_group_id", guest_group_id);
                $.ajax({ //start ajax post
                    type: "POST",
                    url: "scripts/event.script.php",
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() { //animate button
                        $("#active_guest_list").fadeOut(300);
                    },

                    success: function(data, responseText) {

                        var event_id = <?php echo $event_id; ?>;
                        url = "scripts/event.script.php?action=load&event_id=" + event_id;
                        $.ajax({ //load active guest list
                            type: "GET",
                            url: url,
                            encode: true,
                            success: function(data, responseText) {
                                $("#active_guest_list").html(data);
                                $("#active_guest_list").fadeIn(300);
                            }
                        });
                    }
                });
            })
        </script>
        <script>
            //check all check boxes
            $("#check_all").on("click", function() {
                $(".assign_check").not(this).prop('checked', this.checked)
            })
        </script>
    <?php endif; ?>
<?php endif; ?>
<?php
if (array_key_exists('action', $_POST)) {
    if ($_POST['action'] == "assign") {
        include("..//connect.php");
        //set up invite rsvp status as not replied for all invites
        $invite_rsvp_status = "Not Replied";
        //declare event_id
        $event_id = $_POST['event_id'];
        //prepare the invite script
        $invite = $db->prepare('INSERT INTO invitations (guest_id, event_id, invite_rsvp_status, guest_group_id) VALUES (?,?,?,?)');
        $sole_invite = $db->prepare('INSERT INTO invitations (guest_id, event_id, invite_rsvp_status) VALUES (?,?,?)');
        //group members
        $group_invites = $db->prepare('INSERT INTO invitations (guest_id, event_id, invite_rsvp_status,  guest_group_id) VALUES (?,?,?,?)');
        //process each guest ID that has been sent across and give them an invitation to this event
        //find their group id
        foreach ($_POST['guest_id'] as $guest_id) {
            $group_id = $db->query("SELECT guest_group_id FROM guest_list WHERE guest_id =".$guest_id."");
            $res = $group_id->fetch_array();
            $group_id= $res['guest_group_id'];
            
            $invite->bind_param('iisi', $guest_id, $event_id,  $invite_rsvp_status, $group_id );
            $invite->execute();
        }
        $invite->close();
        //find the guest group each guest being added belongs to and add their group members if they have any, if not then add them as a sole invite
        foreach ($_POST['guest_id'] as $id) {
            $group_id = $db->query("SELECT guest_group_id FROM guest_list WHERE guest_id =" . $id . "");
            $res = $group_id->fetch_array();
            $group_id = $res['guest_group_id'];
           
            //if the group id is not null then find all group members associated with each group organiser being added.
            if ($group_id == NULL || $group_id == 0) {
                $sole_invite->bind_param('iis', $id, $event_id, $invite_rsvp_status);
                $sole_invite->execute();
                
                // if null then add the one guest
            } else {
                // find the group members and add them to the same invite list
                $members = $db->query("SELECT guest_id, guest_group_id FROM guest_list WHERE guest_group_id =" . $group_id . " AND guest_type='Member'");
                if ($members->num_rows > 0) {
                    $res2 = $members->fetch_array();
                    foreach ($members as $result) {
                        $group_invites->bind_param('iisi', $result['guest_id'], $event_id, $invite_rsvp_status, $result['guest_group_id']);
                        $group_invites->execute();
                    }
                    
                }
                
            }
        }
        $group_invites->close();
        $sole_invite->close();
    }

    if ($_POST['action'] == "remove_guest") {
        include("..//connect.php");
        //declare event_id and guest_id
        $event_id = $_POST['event_id'];
        $guest_id = $_POST['guest_id'];
        $guest_group_id = $_POST['guest_group_id'];
        //remove from invitation table
        $invite = $db->prepare('DELETE FROM  invitations  WHERE guest_id=? AND event_id=?');
        $invite->bind_param('ii', $guest_id, $event_id);
        $invite->execute();
        $invite->close();
        //remove any group members if this guest is a group organiser 
        if($_POST['guest_type']=="Group Organiser"){
            $guest_group_members = $db->prepare('DELETE FROM  invitations  WHERE guest_group_id=? AND event_id=?');
            $guest_group_members->bind_param('ii', $guest_group_id, $event_id);
            $guest_group_members->execute();
            $guest_group_members->close();
        }
    }
    if ($_POST['action'] == "edit_event") {
        include("..//connect.php");
        //declare variables
        $event_id = $_POST['event_id'];
        $event_name = mysqli_real_escape_string($db, $_POST['event_name']);
        $event_location = mysqli_real_escape_string($db, $_POST['event_location']);
        $event_address= htmlspecialchars($_POST['event_address']);
        $event_postcode= htmlspecialchars($_POST['event_postcode']);
        $event_date = mysqli_real_escape_string($db, $_POST['event_date']);
        $event_time = mysqli_real_escape_string($db, $_POST['event_time']);
        $event_end_time = mysqli_real_escape_string($db, $_POST['event_end_time']);
        $event_notes = htmlentities($_POST['event_notes']);
        $event_capacity = mysqli_real_escape_string($db, $_POST['event_capacity']);
        //insert into guest group tables
        $event = $db->prepare('UPDATE wedding_events SET event_name=?, event_location=?, event_address=?, event_postcode=?, event_date=?, event_time=?, event_end=?, event_notes=?, event_capacity=?  WHERE event_id =?');
        $event->bind_param('sssssssssi', $event_name, $event_location, $event_address, $event_postcode, $event_date, $event_time, $event_end_time, $event_notes, $event_capacity, $event_id);
        $event->execute();
        $event->close();
    }
    if ($_POST['action'] == "add_event") {
        include("..//connect.php");
        //declare variables
        $event_name = mysqli_real_escape_string($db, $_POST['event_name']);
        $event_location = mysqli_real_escape_string($db, $_POST['event_location']);
        $event_address= htmlspecialchars($_POST['event_address']);
        $event_postcode= htmlspecialchars($_POST['event_postcode']);
        $event_date = mysqli_real_escape_string($db, $_POST['event_date']);
        $event_time = mysqli_real_escape_string($db, $_POST['event_time']);
        $event_end_time = mysqli_real_escape_string($db, $_POST['event_end_time']);
        $event_notes = htmlentities($_POST['event_notes']);
        $event_capacity = mysqli_real_escape_string($db, $_POST['event_capacity']);
        //insert into events table
        $new_event = $db->prepare('INSERT INTO wedding_events (event_name, event_location, event_address, event_postcode, event_date, event_time, event_end, event_notes, event_capacity ) VALUES (?,?,?,?,?,?,?,?,?)');
        $new_event->bind_param('ssssssssi', $event_name, $event_location, $event_address, $event_postcode, $event_date, $event_time,  $event_end_time,  $event_notes, $event_capacity);
        $new_event->execute();
        $new_event->close();
    }
}
