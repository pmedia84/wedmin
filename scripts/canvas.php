<?php
    require("functions.php");
    db_connect($db);
if ($_GET['canvas'] == "add_guest") :
//script for loading canvas body
    //find wedding events details
    $wedding_events_query = ('SELECT * FROM wedding_events ORDER BY event_time');
    $wedding_events = $db->query($wedding_events_query);
    $wedding_events_result = $wedding_events->fetch_assoc();
?>
    <div class="modal-header offcanvas_header">
        <h2 class="modal-title"><svg class="icon feather-icon">
            <use xlink:href="assets/img/icons/feather.svg#user-plus"></use>
        </svg> <span id="canvas-title">Add Guest</span></h2>
        <button class="btn-close" type="button" id="close-canvas">
            <svg class="icon x-mark">
                <use href="assets/img/icons/solid.svg#xmark" />
            </svg>
        </button>
    </div>
    <div class="offcanvas_body">
        <form id="add_guest" action="scripts/guest.script.php" method="POST" >
        <div class="std-card">
            <h3>Lead Guest</h3>
            <div class="form-input-wrapper">
                <label for="guest_fname"><strong>First Name</strong></label>
                <!-- input -->
                <input class="text-input input" type="text" name="guest_fname" id="guest_fname" placeholder="First Name" required="" maxlength="45" title="First Name">
            </div>
            <div class="form-input-wrapper">
                <label for="guest_sname"><strong>Surname</strong></label>
                <!-- input -->
                <input class="text-input input" type="text" name="guest_sname" id="guest_sname" placeholder="Surname" required="" maxlength="45" title="Surname">
            </div>
            <div class="form-input-wrapper my-2">
                <label for="guest_email"><strong>Email Address</strong></label>
                <p class="form-hint-small">Optional, guests that use your guest area will update this themselves.</p>
                <input class="text-input input" type="text" id="guest_email" name="guest_email" placeholder="Email Address">
            </div>
            <button class="btn-primary btn-secondary my-2" type="button" id="show_address"><svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#map-location-dot"></use>
                </svg> Address</button>
            <div class="form-hidden d-none">
                <div class="form-input-wrapper my-2">
                    <label for="guest_address"><strong>Address</strong></label>
                    <p class="form-hint-small">Optional</p>
                    <textarea name="guest_address" id="guest_address"></textarea>
                </div>
                <div class="form-input-wrapper my-2">
                    <label for="guest_postcode"><strong>Postcode</strong></label>
                    <p class="form-hint-small">Optional</p>
                    <input class="text-input input" type="text" id="guest_postcode" name="guest_postcode" placeholder="Postcode">
                </div>
            </div>
        </div>
        <div class="std-card my-3">
            <h2>Additional Guests</h2>
            <p>This will form a group with a lead guest.</p>
            <div id="guest-group-row"></div>
            <button class="btn-primary btn-secondary my-2" type="button" id="add-member" data-guest_sname=""><svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#user-plus"></use>
                </svg> Add Guests</button>
        </div>
        <div class="std-card">
            <h2>Assign To Events</h2>
            <p>You can assign this guest to your events now, or you can do this within the event manager tab.</p>
            <?php if ($wedding_events->num_rows > 0) : ?>
                <?php foreach ($wedding_events as $event) : ?>
                    <div class="form-input-wrapper my-2">
                        <label class="radio-form-control" for="eventid<?= $event['event_id']; ?>"> <strong><?= $event['event_name']; ?></strong>
                            <input class="radio" type="radio" id="eventid<?= $event['event_id']; ?>" name="event_id" value="<?= $event['event_id']; ?>" />
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>There are no events set up, you will need to create them in your <a href="events">Events Tab</a></p>
            <?php endif; ?>
        </div>
        <div class="button-section my-3">
            <button class="btn-primary form-controls-btn" type="button" id="save-and-new" title="Save this guest and add another"> Save & Add Another Guest</button>
            <button class="btn-primary  btn-secondary form-controls-btn" type="button" id="save-close" title="Save this guest and return to your guest list"> Save & Close</button>
        </div>
        </form>
            <div id="response" class="d-none">
            <h4>Please correct the following errors</h4>
            <p></p>
        </div>
    </div>
<?php endif; ?>

