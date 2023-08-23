<?php 
include("../connect.php");


$table = "<table class='std-table'>";
$table.="<th>Guest Name</th>";
$table.="<th>Course</th>";
$table.="<th>Meal</th>";
$meal_choices = $db->query('SELECT meal_choices.menu_item_id, meal_choices.choice_order_id, menu_items.menu_item_name, menu_items.course_id, menu_courses.course_name, menu_courses.course_id, meal_choice_order.choice_order_id, meal_choice_order.guest_id, guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname FROM meal_choices LEFT JOIN menu_items ON menu_items.menu_item_id=meal_choices.menu_item_id LEFT JOIN menu_courses ON menu_courses.course_id=menu_items.course_id LEFT JOIN meal_choice_order ON meal_choice_order.choice_order_id=meal_choices.choice_order_id LEFT JOIN guest_list ON guest_list.guest_id=meal_choice_order.guest_id ORDER BY guest_list.guest_id, menu_courses.course_id');
foreach ($meal_choices as $choice){
    $table.="<tr>";
    $table.="<td>".$choice['guest_fname']." ".$choice['guest_sname']."</td>";
    $table.="<td>".$choice['course_name']."</td>";
    $table.="<td>".$choice['menu_item_name']."</td>";
    $table.="</tr>";
}
   


$table.="</table>";
require $_SERVER['DOCUMENT_ROOT']."/admin/vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;
$options = new Options;
$options->setChroot($_SERVER['DOCUMENT_ROOT']);
$options->setIsRemoteEnabled(true);
$dompdf = new Dompdf($options);
$html = file_get_contents("choices_template.php");
$html = str_replace(["{{menu}}"],[$table], $html);
$dompdf->setPaper('A4', 'Landscape');
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("Meal Choices ".date('d-m-y'),["Attachment" =>0]);
