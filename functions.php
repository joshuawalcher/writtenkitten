<?php

/* 
@description checks for existence of database. Called by fillOutHeader and Save
@return boolean
*/
function checkForDatabase()
{
    //check config
    //check database connection
    //close connection
    //return true
}

/* 
@description returns the things to put in the header, which may be nothing
@return string
*/
function fillOutHeader()
{}

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
{}
