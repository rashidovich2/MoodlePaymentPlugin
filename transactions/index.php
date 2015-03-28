<?php

/*
 * The Transactions portion of the Moodle Payment Plugin is meant to serve as the admin reporting
 * tool for behind the scenes, government tracking of the financial aspects of the transactions
 * processed by the Moodle payment plugin. This will involve reading the logs produced by the 
 * other payment plugin components -- mainly the payment and discount components.
 */
//-------------------------------------------------------------------------------------------------------------------
//
//          Fields
//
//-------------------------------------------------------------------------------------------------------------------
define('TABLE', 'transactions'); //Name of transactions table

//-------------------------------------------------------------------------------------------------------------------
//
//          Moodle Extras
//
//-------------------------------------------------------------------------------------------------------------------
//Required Libraries
require('../../config.php');
require_once($CFG->dirroot . '/report/stats/locallib.php');
require_once($CFG->libdir . '/adminlib.php');

//Page Definitions
define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);

//Variables of Import
$start_date; //No idea how to assign these yet. 
$end_date;

//Require Login and admin 
require_login();
$ADMIN->add('reports', new admin_externalpage('transaction_report', get_string('transactions', 'report_transactions'), "$CFG->wwwroot/report/stats/transactions/index.php"));
$settings = null;

//The page's URL
$url = "$CFG->wwwroot/report/stats/transactions/index.php";

//Depending on how demanding the reporting is, this may need to be in effect.
//raise_memory_limit(MEMORY_EXTRA);
//core_php_time_limit::raise();
//Check for the required variables
$date = required_param('date', PARAM_DATE);

//-------------------------------------------------------------------------------------------------------------------
//
//          Report Controls
//
//-------------------------------------------------------------------------------------------------------------------
$start_year_selector = html_select::make_time_selector('years', 'myyear', '120308000');
$start_month_selector = html_select::make_time_selector('months', 'mymonth', '120308000');

$end_year_selector = html_select::make_time_selector('years', 'myyear', '120308000');
$end_month_selector = html_select::make_time_selector('months', 'mymonth', '120308000');

$checkbox = html_select_option::make_checkbox('1', false, get_string('checkbox'));

echo $OUTPUT->select(start_year_selector);
echo $OUTPUT->select(start_month_selector);
echo $OUTPUT->select(end_year_selector);
echo $OUTPUT->select(end_month_selector);
echo $OUTPUT->checkbox($checkbox, get_string('checkbox'));
//-------------------------------------------------------------------------------------------------------------------
//
//          Report Display
//
//-------------------------------------------------------------------------------------------------------------------
//  The following is sample code to create a table of output
//    $table = new html_table();
//    $table->head  = array($strissue, $strstatus, $strdesc, $strconfig);
//    $table->rowclasses = array('leftalign issue', 'leftalign status', 'leftalign desc', 'leftalign config');
//    $table->attributes = array('class'=>'admintable securityreport generaltable');
//    $table->id = 'securityissuereporttable';
//    $table->data  = array();
//
//    // print detail of one issue only
//    $row = array();
//    $row[0] = report_security_doc_link($issue, $result->name);
//    $row[1] = $statusarr[$result->status];
//    $row[2] = $result->info;
//    $row[3] = is_null($result->link) ? '&nbsp;' : $result->link;
//
//    $PAGE->set_docs_path('report/security/' . $issue);
//
//    $table->data[] = $row;
//
//    echo html_writer::table($table);
//Offer Download  
echo $OUTPUT->single_button(new moodle_url('/transactions/export/' . $this->plugin . '/report.php', $params), get_string('download', 'admin'));

//-------------------------------------------------------------------------------------------------------------------
//
//          Logical Functions
//
//-------------------------------------------------------------------------------------------------------------------

function get_data($startYear, $startMonth, $endYear, $endMonth) {
    $where_query = 'date between \''.$startYear.'-'.$startMonth.'-00\' AND \''.$endYear.'-'.$endMonth.'-00\''; //Moodle auto-inserts the "WHERE" keyword
    $list = $DB -> get_records_select(TABLE, $where_query);    
    
    return $list;
} //End get_data

//Given a start date, an end date and a file location, dump the transaction history into the .csv
function make_csv($list, $location) {
        
    $file = fopen($location."report.csv", "w");

    foreach ($list as $line) {
        fputcsv($file, explode(',', $line));
    }

    fclose($file);
}//End make_csv


?>