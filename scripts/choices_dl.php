<?php
require $_SERVER['DOCUMENT_ROOT'] . "/admin/vendor/autoload.php";

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;
include("../connect.php");
//find the wedding name
$wedding_q = $db->query('SELECT wedding_name FROM wedding');
$wedding_r = mysqli_fetch_assoc($wedding_q);
$wedding_name = $wedding_r['wedding_name'];
//find the totals
$choices_totals = $db->query('SELECT meal_choices.menu_item_id, menu_items.menu_item_id, menu_items.menu_item_name, COUNT(meal_choices.choice_id) AS numberOfChoices FROM meal_choices LEFT JOIN menu_items ON menu_items.menu_item_id=meal_choices.menu_item_id GROUP BY menu_item_name');
//find all the meal choices
$choice = $db->query("SELECT meal_choices.menu_item_id, meal_choices.choice_order_id, menu_items.menu_item_name, menu_items.course_id, menu_courses.course_name, menu_courses.course_id, meal_choice_order.choice_order_id, meal_choice_order.guest_id, guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname FROM meal_choices LEFT JOIN menu_items ON menu_items.menu_item_id=meal_choices.menu_item_id LEFT JOIN menu_courses ON menu_courses.course_id=menu_items.course_id LEFT JOIN meal_choice_order ON meal_choice_order.choice_order_id=meal_choices.choice_order_id LEFT JOIN guest_list ON guest_list.guest_id=meal_choice_order.guest_id ORDER BY guest_list.guest_id, menu_courses.course_id 
  ");
$writer = WriterEntityFactory::createXLSXWriter();
$path = 'guest meal choices ('.date('d-m-y').').xlsx';
$writer->openToFile($path);
$heading_style = (new StyleBuilder())
    ->setFontBold()
    ->setFontSize(18)
    ->setShouldWrapText(false)
    ->setCellAlignment(CellAlignment::LEFT)
    ->setFontColor(Color::WHITE)
    ->setBackgroundColor(Color::rgb(59, 104, 92))
    ->build();
$num_style =(new StyleBuilder())
    ->setFormat('0')
    ->build();
$title_style=(new StyleBuilder())
    ->setFontBold()
    ->setFontSize(24)
    ->setFontColor(Color::WHITE)
    ->setBackgroundColor(Color::rgb(59, 104, 92))
    ->build();
$firstSheet = $writer->getCurrentSheet();
$newSheet = $writer->addNewSheetAndMakeItCurrent();
$newSheet->setName('Guest Meal Choice Totals');
$title=[
    WriterEntityFactory::createCell('Guest Meal Choices For '.$wedding_name.'\'s Wedding')
];
$titleRow = WriterEntityFactory::createRow($title, $title_style);
$writer->addRow($titleRow);
$totals_headings = [
    WriterEntityFactory::createCell('Dish'),
    WriterEntityFactory::createCell('Total Ordered', $num_style),
];
$totals_headingRow = WriterEntityFactory::createRow($totals_headings, $heading_style);
$writer->addRow($totals_headingRow);
foreach($choices_totals as $total){
    $menu_item_name = html_entity_decode($total['menu_item_name'] ,ENT_QUOTES);
    $rows = [
        WriterEntityFactory::createCell($menu_item_name),
        WriterEntityFactory::createCell($total['numberOfChoices'], $num_style),
    ];
    $singleRow = WriterEntityFactory::createRow($rows);
    $writer->addRow($singleRow);
}
$writer->setCurrentSheet($firstSheet);
//set up headings
$header = [
    WriterEntityFactory::createCell('Guest Name'),
    WriterEntityFactory::createCell('Course'),
    WriterEntityFactory::createCell('Dish'),
];
$singleRow = WriterEntityFactory::createRow($header, $heading_style);
$writer->addRow($singleRow);
//insert each guests choices
foreach ($choice as $choice) {
    $menu_item_name = html_entity_decode($choice['menu_item_name'] ,ENT_QUOTES);
    $rows = [
        WriterEntityFactory::createCell($choice['guest_fname'] . ' ' . $choice['guest_sname']),
        WriterEntityFactory::createCell($choice['course_name']),
        WriterEntityFactory::createCell($menu_item_name),
    ];
    $singleRow = WriterEntityFactory::createRow($rows);
    $writer->addRow($singleRow);
}
$sheet = $writer->getCurrentSheet();
$sheet->setName('Guest Meal Choices');
$writer->close();
$db->close();

if(file_exists($path)){
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$path.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($path));
    flush(); // Flush system output buffer
    readfile($path);
}