<?php
require_once('functions.php');
if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] != '') {
        switch($_POST['action']) {
            case 'loadHeader':
                die(fillOutHeader());
            break;
            default:
            break;
        }
    }
}