<?php if(isset($_GET['canvas'])&& $_GET['canvas']=="load_guest"):
    //load guest information
    $guest_id=$_GET['guest_id'];
    $guest_q=$db->query("SELECT * FROM guest_list WHERE guest_id=".$guest_id);
    //search for any events the guest is associated with
    $guest_invites = $db->query('SELECT wedding_events.event_id, wedding_events.event_name, invitations.guest_id, invitations.event_id, guest_list.guest_id, guest_list.guest_fname FROM wedding_events
    LEFT JOIN invitations ON invitations.event_id=wedding_events.event_id
    LEFT JOIN guest_list ON guest_list.guest_id=invitations.guest_id
    WHERE guest_list.guest_id=' . $guest_id);
    if ($guest_invites->num_rows > 1) {
        $guest_invites->fetch_array();
    }
    if ($meal_choices_wedmin->status() == "On") {
        $meal_choices_q = $db->query('SELECT menu_items.menu_item_id, menu_items.menu_item_name, menu_items.course_id, meal_choices.menu_item_id, meal_choices.choice_order_id, meal_choice_order.choice_order_id, meal_choice_order.guest_id, menu_courses.course_name, menu_courses.course_id  FROM menu_items LEFT JOIN meal_choices ON meal_choices.menu_item_id=menu_items.menu_item_id LEFT JOIN meal_choice_order ON meal_choices.choice_order_id=meal_choice_order.choice_order_id LEFT JOIN menu_courses ON menu_courses.course_id=menu_items.course_id WHERE meal_choice_order.guest_id=' . $guest_id);
    }
    ?>
    <?php if($guest_q->num_rows >0):
        $guest_r=mysqli_fetch_assoc($guest_q);
        ?>
    <div class="modal-header offcanvas_header">
        <h2 class="modal-title"><svg class="icon feather-icon">
            <use xlink:href="assets/img/icons/feather.svg#user-check"></use>
        </svg> <span id="canvas-title">Edit Guest</span></h2>
        <button class="btn-close" type="button" id="close-canvas">
            <svg class="icon x-mark">
                <use href="assets/img/icons/solid.svg#xmark" />
            </svg>
        </button>
    </div>
    <div class="offcanvas_body">
        <form id="edit_guest" action="scripts/guest.script.php" method="POST" >
        <div class="std-card">
            <h2><?=$guest_r['guest_fname']." ".$guest_r['guest_sname']; if($guest_r['guest_extra_invites']>0){echo " +".$guest_r['guest_extra_invites'];}?></h2>
            <div class="guest-card-tags my-2">
            <span class="guest-card-tag" data-guest-type="<?= $guest_r['guest_type']; ?>">
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#user"></use>
                </svg>
                <?= $guest_r['guest_type']; ?>
            </span>
            <span class="guest-card-tag" data-invite-status="<?= $guest_r['guest_rsvp_status']; ?>">
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                </svg>
                <?= $guest_r['guest_rsvp_status']; ?>
            </span>
            <?php if($guest_r['guest_rsvp_code']!=NULL):?>

            <?php endif;?>
        </div>
            <div class="form-input-wrapper">
                <label for="guest_fname"><strong>First Name</strong></label>
                <!-- input -->
                <input class="text-input input" type="text" name="guest_fname" id="guest_fname" placeholder="First Name" required="" value="<?=$guest_r['guest_fname'];?>" title="First Name">
            </div>
            <div class="form-input-wrapper">
                <label for="guest_sname"><strong>Surname</strong></label>
                <!-- input -->
                <input class="text-input input" type="text" name="guest_sname" id="guest_sname" placeholder="Surname" required="" value="<?=$guest_r['guest_sname'];?>" title="Surname">
            </div>
            <div class="form-input-wrapper my-2">
                <label for="guest_email"><strong>Email Address</strong></label>
                <p class="form-hint-small">Optional, guests that use your guest area will update this themselves.</p>
                <input class="text-input input" type="text" id="guest_email" name="guest_email" placeholder="Email Address" value="<?=$guest_r['guest_email'];?>">
            </div>
            <button class="btn-primary btn-secondary my-2" type="button" id="show_address"><svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#map-location-dot"></use>
                </svg> Address</button>
            <div class="form-hidden d-none">
                <div class="form-input-wrapper my-2">
                    <label for="guest_address"><strong>Address</strong></label>
                    <p class="form-hint-small">Optional</p>
                    <textarea name="guest_address" id="guest_address"><?=$guest_r['guest_address'];?></textarea>
                </div>
                <div class="form-input-wrapper my-2">
                    <label for="guest_postcode"><strong>Postcode</strong></label>
                    <p class="form-hint-small">Optional</p>
                    <input class="text-input input" type="text" id="guest_postcode" name="guest_postcode" placeholder="Postcode" value="<?=$guest_r['guest_postcode'];?>">
                </div>
            </div>
        </div>
        <div class="tab-buttons">
            <button class="tablinks active" onclick="openTab(event, 'rsvp')" type="button" >
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                </svg> RSVP
            </button>
            <button class="tablinks" onclick="openTab(event, 'group')" type="button">
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#users"></use>
                </svg> Guest Group
            </button>
            <?php if ($meal_choices_wedmin->status() == "On"):?>
                <?php if ($meal_choices_q->num_rows > 0) :?>
            <button class="tablinks" onclick="openTab(event, 'meal-choices')" type="button">
                <svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#utensils"></use>
                </svg> Meal Choices
            </button>
            <?php endif;?>
            <?php endif;?>
        </div>
    <div id="rsvp" class="tabcontent " style="display: block;">
        <?php if($guest_r['guest_rsvp_code']!=NULL):?>
            <div class="guest-card-tags">
                <span class="guest-card-tag">
                    <svg class="icon">
                        <use xlink:href="assets/img/icons/solid.svg#reply"></use>
                    </svg>
                    RSVP CODE: <?= $guest_r['guest_rsvp_code']; ?>
                </span>
            </div>
            <?php endif;?>
            <div class="my-2">
                <h3>Invitation</h3>
                <p><?=$guest_r['guest_fname'];?> is invited to:</p>
                <?php if ($guest_invites->num_rows >= 1) : ?>
                    <?php foreach ($guest_invites as $invite) : ?>
                        <span class="guest-card-tag">
                    <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#calendar"></use>
                    </svg>
                    <a href="event.php?action=view&event_id=<?= $invite['event_id']; ?>"><?= $invite['event_name']; ?></a>
                </span>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if($guest_r['guest_rsvp_message']!=""):?>
        
                <h3>RSVP Message</h3>
            <p><svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#quote-left"></use>
                </svg> <?= html_entity_decode($guest_r['guest_rsvp_message']);?> <svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#quote-right"></use>
                </svg></p>
            
            <?php endif;?>
            <h3>RSVP Status</h3>
            <p>This will update as your guests respond to their invite. You can do this manually if you need to as well.</p>

            <div class="form-input-wrapper">
                <label for="rsvp_status">Update RSVP Status</label>
                <select name="rsvp_status" id="rsvp_status" class="guest-rsvp-status-select" data-invite-status="<?= $guest_r['guest_rsvp_status']; ?>">
                    <option value="0" selected><?= $guest_r['guest_rsvp_status']; ?></option>
                    <?php if($guest_r['guest_rsvp_status']!="Attending"):?>
                        <option value="Attending">Attending</option>
                    <?php endif;?>
                    <?php if($guest_r['guest_rsvp_status']!="Not Replied"):?>
                        <option value="Not Replied">Awaiting</option>
                    <?php endif;?>
                    <?php if($guest_r['guest_rsvp_status']!="Not Attending"):?>
                        <option value="Not Attending">Declined</option>
                    <?php endif;?>
                </select>
            </div>
        </div>
        <div id="group" class="tabcontent">
            <?php if($guest_r['guest_type']=="Sole"): ?>
            <div class="std-card my-2">
                <h3>
                    <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#users"></use>
                    </svg>
                    Additional Invites</h3>
                <div id="guest-group-row"></div>
                <button class="btn-primary btn-secondary my-2" type="button" id="add-member"><svg class="icon">
                        <use xlink:href="assets/img/icons/solid.svg#user-plus"></use>
                    </svg> Add Guests</button>
            </div>
        <?php endif;?>
        <?php if($guest_r['guest_type']=="Group Organiser" ):
        $guest_group_query = ('SELECT guest_id, guest_fname, guest_sname, guest_group_id, guest_rsvp_status FROM guest_list WHERE guest_group_id=' . $guest_r['guest_group_id'] . ' AND guest_type ="Member"');
        $guest_group = $db->query($guest_group_query); ?>

            <div class="my-2">
                <h3>
                    <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#users"></use>
                    </svg>
                    Additional Invites</h3>
                    <?php if($guest_r['guest_extra_invites']>0):  ?>
                        <table class="std-table">
                            <tr>
                                <th>Name</th>
                                <th>RSVP</th>
                            </tr>
                            
                            <?php foreach ($guest_group as $member) : ?>
                                <tr>
                                    <td><a href="#" data-guest_id="<?=$member['guest_id'];?>" data-data="load_guest" class="canvas_open" data-state="closed"><?=$member['guest_fname']." ".$member['guest_sname'];?></a></td>
                                    <td>
                                    <div class="guest-card-tags">
                                        <span class="guest-card-tag" data-invite-status="<?= $member['guest_rsvp_status']; ?>">
                                            <svg class="icon feather-icon">
                                                <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                                            </svg>
                                                <?= $member['guest_rsvp_status']; ?>
                                        </span>
                                    </div>
                                    </td>
                                </tr>
                               
                                
                            <?php endforeach; ?>
                        </table>
                        <?php endif;?>
                <div id="guest-group-row"></div>
                <button class="btn-primary btn-secondary my-2" type="button" id="add-member" data-guest_sname="<?=$guest_r['guest_sname'];?>"><svg class="icon">
                        <use xlink:href="assets/img/icons/solid.svg#user-plus"></use>
                    </svg> Add Guests</button>
            </div>
        <?php endif;?>
        <?php if($guest_r['guest_type']=="Member"):
            $group_details_query = ('SELECT guest_id, guest_fname, guest_sname, guest_group_id FROM guest_list WHERE guest_group_id=' . $guest_r['guest_group_id'] . ' AND guest_type = "Group Organiser"');
            $group_details = $db->query($group_details_query);
            $group_details_result = $group_details->fetch_assoc();
        
            ?>
            <div class="my-2">
                <h3>Guest Group</h3>
                <p><?=$guest_r['guest_fname'];?> belongs to a guest group organised by <a href="#"data-guest_id="<?=$group_details_result['guest_id'];?>" data-data="load_guest" class="canvas_open" data-state="opened"><?=$group_details_result['guest_fname']." ".$group_details_result['guest_sname'];?></a></p>
            </div>
        <?php endif;?>
        </div>
        <div id="meal-choices" class="tabcontent">
            <?php if ($meal_choices_wedmin->status() == "On") : ?>
            <?php if ($meal_choices_q->num_rows > 0) : ?>
            <div class="">
                <h3>Meal Choices</h3>
                    <div class="menu my-2">
                        <?php foreach ($meal_choices_q as $choice) : ?>
                            <h4><?= $choice['course_name']; ?></h4>
                            <p><?= $choice['menu_item_name']; ?></p>
                            <hr>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
        <?php endif; ?>
        <?php if($guest_r['guest_dietery']!=NULL):?>
            <div class="my-2">
                <h3>Dietary Requirements </h3>
                <p><?= $guest_r['guest_dietery'] ?></p>
            </div>
        <?php endif;?>
        <?php if($meal_choices_q->num_rows>0):?>
            <a href="" class="btn-primary btn-secondary">
            <svg class="icon feather-icon">
                <use xlink:href="assets/img/icons/feather.svg#external-link"></use>
            </svg>
            Edit Meal Choices
            </a>
        <?php endif;?>
</div>




        <div class="button-section my-3">
            <button class="btn-primary form-controls-btn" type="button" id="edit-guest" title="Save this guest and return to your guest list" data-guest_id="<?=$guest_r['guest_id'];?>" data-data="remove_guest"  data-state="closed"> 
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#save"></use>
                </svg> Save & Close</button>
            <a href="#" class="btn-primary btn-delete form-controls-btn canvas_open" type="button" id="remove_guest" title="Remove Guest" data-guest_id="<?=$guest_r['guest_id'];?>" data-data="remove_guest"  data-state="closed"> <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#user-minus"></use>
                </svg>Remove Guest</a>
        </div>
        <input type="hidden" name="guest_group_id" value="<?=$guest_r['guest_group_id'];?>">
        </form>

        <?php else:?>
            <h2>Script error</h2>
        <?php endif;?>
    </div>

    <?php endif;?>
</div>

    <?php if($_GET['canvas']=="remove_guest"):
            //load guest information
    $guest_id=$_GET['guest_id'];
    $guest_q=$db->query("SELECT * FROM guest_list WHERE guest_id=".$guest_id);
    //search for any events the guest is associated with
    $guest_invites = $db->query('SELECT wedding_events.event_id, wedding_events.event_name, invitations.guest_id, invitations.event_id, guest_list.guest_id, guest_list.guest_fname FROM wedding_events
    LEFT JOIN invitations ON invitations.event_id=wedding_events.event_id
    LEFT JOIN guest_list ON guest_list.guest_id=invitations.guest_id
    WHERE guest_list.guest_id=' . $guest_id);
    if ($guest_invites->num_rows > 1) {
        $guest_invites->fetch_array();
    }
    if ($meal_choices_wedmin->status() == "On") {
        $meal_choices_q = $db->query('SELECT menu_items.menu_item_id, menu_items.menu_item_name, menu_items.course_id, meal_choices.menu_item_id, meal_choices.choice_order_id, meal_choice_order.choice_order_id, meal_choice_order.guest_id, menu_courses.course_name, menu_courses.course_id  FROM menu_items LEFT JOIN meal_choices ON meal_choices.menu_item_id=menu_items.menu_item_id LEFT JOIN meal_choice_order ON meal_choices.choice_order_id=meal_choice_order.choice_order_id LEFT JOIN menu_courses ON menu_courses.course_id=menu_items.course_id WHERE meal_choice_order.guest_id=' . $guest_id);
    }
        
        ?>
        <div class="modal-header offcanvas_header remove-guest-header">
            <h2 class="modal-title"><svg class="icon feather-icon">
                <use xlink:href="assets/img/icons/feather.svg#user-minus"></use>
            </svg> <span id="canvas-title">Remove Guest</span></h2>
            <button class="btn-close" type="button" id="close-canvas">
                <svg class="icon x-mark">
                    <use href="assets/img/icons/solid.svg#xmark" />
                </svg>
            </button>
        </div>
    <div class="offcanvas_body alert">
    <?php if($guest_q->num_rows >0):
        $guest_r=mysqli_fetch_assoc($guest_q);
        ?>
        <div class="std-card">
            <h2><?=$guest_r['guest_fname']." ".$guest_r['guest_sname'];?></h2>
            <div class="guest-card-tags my-2">
                <span class="guest-card-tag" data-guest-type="<?= $guest_r['guest_type']; ?>">
                    <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#user"></use>
                    </svg>
                    <?= $guest_r['guest_type']; ?>
                </span>
                <span class="guest-card-tag" data-invite-status="<?= $guest_r['guest_rsvp_status']; ?>">
                    <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                    </svg>
                    <?= $guest_r['guest_rsvp_status']; ?>
                </span>
                <?php if($guest_r['guest_rsvp_code']!=NULL):?>
                <span class="guest-card-tag">
                    <svg class="icon">
                        <use xlink:href="assets/img/icons/solid.svg#reply"></use>
                    </svg>
                    RSVP CODE: <?= $guest_r['guest_rsvp_code']; ?>
                </span>
                <?php endif;?>
            </div>
            <p>Are you sure you want to remove <?=$guest_r['guest_fname'];?> from your guest list?</p>
            <p>This action cannot be reversed</p>
            <p>Removing this guest will also remove any additional guests associated to them.</p>
        </div>
        <div class="tab-buttons">
            <button class="tablinks active" onclick="openTab(event, 'rsvp')" type="button" >
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                </svg> RSVP
            </button>
            <button class="tablinks" onclick="openTab(event, 'group')" type="button">
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#users"></use>
                </svg> Guest Group
            </button>
            <?php if ($meal_choices_wedmin->status() == "On"):?>
                <?php if ($meal_choices_q->num_rows > 0) :?>
            <button class="tablinks" onclick="openTab(event, 'meal-choices')" type="button">
                <svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#utensils"></use>
                </svg> Meal Choices
            </button>
            <?php endif;?>
            <?php endif;?>
        </div>
    <div id="rsvp" class="tabcontent " style="display: block;">
        <?php if($guest_r['guest_rsvp_code']!=NULL):?>
            <div class="guest-card-tags">
                <span class="guest-card-tag">
                    <svg class="icon">
                        <use xlink:href="assets/img/icons/solid.svg#reply"></use>
                    </svg>
                    RSVP CODE: <?= $guest_r['guest_rsvp_code']; ?>
                </span>
            </div>
            <?php endif;?>
            <div class="my-2">
                <h3>Invitation</h3>
                <p><?=$guest_r['guest_fname'];?> is invited to:</p>
                <?php if ($guest_invites->num_rows >= 1) : ?>
                    <?php foreach ($guest_invites as $invite) : ?>
                        <span class="guest-card-tag">
                    <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#calendar"></use>
                    </svg>
                    <a href="event.php?action=view&event_id=<?= $invite['event_id']; ?>"><?= $invite['event_name']; ?></a>
                </span>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if($guest_r['guest_rsvp_message']!=""):?>
        
                <h3>RSVP Message</h3>
            <p><svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#quote-left"></use>
                </svg> <?= html_entity_decode($guest_r['guest_rsvp_message']);?> <svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#quote-right"></use>
                </svg></p>
            
            <?php endif;?>


        </div>
        <div id="group" class="tabcontent">
            <?php if($guest_r['guest_type']=="Sole"): ?>
            <div class="std-card my-2">
                <h2>
                    <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#users"></use>
                    </svg>

            </div>
        <?php endif;?>
        <?php if($guest_r['guest_type']=="Group Organiser" ):
        $guest_group_query = ('SELECT guest_id, guest_fname, guest_sname, guest_group_id FROM guest_list WHERE guest_group_id=' . $guest_r['guest_group_id'] . ' AND guest_type ="Member"');
        $guest_group = $db->query($guest_group_query); ?>

            <div class="my-2">
                <h3>
                    <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#users"></use>
                    </svg>
                    Additional Invites</h3>
                    <?php if($guest_r['guest_extra_invites']>0):  ?>
                        <table class="std-table">
                            <tr>
                                <th>Name</th>
                                <th>RSVP</th>
                            </tr>
                            
                            <?php foreach ($guest_group as $member) : ?>
                                <tr>
                                    <td><a href="#" data-guest_id="<?=$member['guest_id'];?>" data-data="load_guest" class="canvas_open" data-state="closed"><?=$member['guest_fname']." ".$member['guest_sname'];?></a></td>
                                    <td>
                                    <div class="guest-card-tags">
                                        <span class="guest-card-tag" data-invite-status="<?= $guest_r['guest_rsvp_status']; ?>">
                                            <svg class="icon feather-icon">
                                                <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                                            </svg>
                                                <?= $guest_r['guest_rsvp_status']; ?>
                                        </span>
                                    </div>
                                    </td>
                                </tr>
                               
                                
                            <?php endforeach; ?>
                        </table>
                        <?php endif;?>

            </div>
        <?php endif;?>
        <?php if($guest_r['guest_type']=="Member"):
            $group_details_query = ('SELECT guest_id, guest_fname, guest_sname, guest_group_id FROM guest_list WHERE guest_group_id=' . $guest_r['guest_group_id'] . ' AND guest_type = "Group Organiser"');
            $group_details = $db->query($group_details_query);
            $group_details_result = $group_details->fetch_assoc();
        
            ?>
            <div class="my-2">
                <h3>Guest Group</h3>
                <p><?=$guest_r['guest_fname'];?> belongs to a guest group organised by <a href="#"data-guest_id="<?=$group_details_result['guest_id'];?>" data-data="load_guest" class="canvas_open" data-state="opened"><?=$group_details_result['guest_fname']." ".$group_details_result['guest_sname'];?></a></p>
            </div>
        <?php endif;?>
        </div>
        <div id="meal-choices" class="tabcontent">
            <?php if ($meal_choices_wedmin->status() == "On") : ?>
            <?php if ($meal_choices_q->num_rows > 0) : ?>
            <div class="">
                <h3>Meal Choices</h3>
                    <div class="menu my-2">
                        <?php foreach ($meal_choices_q as $choice) : ?>
                            <h4><?= $choice['course_name']; ?></h4>
                            <p><?= $choice['menu_item_name']; ?></p>
                            <hr>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
        <?php endif; ?>


        <?php else:?>
            <h2>Script Error</h2>
            <?php endif;?>
        </div>
        <div class="button-section my-2">
            <a href="" class="btn-primary btn-secondary canvas_open" data-guest_id="<?=$guest_r['guest_id'];?>" data-data="load_guest"  data-state="opened"> 
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#x"></use>
                </svg> Cancel</a>
            <button class="btn-primary btn-delete" id="remove_guest_confirm" data-guest_id="<?=$guest_r['guest_id'];?>"> 
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#user-minus"></use>
                </svg> Remove Guest</button>
        </div>
    

        <?php endif;?>