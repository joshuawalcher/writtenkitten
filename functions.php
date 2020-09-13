<?php
require_once('config.php');
/* 
@description checks for existence of database. Called by fillOutHeader and Save
@return boolean
*/
function checkForDatabase()
{
    //check config
    if(isset($dbUser) && $dbUser != '' && isset($dbPass) && $dbPass != '') {
        $conn = connectToDB();
        return true;
    }
    return false;
}

/* 
@description returns the things to put in the header, which may be nothing
@return string
*/
function fillOutHeader($storage)
{
    if (checkForDatabase()) {
        if(isLoggedIn($storage)) {
            $username = getUsername($storage);
            return '
            Logged in as ' . $username . '
            <ul id="menu">
                <li><a href="">Log Out</a></li>
                <li><a href="">Manage User</a></li>
            </ul>';
        } else {
            return '
            <ul id="menu">
                <li><a href="">Sign In</a></li>
                <li><a href="">Register</a></li>
            </ul>';
        }
    }
}

/* 
@description save the work
@return boolean
*/
function saveWork($user, $work)
{
    //check for database connection
    //connect to database
    //
}

function login($user)
{}

function logout($user)
{}

/* 
@description connect to database using settings in config file (if present)
@return MySQLi connection object
*/
function connectToDB()
{
    $namea = $dbName;
	$usera = $dbUser;
	$passworda = $dbPass;
	$conn = mysqli_connect($dbHost,$usera,$passworda,$namea) or die(mysqli_error());
	return $conn;
}

function createToken()
{}

function checkPasswordForLogin($password)
{}

function createUser($data)
{}

function checkForExistingSession($userId)
{}

function createSession($userId)
{}

function checkUsernameForExisting($username)
{}

function checkEmailForExisting($email)
{}

function isLoggedIn($storage)
{}

function getUsername($storage)
{}